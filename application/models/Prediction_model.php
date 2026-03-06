<?php
defined("BASEPATH") or exit("No direct script access allowed");

class Prediction_model extends CI_Model
{

    public function submit_prediction($user_id, $match_id, $home_score, $away_score)
    {
        $data = [
            "user_id" => $user_id,
            "match_id" => $match_id,
            "predicted_home_score" => $home_score,
            "predicted_away_score" => $away_score,
            "predicted_at" => date("Y-m-d H:i:s")
        ];

        return $this->db->insert("user_predictions", $data);
    }

    public function get_user_predictions($user_id)
    {
        $this->db->select('
        p.*,
        m.match_date,
        m.match_time,
        m.week_number,
        m.status AS match_status,
        m.league_id,
        m.home_score,
        m.away_score,
        leagues.name AS league_name,
        home_team.name AS home_team_name,
        home_team.logo AS home_logo,
        away_team.name AS away_team_name,
        away_team.logo AS away_logo
    ');
        $this->db->from('user_predictions AS p');
        $this->db->join('matches AS m', 'm.id = p.match_id', 'left');
        $this->db->join('leagues', 'leagues.id = m.league_id', 'left');
        $this->db->join('teams AS home_team', 'home_team.id = m.home_team_id', 'left');
        $this->db->join('teams AS away_team', 'away_team.id = m.away_team_id', 'left');
        $this->db->where('p.user_id', $user_id);
        $this->db->order_by('m.match_date', 'DESC');
        $this->db->order_by('m.match_time', 'DESC');

        $rows = $this->db->get()->result();

        // Normalisasi logo → full URL kalau masih path relatif
        foreach ($rows as &$r) {
            if (!empty($r->home_logo) && strpos($r->home_logo, 'http') !== 0) {
                $r->home_logo = base_url($r->home_logo);
            }
            if (!empty($r->away_logo) && strpos($r->away_logo, 'http') !== 0) {
                $r->away_logo = base_url($r->away_logo);
            }
        }

        return $rows;
    }


    // application/models/Prediction_model.php

    public function get_prediction($user_id, $match_id)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('match_id', $match_id);
        $query = $this->db->get('user_predictions');

        return $query->row();
    }

    public function create_prediction($data)
    {
        return $this->db->insert('user_predictions', $data);
    }

    public function update_prediction($prediction_id, $data)
    {
        $this->db->where('id', $prediction_id);
        return $this->db->update('user_predictions', $data);
    }

    // application/models/Prediction_model.php

    public function get_user_predictions_with_match_details($user_id)
    {
        $this->db->select('
        user_predictions.*,
        matches.match_date,
        matches.match_time,
        matches.home_team_id,
        matches.away_team_id,
        home_team.name as home_team_name,
        away_team.name as away_team_name,
        leagues.name as league_name,
        matches.home_score,
        matches.away_score,
        matches.status as match_status
    ');

        $this->db->from('user_predictions');
        $this->db->join('matches', 'matches.id = user_predictions.match_id', 'left');
        $this->db->join('teams as home_team', 'home_team.id = matches.home_team_id', 'left');
        $this->db->join('teams as away_team', 'away_team.id = matches.away_team_id', 'left');
        $this->db->join('leagues', 'leagues.id = matches.league_id', 'left');

        $this->db->where('user_predictions.user_id', $user_id);
        $this->db->order_by('matches.match_date', 'DESC');
        $this->db->order_by('matches.match_time', 'DESC');

        $query = $this->db->get();
        return $query->result();
    }

    // Update method calculate_weekly_bonus
    public function calculate_weekly_bonus($week_number, $year)
    {
        $this->load->model('Settings_model');

        // Get dynamic settings
        $min_predictions = $this->Settings_model->get_setting('min_predictions_for_bonus', 10);
        $total_bonus_per_league = $this->Settings_model->get_setting('bonus_amount', 100.00);

        // Get all active leagues
        $leagues = $this->db->get_where('leagues', ['is_active' => TRUE])->result();

        $total_bonus_distributed = 0;
        $total_eligible_users = 0;

        foreach ($leagues as $league) {
            // Get eligible users for this specific league
            $this->db->select('
            ws.user_id,
            ws.league_id,
            ws.week_number,
            ws.year,
            ws.correct_predictions,
            u.username,
            u.balance,
            l.name as league_name
        ');
            $this->db->from('weekly_stats ws');
            $this->db->join('users u', 'u.id = ws.user_id');
            $this->db->join('leagues l', 'l.id = ws.league_id');
            $this->db->where('ws.league_id', $league->id);
            $this->db->where('ws.week_number', $week_number);
            $this->db->where('ws.year', $year);
            $this->db->where('ws.correct_predictions >=', $min_predictions);
            $this->db->where('ws.has_received_bonus', FALSE);

            $eligible_users = $this->db->get()->result();
            $bonus_count = count($eligible_users);

            if ($bonus_count == 0) {
                continue; // No eligible users in this league
            }

            // Calculate equal share for each eligible user in this league
            $bonus_per_user = $total_bonus_per_league / $bonus_count;

            foreach ($eligible_users as $user) {
                $bonus_data = [
                    'user_id' => $user->user_id,
                    'league_id' => $user->league_id,
                    'week_number' => $week_number,
                    'year' => $year,
                    'bonus_amount' => $bonus_per_user, // Individual share from league pool
                    'correct_predictions' => $user->correct_predictions,
                    'total_eligible_users' => $bonus_count, // Store total eligible count in this league
                    'league_bonus_pool' => $total_bonus_per_league, // Store league bonus pool amount
                    'payment_status' => 'paid',
                    'paid_at' => date('Y-m-d H:i:s')
                ];

                $this->db->insert('bonus_payments', $bonus_data);
                $bonus_id = $this->db->insert_id();

                // Update balance with individual share
                $this->db->set('balance', "balance + $bonus_per_user", FALSE);
                $this->db->where('id', $user->user_id);
                $this->db->update('users');

                // Update weekly stats to mark as received
                $this->db->set('has_received_bonus', TRUE);
                $this->db->where('user_id', $user->user_id);
                $this->db->where('league_id', $user->league_id);
                $this->db->where('week_number', $week_number);
                $this->db->where('year', $year);
                $this->db->update('weekly_stats');

                $transaction_data = [
                    'user_id' => $user->user_id,
                    'type' => 'bonus',
                    'amount' => $bonus_per_user,
                    'description' => "Bonus from {$user->league_name}: {$user->correct_predictions} correct predictions in week {$week_number} ({$bonus_count} users shared ${$total_bonus_per_league})",
                    'reference_id' => $bonus_id,
                    'status' => 'completed',
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $this->db->insert('transactions', $transaction_data);

                $total_bonus_distributed += $bonus_per_user;
            }

            $total_eligible_users += $bonus_count;
        }

        return [
            'total_users' => $total_eligible_users,
            'total_bonus' => $total_bonus_distributed,
            'leagues_processed' => count($leagues)
        ];
    }
}
