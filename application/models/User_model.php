<?php
defined("BASEPATH") OR exit("No direct script access allowed");

class User_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function get_user_by_id($user_id) {
        return $this->db->get_where("users", ["id" => $user_id])->row();
    }
    
    public function get_user_by_username_or_email($username) {
        $this->db->where('username', $username);
        $this->db->or_where('email', $username);
        return $this->db->get('users')->row();
    }
    
    public function get_user_stats($user_id) {
        $this->db->select("
            total_correct_predictions,
            total_predictions,
            balance,
            (total_correct_predictions / GREATEST(total_predictions, 1)) * 100 as accuracy
        ");
        return $this->db->get_where("users", ["id" => $user_id])->row();
    }
    
    public function update_balance($user_id, $amount) {
        $this->db->set("balance", "balance + " . $amount, FALSE);
        $this->db->where("id", $user_id);
        return $this->db->update("users");
    }
    
    public function get_all_admins() {
        return $this->db->get_where("users", ["role" => "admin"])->result();
    }
    
    public function get_all_users() {
        return $this->db->get_where("users", ["role" => "user"])->result();
    }
    
    public function create_user($user_data) {
        return $this->db->insert("users", $user_data);
    }
    
    public function update_user($user_id, $user_data) {
        $this->db->where("id", $user_id);
        return $this->db->update("users", $user_data);
    }
    
    public function delete_user($user_id) {
        $this->db->where("id", $user_id);
        return $this->db->delete("users");
    }
    
    public function get_user_by_email($email) {
        return $this->db->get_where("users", ["email" => $email])->row();
    }
    
    public function get_user_by_username($username) {
        return $this->db->get_where("users", ["username" => $username])->row();
    }
    
    public function count_all_users() {
        return $this->db->count_all("users");
    }
    
    public function count_admins() {
        return $this->db->where("role", "admin")->count_all_results("users");
    }
    
    public function count_regular_users() {
        return $this->db->where("role", "user")->count_all_results("users");
    }
    
    public function get_recent_users($limit = 5) {
        $this->db->order_by("created_at", "DESC");
        $this->db->limit($limit);
        return $this->db->get("users")->result();
    }
}