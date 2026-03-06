<?php
defined("BASEPATH") OR exit("No direct script access allowed");

class Match_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
    }

    /**
     * Get all matches
     */
    public function get_all_matches() {
        return $this->db->get('matches')->result();
    }

    /**
     * Get match by ID
     */
    public function get_match_by_id($id) {
        return $this->db->get_where('matches', ['id' => $id])->row();
    }

    /**
     * Get match by ID with team details
     */
    public function get_match_with_details($id) {
        $this->db->select("
            m.*,
            ht.name as home_team_name,
            at.name as away_team_name,
            l.name as league_name
        ");
        $this->db->from("matches m");
        $this->db->join("teams ht", "ht.id = m.home_team_id");
        $this->db->join("teams at", "at.id = m.away_team_id");
        $this->db->join("leagues l", "l.id = m.league_id");
        $this->db->where("m.id", $id);
        
        return $this->db->get()->row();
    }
    
    /**
     * Get matches by week
     */
    public function get_matches_by_week($league_id, $week_number) {
        $this->db->select("
            m.*,
            ht.name as home_team_name,
            at.name as away_team_name,
            l.name as league_name
        ");
        $this->db->from("matches m");
        $this->db->join("teams ht", "ht.id = m.home_team_id");
        $this->db->join("teams at", "at.id = m.away_team_id");
        $this->db->join("leagues l", "l.id = m.league_id");
        $this->db->where("m.league_id", $league_id);
        $this->db->where("m.week_number", $week_number);
        $this->db->order_by("m.match_date, m.match_time");
        
        return $this->db->get()->result();
    }
    
    /**
     * Get upcoming matches
     */
    public function get_upcoming_matches($limit = 10) {
        $this->db->select("
            m.*,
            ht.name as home_team_name,
            at.name as away_team_name,
            l.name as league_name
        ");
        $this->db->from("matches m");
        $this->db->join("teams ht", "ht.id = m.home_team_id");
        $this->db->join("teams at", "at.id = m.away_team_id");
        $this->db->join("leagues l", "l.id = m.league_id");
        $this->db->where("m.status", "scheduled");
        $this->db->where("m.match_date >=", date("Y-m-d"));
        $this->db->order_by("m.match_date, m.match_time");
        $this->db->limit($limit);
        
        return $this->db->get()->result();
    }

    /**
     * Get finished matches
     */
    public function get_finished_matches($limit = 10) {
        $this->db->select("
            m.*,
            ht.name as home_team_name,
            at.name as away_team_name,
            l.name as league_name
        ");
        $this->db->from("matches m");
        $this->db->join("teams ht", "ht.id = m.home_team_id");
        $this->db->join("teams at", "at.id = m.away_team_id");
        $this->db->join("leagues l", "l.id = m.league_id");
        $this->db->where("m.status", "finished");
        $this->db->order_by("m.match_date DESC, m.match_time DESC");
        $this->db->limit($limit);
        
        return $this->db->get()->result();
    }

    /**
     * Get ongoing matches
     */
    public function get_ongoing_matches() {
        $this->db->select("
            m.*,
            ht.name as home_team_name,
            at.name as away_team_name,
            l.name as league_name
        ");
        $this->db->from("matches m");
        $this->db->join("teams ht", "ht.id = m.home_team_id");
        $this->db->join("teams at", "at.id = m.away_team_id");
        $this->db->join("leagues l", "l.id = m.league_id");
        $this->db->where("m.status", "ongoing");
        $this->db->order_by("m.match_date, m.match_time");
        
        return $this->db->get()->result();
    }

    /**
     * Get matches by status
     */
    public function get_matches_by_status($status) {
        $this->db->select("
            m.*,
            ht.name as home_team_name,
            at.name as away_team_name,
            l.name as league_name
        ");
        $this->db->from("matches m");
        $this->db->join("teams ht", "ht.id = m.home_team_id");
        $this->db->join("teams at", "at.id = m.away_team_id");
        $this->db->join("leagues l", "l.id = m.league_id");
        $this->db->where("m.status", $status);
        $this->db->order_by("m.match_date, m.match_time");
        
        return $this->db->get()->result();
    }

    /**
     * Update match scores
     */
    public function update_match_scores($match_id, $home_score, $away_score, $status = null) {
        $data = [
            'home_score' => $home_score,
            'away_score' => $away_score,
            'last_score_update' => date('Y-m-d H:i:s')
        ];
        
        if ($status) {
            $data['status'] = $status;
        }
        
        $this->db->where('id', $match_id);
        return $this->db->update('matches', $data);
    }

    /**
     * Get matches for prediction (upcoming matches)
     */
    public function get_matches_for_prediction($user_id) {
        $this->db->select("
            m.*,
            ht.name as home_team_name,
            at.name as away_team_name,
            l.name as league_name,
            up.predicted_home_score,
            up.predicted_away_score,
            up.is_correct,
            up.points_earned
        ");
        $this->db->from("matches m");
        $this->db->join("teams ht", "ht.id = m.home_team_id");
        $this->db->join("teams at", "at.id = m.away_team_id");
        $this->db->join("leagues l", "l.id = m.league_id");
        $this->db->join("user_predictions up", "up.match_id = m.id AND up.user_id = $user_id", "left");
        $this->db->where("m.status", "scheduled");
        $this->db->where("m.match_date >=", date('Y-m-d'));
        $this->db->order_by("m.match_date, m.match_time");
        
        return $this->db->get()->result();
    }
}