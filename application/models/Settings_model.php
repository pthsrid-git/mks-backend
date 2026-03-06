<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function get_setting($key, $default = null) {
        $setting = $this->db->get_where('system_settings', ['setting_key' => $key])->row();
        
        if ($setting && !empty($setting->setting_value)) {
            // Convert value based on type
            switch ($setting->setting_type) {
                case 'integer':
                    return (int) $setting->setting_value;
                case 'decimal':
                    return (float) $setting->setting_value;
                case 'boolean':
                    return (bool) $setting->setting_value;
                default:
                    return $setting->setting_value;
            }
        }
        
        return $default;
    }
    
    public function get_all_settings() {
        $settings = $this->db->get('system_settings')->result();
        
        // Jika table settings kosong, insert default settings
        if (empty($settings)) {
            $this->insert_default_settings();
            $settings = $this->db->get('system_settings')->result();
        }
        
        return $settings;
    }
    
    public function update_setting($key, $value) {
        $setting = $this->db->get_where('system_settings', ['setting_key' => $key])->row();
        
        if ($setting) {
            $data = [
                'setting_value' => $value,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->where('setting_key', $key);
            return $this->db->update('system_settings', $data);
        }
        
        return false;
    }
    
    public function update_settings_batch($settings) {
        foreach ($settings as $key => $value) {
            $this->update_setting($key, $value);
        }
        return true;
    }
    
    public function get_bonus_settings() {
        return [
            'min_predictions' => $this->get_setting('min_predictions_for_bonus', 10),
            'bonus_amount' => $this->get_setting('bonus_amount', 100.00),
            'max_predictions_per_week' => $this->get_setting('max_predictions_per_week', 20)
        ];
    }
    
    private function insert_default_settings() {
        $default_settings = [
            [
                'setting_key' => 'min_predictions_for_bonus',
                'setting_value' => '10',
                'setting_type' => 'integer',
                'description' => 'Minimum correct predictions required to get bonus'
            ],
            [
                'setting_key' => 'bonus_amount',
                'setting_value' => '100.00',
                'setting_type' => 'decimal',
                'description' => 'Bonus amount in USD for achieving minimum predictions'
            ],
            [
                'setting_key' => 'max_predictions_per_week',
                'setting_value' => '20',
                'setting_type' => 'integer',
                'description' => 'Maximum predictions a user can make per week'
            ],
            [
                'setting_key' => 'prediction_deadline_minutes',
                'setting_value' => '5',
                'setting_type' => 'integer',
                'description' => 'Minutes before match start when prediction closes'
            ],
            [
                'setting_key' => 'registration_bonus',
                'setting_value' => '10.00',
                'setting_type' => 'decimal',
                'description' => 'Bonus amount for new user registration'
            ],
            [
                'setting_key' => 'system_currency',
                'setting_value' => 'USD',
                'setting_type' => 'string',
                'description' => 'System currency code'
            ],
            [
                'setting_key' => 'maintenance_mode',
                'setting_value' => '0',
                'setting_type' => 'boolean',
                'description' => 'System maintenance mode'
            ]
        ];
        
        foreach ($default_settings as $setting) {
            $this->db->insert('system_settings', $setting);
        }
    }
}