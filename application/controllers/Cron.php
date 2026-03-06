<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        
        // Only allow CLI access
        if (!$this->input->is_cli_request()) {
            show_error('Access denied', 403);
        }
    }
    
    public function update_scores() {
        $this->load->model('Match_update_model');
        
        echo "==========================================" . PHP_EOL;
        echo "Starting score update: " . date('Y-m-d H:i:s') . PHP_EOL;
        echo "==========================================" . PHP_EOL;
        
        $result = $this->Match_update_model->update_match_scores();
        
        echo "Update completed: " . date('Y-m-d H:i:s') . PHP_EOL;
        echo "Results:" . PHP_EOL;
        echo "- Status: " . $result['status'] . PHP_EOL;
        echo "- Updated: " . $result['updated'] . " matches" . PHP_EOL;
        echo "- Total checked: " . $result['total_checked'] . PHP_EOL;
        
        if (!empty($result['errors'])) {
            echo "- Errors: " . count($result['errors']) . PHP_EOL;
            foreach ($result['errors'] as $error) {
                echo "  * " . $error . PHP_EOL;
            }
        }
        
        echo "==========================================" . PHP_EOL . PHP_EOL;
    }
    
    public function test_manual_update($match_id) {
        $this->load->model('Match_update_model');
        
        echo "==========================================" . PHP_EOL;
        echo "Manual update test for match: " . $match_id . PHP_EOL;
        echo "Time: " . date('Y-m-d H:i:s') . PHP_EOL;
        echo "==========================================" . PHP_EOL;
        
        $result = $this->Match_update_model->manual_update_match($match_id);
        
        echo "Result: " . json_encode($result, JSON_PRETTY_PRINT) . PHP_EOL;
        echo "==========================================" . PHP_EOL . PHP_EOL;
    }
    
    public function get_status() {
        $this->load->model('Match_update_model');
        
        echo "==========================================" . PHP_EOL;
        echo "Match Update System Status" . PHP_EOL;
        echo "Time: " . date('Y-m-d H:i:s') . PHP_EOL;
        echo "==========================================" . PHP_EOL;
        
        $status = $this->Match_update_model->get_update_status();
        
        echo "Recent matches (last 2 days): " . $status['total_recent_matches'] . PHP_EOL;
        echo "Ongoing matches: " . $status['ongoing_matches'] . PHP_EOL;
        echo "Finished matches: " . $status['finished_matches'] . PHP_EOL;
        echo "Next update available: " . $status['next_update_available'] . PHP_EOL;
        echo "==========================================" . PHP_EOL . PHP_EOL;
    }
    
    public function evaluate_predictions($match_id) {
        $this->load->model('Match_update_model');
        
        echo "==========================================" . PHP_EOL;
        echo "Evaluating predictions for match: " . $match_id . PHP_EOL;
        echo "Time: " . date('Y-m-d H:i:s') . PHP_EOL;
        echo "==========================================" . PHP_EOL;
        
        $result = $this->Match_update_model->evaluate_predictions($match_id);
        
        if ($result) {
            echo "Successfully evaluated " . $result . " predictions" . PHP_EOL;
        } else {
            echo "No predictions evaluated (match may not be finished)" . PHP_EOL;
        }
        
        echo "==========================================" . PHP_EOL . PHP_EOL;
    }
}