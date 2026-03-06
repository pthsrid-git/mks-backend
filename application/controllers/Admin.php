<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->model('Match_model');
        $this->load->model('Prediction_model');
        $this->load->model('League_model');
        $this->load->library('form_validation');
        $this->load->library('upload');

        // Check admin authentication dan role
        $this->check_admin_auth();
    }

    private function check_admin_auth()
    {
        if (!$this->session->userdata('admin_logged_in')) {
            redirect('auth/admin_login');
        }

        if ($this->session->userdata('admin_role') != 'admin') {
            $this->session->set_flashdata('error', 'Access denied. Admin privileges required.');
            redirect('auth/admin_login');
        }
    }

    public function index()
    {
        $data['title'] = 'Dashboard';

        // Load models yang diperlukan
        $this->load->model('User_model');
        $this->load->model('Settings_model');

        $data['total_users'] = $this->User_model->count_all_users();
        $data['total_admins'] = $this->User_model->count_admins();
        $data['total_regular_users'] = $this->User_model->count_regular_users();
        $data['total_matches'] = $this->db->count_all('matches');
        $data['total_predictions'] = $this->db->count_all('user_predictions');
        $data['total_leagues'] = $this->db->count_all('leagues');

        $data['recent_users'] = $this->User_model->get_recent_users(5);

        // Get bonus settings untuk dashboard
        $data['bonus_settings'] = $this->Settings_model->get_bonus_settings();

        $this->load->view('templates/admin_header', $data);
        $this->load->view('admin/dashboard', $data);
        $this->load->view('templates/admin_footer');
    }

    public function matches()
    {
        $data['title'] = 'Manage Matches';

        $this->db->select('m.*, ht.name as home_team, at.name as away_team, l.name as league_name');
        $this->db->from('matches m');
        $this->db->join('teams ht', 'ht.id = m.home_team_id');
        $this->db->join('teams at', 'at.id = m.away_team_id');
        $this->db->join('leagues l', 'l.id = m.league_id');
        $this->db->order_by('m.created_at', 'DESC');
        $this->db->limit(100);
        $data['matches'] = $this->db->get()->result();

        // Get data for forms
        $data['leagues'] = $this->db->get('leagues')->result();
        $data['teams'] = $this->db->get('teams')->result();

        $this->load->view('templates/admin_header', $data);
        $this->load->view('admin/matches', $data);
        $this->load->view('templates/admin_footer');
    }

    public function add_match()
    {
        if ($this->input->post()) {
            $this->form_validation->set_rules('league_id', 'League', 'required');
            $this->form_validation->set_rules('home_team_id', 'Home Team', 'required');
            $this->form_validation->set_rules('away_team_id', 'Away Team', 'required');
            $this->form_validation->set_rules('match_date', 'Match Date', 'required');
            $this->form_validation->set_rules('match_time', 'Match Time', 'required');
            $this->form_validation->set_rules('week_number', 'Week Number', 'required');

            if ($this->form_validation->run() == TRUE) {
                $match_data = [
                    'league_id' => $this->input->post('league_id'),
                    'home_team_id' => $this->input->post('home_team_id'),
                    'away_team_id' => $this->input->post('away_team_id'),
                    'match_date' => $this->input->post('match_date'),
                    'match_time' => $this->input->post('match_time'),
                    'week_number' => $this->input->post('week_number'),
                    'season' => $this->input->post('season'),
                    'status' => 'scheduled',
                    'created_at' => date('Y-m-d H:i:s')
                ];

                if ($this->db->insert('matches', $match_data)) {
                    $this->session->set_flashdata('success', 'Match added successfully!');
                } else {
                    $this->session->set_flashdata('error', 'Failed to add match.');
                }
                redirect('admin/matches');
            }
        }
    }

    public function edit_match($id = null)
    {
        if (!$id) {
            show_404();
        }

        // Get match data
        $match = $this->db->get_where('matches', ['id' => $id])->row();
        if (!$match) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('league_id', 'League', 'required');
            $this->form_validation->set_rules('home_team_id', 'Home Team', 'required');
            $this->form_validation->set_rules('away_team_id', 'Away Team', 'required');
            $this->form_validation->set_rules('match_date', 'Match Date', 'required');
            $this->form_validation->set_rules('match_time', 'Match Time', 'required');
            $this->form_validation->set_rules('week_number', 'Week Number', 'required');

            if ($this->form_validation->run() == TRUE) {
                $home_score = $this->input->post('home_score');
                $away_score = $this->input->post('away_score');
                $status     = $this->input->post('status');

                $match_data = [
                    'league_id'    => $this->input->post('league_id'),
                    'home_team_id' => $this->input->post('home_team_id'),
                    'away_team_id' => $this->input->post('away_team_id'),
                    'match_date'   => $this->input->post('match_date'),
                    'match_time'   => $this->input->post('match_time'),
                    'week_number'  => $this->input->post('week_number'),
                    'season'       => $this->input->post('season'),
                    'status'       => $status,
                    'home_score'   => $home_score,
                    'away_score'   => $away_score
                ];

                $this->db->where('id', $id);
                if ($this->db->update('matches', $match_data)) {

                    // ==== UPDATE user_predictions BERDASARKAN SKOR ====
                    // Hanya hitung kalau skor sudah diisi dan status misalnya 'finished'
                    if ($home_score !== '' && $away_score !== '') {
                        $home = (int) $home_score;
                        $away = (int) $away_score;

                        if ($home > $away) {
                            $actual_result = 'home_win';
                        } elseif ($home < $away) {
                            $actual_result = 'away_win';
                        } else {
                            $actual_result = 'draw';
                        }

                        // Update semua prediction utk match ini
                        $sql = "
                            UPDATE user_predictions
                            SET 
                                is_correct = CASE 
                                    WHEN predicted_result = ? THEN 1 
                                    ELSE 0 
                                END,
                                result_type = CASE 
                                    WHEN predicted_result = ? THEN 'correct' 
                                    ELSE 'wrong' 
                                END,
                                points_earned = CASE
                                    WHEN predicted_result = ? THEN 1
                                    ELSE 0
                                END
                            WHERE match_id = ?
                        ";
                        $this->db->query($sql, [$actual_result, $actual_result, $actual_result, $id]);
                    }
                    // ==== END UPDATE user_predictions ====

                    $this->session->set_flashdata('success', 'Match updated successfully!');
                } else {
                    $this->session->set_flashdata('error', 'Failed to update match.');
                }
                redirect('admin/matches');
            }
        }

        $data['title']   = 'Edit Match';
        $data['match']   = $match;
        $data['leagues'] = $this->db->get('leagues')->result();
        $data['teams']   = $this->db->get('teams')->result();

        $this->load->view('templates/admin_header', $data);
        $this->load->view('admin/edit_match', $data);
        $this->load->view('templates/admin_footer');
    }

    public function delete_match($id)
    {
        $id = (int)$id;
        if ($id <= 0) show_404();

        // cek apakah ada prediksi
        $count = $this->db
            ->where('match_id', $id)
            ->count_all_results('user_predictions');

        if ($count > 0) {
            $this->session->set_flashdata(
                'error',
                'Pertandingan tidak dapat dihapus karena sudah ada prediksi user (' . $count . ' data).'
            );
            return redirect('admin/matches');
        }

        $countEvents = $this->db->where('match_id', $id)->count_all_results('match_events');
        $countPreds  = $this->db->where('match_id', $id)->count_all_results('user_predictions');

        if ($countEvents > 0 || $countPreds > 0) {
            $this->session->set_flashdata(
                'error',
                'Pertandingan tidak dapat dihapus karena sudah ada data event/prediksi.'
            );
            return redirect('admin/matches');
        }


        $this->db->where('id', $id)->delete('matches');
        $this->session->set_flashdata('success', 'Pertandingan berhasil dihapus.');
        redirect('admin/matches');
    }


    public function users()
    {
        $data['title'] = 'Manage Users';

        $this->db->select('*, (total_correct_predictions / GREATEST(total_predictions, 1)) * 100 as accuracy');
        $this->db->order_by('created_at', 'DESC');
        $data['users'] = $this->db->get('users')->result();

        $this->load->view('templates/admin_header', $data);
        $this->load->view('admin/users', $data);
        $this->load->view('templates/admin_footer');
    }

    public function add_user()
    {
        if ($this->input->post()) {
            $this->form_validation->set_rules('username', 'Username', 'required|is_unique[users.username]');
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[users.email]');
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
            $this->form_validation->set_rules('role', 'Role', 'required|in_list[admin,user]');

            if ($this->form_validation->run() == TRUE) {
                $user_data = [
                    'username' => $this->input->post('username'),
                    'email' => $this->input->post('email'),
                    'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
                    'role' => $this->input->post('role'),
                    'balance' => $this->input->post('balance') ?: 0.00,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                if ($this->User_model->create_user($user_data)) {
                    $this->session->set_flashdata('success', 'User added successfully! Role: ' . $user_data['role']);
                } else {
                    $this->session->set_flashdata('error', 'Failed to add user.');
                }
            } else {
                $this->session->set_flashdata('error', validation_errors());
            }
        }

        redirect('admin/users');
    }

    public function profile()
    {
        $data['title'] = 'Admin Profile';
        $admin_id = $this->session->userdata('admin_id');
        $data['admin'] = $this->User_model->get_user_by_id($admin_id);

        $this->load->view('templates/admin_header', $data);
        $this->load->view('admin/profile', $data);
        $this->load->view('templates/admin_footer');
    }

    public function update_profile()
    {
        $admin_id = $this->session->userdata('admin_id');

        if ($this->input->post()) {
            $this->form_validation->set_rules('username', 'Username', 'required');
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email');

            if ($this->form_validation->run() == TRUE) {
                $user_data = [
                    'username' => $this->input->post('username'),
                    'email' => $this->input->post('email'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                // Update password hanya jika diisi
                if ($this->input->post('password')) {
                    $user_data['password'] = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
                }

                if ($this->User_model->update_user($admin_id, $user_data)) {
                    // Update session data
                    $this->session->set_userdata([
                        'admin_username' => $user_data['username'],
                        'admin_email' => $user_data['email']
                    ]);

                    $this->session->set_flashdata('success', 'Profile updated successfully!');
                } else {
                    $this->session->set_flashdata('error', 'Failed to update profile.');
                }
            } else {
                $this->session->set_flashdata('error', validation_errors());
            }
        }

        redirect('admin/profile');
    }

    public function edit_user($id = null)
    {
        if (!$id) {
            show_404();
        }

        // Get user data
        $user = $this->db->get_where('users', ['id' => $id])->row();
        if (!$user) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('username', 'Username', 'required');
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email');

            if ($this->form_validation->run() == TRUE) {
                $user_data = [
                    'username' => $this->input->post('username'),
                    'email' => $this->input->post('email'),
                    'balance' => $this->input->post('balance') ?: 0.00,
                    'total_correct_predictions' => $this->input->post('total_correct_predictions'),
                    'total_predictions' => $this->input->post('total_predictions'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                // Update password only if provided
                if ($this->input->post('password')) {
                    $user_data['password'] = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
                }

                $this->db->where('id', $id);
                if ($this->db->update('users', $user_data)) {
                    $this->session->set_flashdata('success', 'User updated successfully!');
                } else {
                    $this->session->set_flashdata('error', 'Failed to update user.');
                }
                redirect('admin/users');
            }
        }

        $data['title'] = 'Edit User';
        $data['user'] = $user;

        $this->load->view('templates/admin_header', $data);
        $this->load->view('admin/edit_user', $data);
        $this->load->view('templates/admin_footer');
    }

    public function delete_user($id = null)
    {
        if (!$id) {
            show_404();
        }

        // Check if user exists
        $user = $this->db->get_where('users', ['id' => $id])->row();
        if (!$user) {
            show_404();
        }

        // Delete the user
        $this->db->where('id', $id);
        if ($this->db->delete('users')) {
            $this->session->set_flashdata('success', 'User deleted successfully!');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete user.');
        }

        redirect('admin/users');
    }

    public function calculate_bonus()
    {
        $week_number = $this->input->post('week_number');
        $year = $this->input->post('year') ?: date('Y');

        if (!$week_number) {
            $this->session->set_flashdata('error', 'Week number is required');
            redirect('admin');
        }

        $this->load->model('Settings_model');
        $bonus_per_league = $this->Settings_model->get_setting('bonus_amount', 100.00);

        $result = $this->Prediction_model->calculate_weekly_bonus($week_number, $year);

        if ($result['total_users'] > 0) {
            $this->session->set_flashdata(
                'success',
                "Bonus calculated successfully!<br>
            <strong>{$result['total_users']} users</strong> across <strong>{$result['leagues_processed']} leagues</strong><br>
            Total distributed: <strong>\${$result['total_bonus']}</strong><br>
            Each league pool: <strong>\${$bonus_per_league}</strong>"
            );
        } else {
            $min_predictions = $this->Settings_model->get_setting('min_predictions_for_bonus', 10);
            $this->session->set_flashdata(
                'info',
                "No eligible users found for bonus in week {$week_number}.<br>
            Minimum <strong>{$min_predictions} correct predictions</strong> required per league."
            );
        }

        redirect('admin');
    }

    public function leagues()
    {
        $data['title'] = 'Manage Leagues';
        $data['leagues'] = $this->db->get('leagues')->result();

        $this->load->view('templates/admin_header', $data);
        $this->load->view('admin/leagues', $data);
        $this->load->view('templates/admin_footer');
    }

    public function add_league()
    {
        if ($this->input->post()) {
            $this->form_validation->set_rules('name', 'League Name', 'required');
            $this->form_validation->set_rules('country', 'Country', 'required');
            $this->form_validation->set_rules('season', 'Season', 'required');

            if ($this->form_validation->run() == TRUE) {
                $league_data = [
                    'name' => $this->input->post('name'),
                    'country' => $this->input->post('country'),
                    'season' => $this->input->post('season'),
                    'is_active' => $this->input->post('is_active') ? 1 : 0,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                if ($this->db->insert('leagues', $league_data)) {
                    $this->session->set_flashdata('success', 'League added successfully!');
                } else {
                    $this->session->set_flashdata('error', 'Failed to add league.');
                }
            } else {
                $this->session->set_flashdata('error', validation_errors());
            }
        }

        redirect('admin/leagues');
    }

    public function edit_league($id = null)
    {
        if (!$id) {
            show_404();
        }

        $league = $this->db->get_where('leagues', ['id' => $id])->row();
        if (!$league) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('name', 'League Name', 'required');
            $this->form_validation->set_rules('country', 'Country', 'required');
            $this->form_validation->set_rules('season', 'Season', 'required');

            if ($this->form_validation->run() == TRUE) {
                $league_data = [
                    'name' => $this->input->post('name'),
                    'country' => $this->input->post('country'),
                    'season' => $this->input->post('season'),
                    'is_active' => $this->input->post('is_active') ? 1 : 0,
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $this->db->where('id', $id);
                if ($this->db->update('leagues', $league_data)) {
                    $this->session->set_flashdata('success', 'League updated successfully!');
                } else {
                    $this->session->set_flashdata('error', 'Failed to update league.');
                }
                redirect('admin/leagues');
            }
        }

        $data['title'] = 'Edit League';
        $data['league'] = $league;

        $this->load->view('templates/admin_header', $data);
        $this->load->view('admin/edit_league', $data);
        $this->load->view('templates/admin_footer');
    }

    public function delete_league($id = null)
    {
        if (!$id) {
            show_404();
        }

        $league = $this->db->get_where('leagues', ['id' => $id])->row();
        if (!$league) {
            show_404();
        }

        $this->db->where('id', $id);
        if ($this->db->delete('leagues')) {
            $this->session->set_flashdata('success', 'League deleted successfully!');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete league.');
        }

        redirect('admin/leagues');
    }

    public function teams()
    {
        $data['title'] = 'Manage Teams';

        $this->db->select('t.*, l.name as league_name');
        $this->db->from('teams t');
        $this->db->join('leagues l', 'l.id = t.league_id', 'left');
        $this->db->order_by('t.name', 'ASC');
        $data['teams'] = $this->db->get()->result();

        $data['leagues'] = $this->db->get('leagues')->result();

        $this->load->view('templates/admin_header', $data);
        $this->load->view('admin/teams', $data);
        $this->load->view('templates/admin_footer');
    }

    public function add_team()
    {
        if ($this->input->post()) {
            $this->form_validation->set_rules('name', 'Team Name', 'required');
            $this->form_validation->set_rules('league_id', 'League', 'required');

            if ($this->form_validation->run() == TRUE) {
                $team_data = [
                    'name' => $this->input->post('name'),
                    'league_id' => $this->input->post('league_id'),
                    'created_at' => date('Y-m-d H:i:s')
                ];

                // Handle logo upload
                if (!empty($_FILES['logo']['name'])) {
                    $upload_config = [
                        'upload_path' => './uploads/teams/',
                        'allowed_types' => 'jpg|jpeg|png|gif',
                        'max_size' => 2048,
                        'encrypt_name' => TRUE
                    ];

                    $this->upload->initialize($upload_config);

                    if ($this->upload->do_upload('logo')) {
                        $upload_data = $this->upload->data();
                        $team_data['logo'] = 'uploads/teams/' . $upload_data['file_name'];
                    } else {
                        $this->session->set_flashdata('error', 'Logo upload failed: ' . $this->upload->display_errors());
                        redirect('admin/teams');
                    }
                } else {
                    // Use default logo URL if provided
                    $team_data['logo'] = $this->input->post('logo_url') ?: NULL;
                }

                if ($this->db->insert('teams', $team_data)) {
                    $this->session->set_flashdata('success', 'Team added successfully!');
                } else {
                    $this->session->set_flashdata('error', 'Failed to add team.');
                }
            } else {
                $this->session->set_flashdata('error', validation_errors());
            }
        }

        redirect('admin/teams');
    }

    public function edit_team($id = null)
    {
        if (!$id) {
            show_404();
        }

        $team = $this->db->get_where('teams', ['id' => $id])->row();
        if (!$team) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('name', 'Team Name', 'required');
            $this->form_validation->set_rules('league_id', 'League', 'required');

            if ($this->form_validation->run() == TRUE) {
                $team_data = [
                    'name' => $this->input->post('name'),
                    'league_id' => $this->input->post('league_id')
                ];

                // Handle logo upload
                if (!empty($_FILES['logo']['name'])) {
                    $upload_config = [
                        'upload_path' => './uploads/teams/',
                        'allowed_types' => 'jpg|jpeg|png|gif',
                        'max_size' => 2048,
                        'encrypt_name' => TRUE
                    ];

                    $this->upload->initialize($upload_config);

                    if ($this->upload->do_upload('logo')) {
                        // Delete old logo if exists
                        if ($team->logo && file_exists($team->logo)) {
                            unlink($team->logo);
                        }

                        $upload_data = $this->upload->data();
                        $team_data['logo'] = 'uploads/teams/' . $upload_data['file_name'];
                    } else {
                        $this->session->set_flashdata('error', 'Logo upload failed: ' . $this->upload->display_errors());
                        redirect('admin/teams');
                    }
                } elseif ($this->input->post('logo_url')) {
                    // Use logo URL if provided
                    $team_data['logo'] = $this->input->post('logo_url');
                }

                $this->db->where('id', $id);
                if ($this->db->update('teams', $team_data)) {
                    $this->session->set_flashdata('success', 'Team updated successfully!');
                } else {
                    $this->session->set_flashdata('error', 'Failed to update team.');
                }
                redirect('admin/teams');
            }
        }

        $data['title'] = 'Edit Team';
        $data['team'] = $team;
        $data['leagues'] = $this->db->get('leagues')->result();

        $this->load->view('templates/admin_header', $data);
        $this->load->view('admin/edit_team', $data);
        $this->load->view('templates/admin_footer');
    }

    public function delete_team($id = null)
    {
        if (!$id) {
            show_404();
        }

        $team = $this->db->get_where('teams', ['id' => $id])->row();
        if (!$team) {
            show_404();
        }

        // Delete logo file if exists
        if ($team->logo && file_exists($team->logo)) {
            unlink($team->logo);
        }

        $this->db->where('id', $id);
        if ($this->db->delete('teams')) {
            $this->session->set_flashdata('success', 'Team deleted successfully!');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete team.');
        }

        redirect('admin/teams');
    }

    // Tambahkan di Admin.php setelah constructor
    public function settings()
    {
        $data['title'] = 'System Settings';
        $this->load->model('Settings_model');
        $data['settings'] = $this->Settings_model->get_all_settings();

        // Group settings by category
        $data['bonus_settings'] = $this->Settings_model->get_bonus_settings();

        $this->load->view('templates/admin_header', $data);
        $this->load->view('admin/settings', $data);
        $this->load->view('templates/admin_footer');
    }

    public function update_settings()
    {
        if ($this->input->post()) {
            $this->load->model('Settings_model');

            $settings_data = $this->input->post('settings');

            if ($this->Settings_model->update_settings_batch($settings_data)) {
                $this->session->set_flashdata('success', 'Settings updated successfully!');
            } else {
                $this->session->set_flashdata('error', 'Failed to update settings.');
            }
        }

        redirect('admin/settings');
    }

    public function update_bonus_settings()
    {
        if ($this->input->post()) {
            $this->load->model('Settings_model');

            $bonus_data = [
                'min_predictions_for_bonus' => $this->input->post('min_predictions'),
                'bonus_amount' => $this->input->post('bonus_amount'),
                'max_predictions_per_week' => $this->input->post('max_predictions')
            ];

            if ($this->Settings_model->update_settings_batch($bonus_data)) {
                $this->session->set_flashdata('success', 'Bonus settings updated successfully!');
            } else {
                $this->session->set_flashdata('error', 'Failed to update bonus settings.');
            }
        }

        redirect('admin/settings');
    }

    public function bonus_reports()
    {
        $data['title'] = 'Bonus Distribution Reports (Per League)';

        $this->db->select('
        bp.*,
        u.username,
        l.name as league_name,
        bp.bonus_amount as individual_share,
        bp.league_bonus_pool
    ');
        $this->db->from('bonus_payments bp');
        $this->db->join('users u', 'u.id = bp.user_id');
        $this->db->join('leagues l', 'l.id = bp.league_id');
        $this->db->order_by('bp.paid_at', 'DESC');
        $data['bonus_payments'] = $this->db->get()->result();

        $this->load->view('templates/admin_header', $data);
        $this->load->view('admin/bonus_reports', $data);
        $this->load->view('templates/admin_footer');
    }

    public function match_updates()
    {
        $data['title'] = 'Match Updates & Scores';

        $this->load->model('Match_update_model');
        $this->load->model('Settings_model');

        // Get recent matches with predictions
        $this->db->select('m.*, ht.name as home_team, at.name as away_team, l.name as league_name');
        $this->db->from('matches m');
        $this->db->join('teams ht', 'ht.id = m.home_team_id');
        $this->db->join('teams at', 'at.id = m.away_team_id');
        $this->db->join('leagues l', 'l.id = m.league_id');
        $this->db->where('m.match_date >=', date('Y-m-d', strtotime('-7 days')));
        $this->db->order_by('m.match_date DESC, m.match_time DESC');
        $data['matches'] = $this->db->get()->result();

        // Get update settings
        $data['update_settings'] = [
            'auto_update' => $this->Settings_model->get_setting('auto_update_scores', 1),
            'update_interval' => $this->Settings_model->get_setting('update_interval_minutes', 5)
        ];

        $this->load->view('templates/admin_header', $data);
        $this->load->view('admin/match_updates', $data);
        $this->load->view('templates/admin_footer');
    }

    public function manual_score_update()
    {
        $this->load->model('Match_update_model');

        $result = $this->Match_update_model->update_match_scores();

        if ($result['status'] == 'success') {
            $this->session->set_flashdata(
                'success',
                "Score update completed! Updated {$result['updated']} out of {$result['total_checked']} matches."
            );
        } else {
            $this->session->set_flashdata(
                'error',
                "Score update failed: {$result['message']}"
            );
        }

        redirect('admin/match-updates');
    }

    public function update_api_settings()
    {
        if ($this->input->post()) {
            $this->load->model('Settings_model');

            $settings_data = [
                'api_football_key' => $this->input->post('api_key'),
                'api_football_host' => $this->input->post('api_host'),
                'auto_update_scores' => $this->input->post('auto_update') ? 1 : 0,
                'update_interval_minutes' => $this->input->post('update_interval')
            ];

            if ($this->Settings_model->update_settings_batch($settings_data)) {
                $this->session->set_flashdata('success', 'API settings updated successfully!');
            } else {
                $this->session->set_flashdata('error', 'Failed to update API settings.');
            }
        }

        redirect('admin/match-updates');
    }

    public function force_score_update($match_id = null)
    {
        $this->load->model('Match_update_model');

        $result = $this->Match_update_model->force_score_update($match_id);

        if ($result['updated']) {
            $this->session->set_flashdata(
                'success',
                $match_id ? "Score updated for match {$match_id}" : "All scores updated successfully!"
            );
        } else {
            $this->session->set_flashdata(
                'error',
                $result['error'] ?? 'Failed to update scores'
            );
        }

        redirect('admin/match-updates');
    }

    public function create_test_matches()
    {
        $this->load->model('Match_update_model');

        $created = $this->Match_update_model->create_test_matches();

        if ($created > 0) {
            $this->session->set_flashdata('success', "Created {$created} test matches for demonstration!");
        } else {
            $this->session->set_flashdata('error', 'Failed to create test matches');
        }

        redirect('admin/match-updates');
    }

    public function reset_all_scores()
    {
        $this->db->update('matches', [
            'home_score' => null,
            'away_score' => null,
            'status' => 'scheduled',
            'last_score_update' => null,
            'score_updated_at' => null
        ]);

        $affected = $this->db->affected_rows();
        $this->session->set_flashdata('success', "Reset scores for {$affected} matches!");

        redirect('admin/match-updates');
    }
}
