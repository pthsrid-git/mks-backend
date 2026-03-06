<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('User_model');
    }
    
    public function admin_login() {
        if ($this->session->userdata('admin_logged_in')) {
            redirect('admin');
        }
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('username', 'Username', 'required');
            $this->form_validation->set_rules('password', 'Password', 'required');
            
            if ($this->form_validation->run() == TRUE) {
                $username = $this->input->post('username');
                $password = $this->input->post('password');
                
                // Cari user by username atau email menggunakan method baru
                $user = $this->User_model->get_user_by_username_or_email($username);
                
                // Check jika user ada, password benar, dan role admin
                if ($user && password_verify($password, $user->password)) {
                    if ($user->role == 'admin') {
                        $this->session->set_userdata([
                            'admin_logged_in' => TRUE,
                            'admin_id' => $user->id,
                            'admin_username' => $user->username,
                            'admin_email' => $user->email,
                            'admin_role' => $user->role
                        ]);
                        $this->session->set_flashdata('success', 'Welcome back, ' . $user->username . '!');
                        redirect('admin');
                    } else {
                        $this->session->set_flashdata('error', 'Access denied. Admin privileges required.');
                    }
                } else {
                    $this->session->set_flashdata('error', 'Invalid username or password');
                }
            }
        }
        
        $data['title'] = 'Admin Login';
        $this->load->view('templates/auth_header', $data);
        $this->load->view('auth/admin_login');
        $this->load->view('templates/admin_footer');
    }
    
    public function admin_logout() {
        $this->session->unset_userdata([
            'admin_logged_in', 
            'admin_id', 
            'admin_username', 
            'admin_email',
            'admin_role'
        ]);
        $this->session->set_flashdata('success', 'You have been logged out successfully');
        redirect('auth/admin_login');
    }
}