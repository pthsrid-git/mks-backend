<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Match_update_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Settings_model');
    }

    public function update_match_scores()
    {
        // Check if auto update is enabled
        if (!$this->Settings_model->get_setting('auto_update_scores', 1)) {
            return ['status' => 'disabled', 'message' => 'Auto score updates are disabled'];
        }

        // Get matches that need updating (prioritize ongoing matches)
        $this->db->where("(status = 'ongoing' OR status = 'finished')");
        $this->db->where('match_date >=', date('Y-m-d', strtotime('-2 days')));
        $this->db->order_by('status', 'ASC'); // Ongoing first
        $this->db->order_by('match_date', 'DESC'); // Recent first
        $matches = $this->db->get('matches')->result();

        // Limit to 8 matches to stay under 10 requests/minute
        $matches = array_slice($matches, 0, 8);

        $updated_count = 0;
        $errors = [];

        foreach ($matches as $match) {
            try {
                // Add delay between requests to respect rate limits
                if ($updated_count > 0) {
                    sleep(2); // 2 second delay between requests
                }

                $result = $this->update_single_match($match);
                if ($result['updated']) {
                    $updated_count++;

                    // Update predictions if scores changed and match is finished
                    if ($result['score_changed'] && $result['new_score'] !== "null-null") {
                        if ($result['status'] === 'finished') {
                            $this->evaluate_predictions($match->id);
                        }
                    }
                }
            } catch (Exception $e) {
                $errors[] = "Match {$match->id}: " . $e->getMessage();

                // If rate limit hit, stop processing
                if (strpos($e->getMessage(), 'Rate limit exceeded') !== false) {
                    $errors[] = "Stopping due to rate limit";
                    break;
                }
            }
        }

        return [
            'status' => 'success',
            'updated' => $updated_count,
            'total_checked' => count($matches),
            'errors' => $errors
        ];
    }

    private function update_single_match($match)
    {
        $api_key = $this->Settings_model->get_setting('api_football_key');

        if (!$api_key || $api_key == 'your_api_key_here') {
            throw new Exception('API key not configured');
        }

        try {
            // Use Football-Data.org API
            $api_scores = $this->get_live_scores_football_data($match);

            $update_data = [
                'home_score' => $api_scores['home_score'],
                'away_score' => $api_scores['away_score'],
                'status' => $api_scores['status'],
                'last_score_update' => date('Y-m-d H:i:s')
            ];

            // Check if scores actually changed
            $score_changed = ($match->home_score != $api_scores['home_score']) ||
                ($match->away_score != $api_scores['away_score']) ||
                ($match->status != $api_scores['status']);

            if ($score_changed) {
                $update_data['score_updated_at'] = date('Y-m-d H:i:s');
            }

            $this->db->where('id', $match->id);
            $this->db->update('matches', $update_data);

            // Log the update
            if ($score_changed) {
                $this->log_match_event($match->id, 'score_update', [
                    'old_score' => "{$match->home_score}-{$match->away_score}",
                    'new_score' => "{$api_scores['home_score']}-{$api_scores['away_score']}",
                    'old_status' => $match->status,
                    'new_status' => $api_scores['status'],
                    'source' => 'football-data.org'
                ]);
            }

            return [
                'updated' => true,
                'score_changed' => $score_changed,
                'new_score' => "{$api_scores['home_score']}-{$api_scores['away_score']}",
                'status' => $api_scores['status']
            ];
        } catch (Exception $e) {
            // Fallback to mock data if API fails (for development)
            if (ENVIRONMENT === 'development') {
                return $this->fallback_to_mock_data($match);
            }
            throw $e;
        }
    }

    private function get_live_scores_football_data($match)
    {
        $api_key = $this->Settings_model->get_setting('api_football_key');

        if (!$api_key || $api_key == 'your_api_key_here') {
            throw new Exception('Football-Data.org API key not configured');
        }

        $fixture_id = $match->external_id;
        if (!$fixture_id) {
            throw new Exception('No external fixture ID available for match ' . $match->id);
        }

        $url = "https://api.football-data.org/v4/matches/{$fixture_id}";

        $headers = [
            "X-Auth-Token: {$api_key}",
            "Content-Type: application/json"
        ];

        // Method 1: cURL
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_USERAGENT => 'FootballPredictorApp/1.0',
                CURLOPT_SSL_VERIFYPEER => true,
            ]);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);
        }
        // Method 2: file_get_contents
        elseif (ini_get('allow_url_fopen')) {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => implode("\r\n", $headers),
                    'timeout' => 10
                ]
            ]);

            $response = file_get_contents($url, false, $context);
            $http_code = $response !== false ? 200 : 500;
            $curl_error = $response === false ? 'file_get_contents failed' : '';
        }
        // Method 3: No HTTP support
        else {
            throw new Exception('No HTTP support available. Enable cURL or allow_url_fopen.');
        }

        // Handle rate limiting
        if ($http_code === 429) {
            throw new Exception('Rate limit exceeded - 10 requests/minute');
        }

        if ($http_code === 404) {
            throw new Exception('Fixture not found in Football-Data.org');
        }

        if ($http_code !== 200) {
            throw new Exception("API request failed with code: {$http_code} - {$curl_error}");
        }

        $data = json_decode($response, true);

        if (!$data) {
            throw new Exception("Invalid JSON response from API");
        }

        if (isset($data['error'])) {
            throw new Exception("API Error: " . $data['error']);
        }

        return $this->parse_football_data_response($data);
    }

    private function parse_football_data_response($data)
    {
        $status = $this->map_football_data_status($data['status']);
        $score = $data['score'];

        // Handle score data - use fullTime, fallback to halfTime
        $home_score = $score['fullTime']['home'] ?? $score['halfTime']['home'] ?? null;
        $away_score = $score['fullTime']['away'] ?? $score['halfTime']['away'] ?? null;

        // For scheduled matches, scores should be null
        if ($status === 'scheduled') {
            $home_score = null;
            $away_score = null;
        }

        return [
            'home_score' => $home_score,
            'away_score' => $away_score,
            'status' => $status,
            'minute' => $data['minute'] ?? null,
            'matchday' => $data['matchday'] ?? null,
            'last_updated' => $data['lastUpdated'] ?? null
        ];
    }

    private function map_football_data_status($api_status)
    {
        $status_map = [
            'FINISHED' => 'finished',
            'LIVE' => 'ongoing',
            'IN_PLAY' => 'ongoing',
            'PAUSED' => 'ongoing',
            'SCHEDULED' => 'scheduled',
            'POSTPONED' => 'postponed',
            'SUSPENDED' => 'suspended',
            'CANCELED' => 'cancelled',
            'CANCELLED' => 'cancelled',
            'TIMED' => 'scheduled'
        ];

        return $status_map[$api_status] ?? 'scheduled';
    }

    private function fallback_to_mock_data($match)
    {
        // Only use mock data in development
        if (ENVIRONMENT !== 'development') {
            throw new Exception('API unavailable and mock data disabled in production');
        }

        $mock_scores = $this->get_mock_scores($match);

        $update_data = [
            'home_score' => $mock_scores['home_score'],
            'away_score' => $mock_scores['away_score'],
            'status' => $mock_scores['status'],
            'last_score_update' => date('Y-m-d H:i:s')
        ];

        $score_changed = ($match->home_score != $mock_scores['home_score']) ||
            ($match->away_score != $mock_scores['away_score']) ||
            ($match->status != $mock_scores['status']);

        if ($score_changed) {
            $update_data['score_updated_at'] = date('Y-m-d H:i:s');
        }

        $this->db->where('id', $match->id);
        $this->db->update('matches', $update_data);

        // Log mock update
        if ($score_changed) {
            $this->log_match_event($match->id, 'score_update_mock', [
                'old_score' => "{$match->home_score}-{$match->away_score}",
                'new_score' => "{$mock_scores['home_score']}-{$mock_scores['away_score']}",
                'old_status' => $match->status,
                'new_status' => $mock_scores['status'],
                'source' => 'mock_data'
            ]);
        }

        return [
            'updated' => true,
            'score_changed' => $score_changed,
            'new_score' => "{$mock_scores['home_score']}-{$mock_scores['away_score']}",
            'status' => $mock_scores['status']
        ];
    }

    private function get_mock_scores($match)
    {
        // Mock data simulation - for development only
        $match_time = strtotime($match->match_date . ' ' . $match->match_time);
        $current_time = time();

        // Simulate match progression based on time
        if ($current_time < $match_time - 3600) {
            // Match hasn't started (more than 1 hour before)
            return ['home_score' => null, 'away_score' => null, 'status' => 'scheduled'];
        } elseif ($current_time < $match_time) {
            // Match about to start (within 1 hour)
            return ['home_score' => null, 'away_score' => null, 'status' => 'scheduled'];
        } elseif ($current_time < $match_time + 7200) {
            // Match ongoing - random scores
            $home_score = rand(0, 3);
            $away_score = rand(0, 2);
            return ['home_score' => $home_score, 'away_score' => $away_score, 'status' => 'ongoing'];
        } else {
            // Match finished - final scores
            $home_score = rand(0, 5);
            $away_score = rand(0, 4);
            return ['home_score' => $home_score, 'away_score' => $away_score, 'status' => 'finished'];
        }
    }

    public function evaluate_predictions($match_id)
    {
        $match = $this->db->get_where('matches', ['id' => $match_id])->row();

        if (!$match || $match->status != 'finished' || $match->home_score === null || $match->away_score === null) {
            return false;
        }

        // Get all predictions for this match
        $predictions = $this->db->get_where('user_predictions', ['match_id' => $match_id])->result();

        $updated_count = 0;

        foreach ($predictions as $prediction) {
            $result_type = $this->calculate_prediction_result($prediction, $match);
            $points = $this->calculate_points($result_type);

            $update_data = [
                'is_correct' => ($result_type != 'wrong') ? 1 : 0,
                'points_earned' => $points,
                'result_type' => $result_type,
                'evaluated_at' => date('Y-m-d H:i:s')
            ];

            $this->db->where('id', $prediction->id);
            $this->db->update('user_predictions', $update_data);

            // Update user stats if this is the first evaluation
            if ($prediction->is_correct === NULL) {
                $this->update_user_stats($prediction->user_id, $result_type, $points);
            }

            $updated_count++;
        }

        // Log prediction evaluation
        $this->log_match_event($match_id, 'predictions_evaluated', [
            'total_predictions' => count($predictions),
            'updated_count' => $updated_count
        ]);

        return $updated_count;
    }

    private function calculate_prediction_result($prediction, $match)
    {
        $pred_home = $prediction->predicted_home_score;
        $pred_away = $prediction->predicted_away_score;
        $actual_home = $match->home_score;
        $actual_away = $match->away_score;

        // Check if prediction exists
        if ($pred_home === null || $pred_away === null) {
            return 'wrong';
        }

        // Exact score prediction
        if ($pred_home == $actual_home && $pred_away == $actual_away) {
            return 'exact';
        }

        // Correct outcome (win/draw)
        $pred_outcome = $this->get_match_outcome($pred_home, $pred_away);
        $actual_outcome = $this->get_match_outcome($actual_home, $actual_away);

        if ($pred_outcome == $actual_outcome) {
            return 'correct';
        }

        return 'wrong';
    }

    private function get_match_outcome($home_score, $away_score)
    {
        if ($home_score > $away_score) return 'home_win';
        if ($home_score < $away_score) return 'away_win';
        return 'draw';
    }

    private function calculate_points($result_type)
    {
        $points = [
            'exact' => 3,
            'correct' => 1,
            'wrong' => 0
        ];

        return $points[$result_type] ?? 0;
    }

    private function update_user_stats($user_id, $result_type, $points)
    {
        $user = $this->db->get_where('users', ['id' => $user_id])->row();

        if (!$user) return;

        $update_data = [
            'total_predictions' => $user->total_predictions + 1,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($result_type != 'wrong') {
            $update_data['total_correct_predictions'] = $user->total_correct_predictions + 1;
        }

        // Update total points
        $update_data['total_points'] = $user->total_points + $points;

        $this->db->where('id', $user_id);
        $this->db->update('users', $update_data);

        // Update weekly stats
        $this->update_weekly_stats($user_id, $result_type, $points);
    }

    private function update_weekly_stats($user_id, $result_type, $points)
    {
        $current_week = date('W');
        $current_year = date('Y');

        $weekly_stat = $this->db->get_where('weekly_stats', [
            'user_id' => $user_id,
            'week_number' => $current_week,
            'year' => $current_year
        ])->row();

        if ($weekly_stat) {
            $update_data = [
                'total_predictions' => $weekly_stat->total_predictions + 1,
                'total_points' => $weekly_stat->total_points + $points
            ];

            if ($result_type != 'wrong') {
                $update_data['correct_predictions'] = $weekly_stat->correct_predictions + 1;
            }

            $this->db->where('id', $weekly_stat->id);
            $this->db->update('weekly_stats', $update_data);
        } else {
            $insert_data = [
                'user_id' => $user_id,
                'league_id' => 1, // Default league, should be updated based on match
                'week_number' => $current_week,
                'year' => $current_year,
                'total_predictions' => 1,
                'correct_predictions' => ($result_type != 'wrong') ? 1 : 0,
                'total_points' => $points,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->db->insert('weekly_stats', $insert_data);
        }
    }

    private function log_match_event($match_id, $event_type, $event_data)
    {
        $log_data = [
            'match_id' => $match_id,
            'event_type' => $event_type,
            'event_data' => json_encode($event_data),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('match_events', $log_data);
    }

    public function manual_update_match($match_id)
    {
        $match = $this->db->get_where('matches', ['id' => $match_id])->row();

        if (!$match) {
            return ['status' => 'error', 'message' => 'Match not found'];
        }

        try {
            $result = $this->update_single_match($match);

            if ($result['updated']) {
                if ($result['score_changed'] && $result['status'] === 'finished') {
                    $this->evaluate_predictions($match_id);
                }

                return [
                    'status' => 'success',
                    'message' => 'Match updated successfully',
                    'score_changed' => $result['score_changed'],
                    'new_score' => $result['new_score']
                ];
            } else {
                return [
                    'status' => 'success',
                    'message' => 'No changes detected',
                    'score_changed' => false
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function get_update_status()
    {
        $this->db->select('COUNT(*) as total_matches');
        $this->db->select('SUM(CASE WHEN status = "ongoing" THEN 1 ELSE 0 END) as ongoing_matches');
        $this->db->select('SUM(CASE WHEN status = "finished" THEN 1 ELSE 0 END) as finished_matches');
        $this->db->where('match_date >=', date('Y-m-d', strtotime('-2 days')));
        $result = $this->db->get('matches')->row();

        return [
            'total_recent_matches' => $result->total_matches,
            'ongoing_matches' => $result->ongoing_matches,
            'finished_matches' => $result->finished_matches,
            'next_update_available' => date('Y-m-d H:i:s', time() + 60) // 1 minute from now
        ];
    }
}
