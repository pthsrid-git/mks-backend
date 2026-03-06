<?php
// Cron job untuk auto update scores
defined('BASEPATH') OR exit('No direct script access allowed');

class Auto_update_scores extends CI_Controller {
    
    public function index() {
        // Load required models
        $this->load->model('Match_update_model');
        $this->load->model('Settings_model');
        
        // Check if auto update is enabled
        if (!$this->Settings_model->get_setting('auto_update_scores', 1)) {
            log_message('info', 'Auto score updates are disabled');
            exit;
        }
        
        $result = $this->Match_update_model->update_match_scores();
        
        // Log result
        log_message('info', 'Auto score update: ' . json_encode($result));
        
        echo json_encode($result);
    }
}