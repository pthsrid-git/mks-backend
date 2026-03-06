<?php
defined("BASEPATH") OR exit("No direct script access allowed");

class League_model extends CI_Model {
    
    public function get_all_leagues() {
        return $this->db->get_where("leagues", ["is_active" => TRUE])->result();
    }
    
    public function get_league_by_id($league_id) {
        return $this->db->get_where("leagues", ["id" => $league_id])->row();
    }
}