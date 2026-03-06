<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Match_update extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        // Check admin authentication
        if (!$this->session->userdata('admin_logged_in')) {
            redirect('admin/login');
        }

        $this->load->model('Match_update_model');
        $this->load->model('Settings_model');
    }

    public function index()
    {
        // Get auto update setting dengan default value 1 (enabled)
        $auto_update_setting = $this->Settings_model->get_setting('auto_update_scores');
        $auto_update_enabled = ($auto_update_setting !== null && $auto_update_setting !== '') ? intval($auto_update_setting) : 1;

        // Get API key status
        $api_key = $this->Settings_model->get_setting('api_football_key');
        $api_configured = ($api_key && $api_key != 'your_api_key_here');

        $data = [
            'title' => 'Match Score Updates',
            'auto_update_enabled' => $auto_update_enabled,
            'api_configured' => $api_configured,
            'api_key' => $api_configured ? '••••••••' . substr($api_key, -8) : 'Not configured'
        ];

        $this->load->view('templates/admin_header', $data);
        $this->load->view('admin/match_update', $data);
        $this->load->view('templates/admin_footer');
    }

    public function get_status()
    {
        $status = $this->Match_update_model->get_update_status();

        echo json_encode([
            'status' => 'success',
            'data' => $status
        ]);
    }

    public function get_recent_matches()
    {
        $this->load->database();
        $this->db->where('match_date >=', date('Y-m-d', strtotime('-2 days')));
        $this->db->order_by('match_date', 'DESC');
        $this->db->order_by('match_time', 'DESC');
        $matches = $this->db->get('matches')->result();

        echo json_encode([
            'status' => 'success',
            'data' => ['matches' => $matches]
        ]);
    }

    public function get_update_logs()
    {
        $this->load->database();
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit(20);
        $logs = $this->db->get('match_events')->result();

        if (empty($logs)) {
            echo '<p class="text-muted text-center">No update logs found</p>';
            return;
        }

        foreach ($logs as $log) {
            $event_data = json_decode($log->event_data, true);
            echo '<div class="log-entry mb-2 p-2 border rounded">';
            echo '<small class="text-muted">' . $log->created_at . '</small> - ';
            echo '<strong>Match ' . $log->match_id . ':</strong> ';

            switch ($log->event_type) {
                case 'score_update':
                    echo 'Score updated from ' . ($event_data['old_score'] ?? 'N/A') . ' to ' . ($event_data['new_score'] ?? 'N/A');
                    if (isset($event_data['source'])) {
                        echo ' <small class="text-muted">(' . $event_data['source'] . ')</small>';
                    }
                    break;
                case 'predictions_evaluated':
                    echo 'Predictions evaluated: ' . ($event_data['updated_count'] ?? 0) . ' predictions';
                    break;
                case 'api_error':
                    echo 'API Error: ' . ($event_data['error'] ?? 'Unknown error');
                    break;
                case 'score_update_mock':
                    echo 'MOCK: Score updated from ' . ($event_data['old_score'] ?? 'N/A') . ' to ' . ($event_data['new_score'] ?? 'N/A');
                    break;
                default:
                    echo ucfirst(str_replace('_', ' ', $log->event_type));
            }

            echo '</div>';
        }
    }

    public function update_all()
    {
        $result = $this->Match_update_model->update_match_scores();

        echo json_encode([
            'status' => 'success',
            'data' => $result
        ]);
    }

    public function update_single($match_id)
    {
        $result = $this->Match_update_model->manual_update_match($match_id);

        echo json_encode($result);
    }

    public function evaluate_predictions($match_id)
    {
        $result = $this->Match_update_model->evaluate_predictions($match_id);

        if ($result) {
            echo json_encode([
                'status' => 'success',
                'data' => ['evaluated_count' => $result]
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No predictions evaluated or match not finished'
            ]);
        }
    }

    public function toggle_auto_update()
    {
        $enabled = $this->input->post('enabled');

        $this->Settings_model->update_setting('auto_update_scores', $enabled);

        echo json_encode([
            'status' => 'success',
            'message' => 'Auto update setting updated'
        ]);
    }

    public function save_api_key()
    {
        $api_key = $this->input->post('api_key');

        if (empty($api_key)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'API key cannot be empty'
            ]);
            return;
        }

        $result = $this->Settings_model->update_setting('api_football_key', $api_key);

        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => 'API key saved successfully'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to save API key'
            ]);
        }
    }

    public function test_api()
    {
        $api_key = $this->Settings_model->get_setting('api_football_key');

        if (!$api_key || $api_key == 'your_api_key_here') {
            echo json_encode([
                'status' => 'error',
                'message' => 'API key not configured'
            ]);
            return;
        }

        // Test API connection dengan fallback method
        $url = "https://api.football-data.org/v4/areas/2077";

        $headers = [
            "X-Auth-Token: {$api_key}",
            "Content-Type: application/json"
        ];

        // Method 1: cURL (Preferred)
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_USERAGENT => 'FootballPredictorApp/1.0',
                CURLOPT_SSL_VERIFYPEER => false, // For testing only
            ]);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
        }
        // Method 2: file_get_contents (Fallback)
        elseif (ini_get('allow_url_fopen')) {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => implode("\r\n", $headers),
                    'timeout' => 10
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ]
            ]);

            $response = file_get_contents($url, false, $context);
            $http_code = $response !== false ? 200 : 500;
        }
        // Method 3: No HTTP support
        else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No HTTP support available. Enable cURL or allow_url_fopen.'
            ]);
            return;
        }

        if ($http_code === 200) {
            echo json_encode([
                'status' => 'success',
                'message' => 'API connection successful! Football-Data.org is working.'
            ]);
        } else if ($http_code === 429) {
            echo json_encode([
                'status' => 'warning',
                'message' => 'API rate limit exceeded - Please wait 1 minute'
            ]);
        } else if ($http_code === 403) {
            echo json_encode([
                'status' => 'error',
                'message' => 'API access forbidden - Check if your API key is valid'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => "API test failed with HTTP code: {$http_code}"
            ]);
        }
    }

    public function get_api_status()
    {
        $api_key = $this->Settings_model->get_setting('api_football_key');
        $api_configured = ($api_key && $api_key != 'your_api_key_here');

        echo json_encode([
            'status' => 'success',
            'data' => [
                'configured' => $api_configured,
                'key_preview' => $api_configured ? '••••••••' . substr($api_key, -8) : 'Not configured'
            ]
        ]);
    }
}
