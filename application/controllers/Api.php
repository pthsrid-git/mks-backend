<?php
defined("BASEPATH") or exit("No direct script access allowed");

class Api extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model("User_model");
        $this->load->model("Match_model");
        $this->load->model("Prediction_model");
        $this->load->model("Promo_model", "promo");
        $this->load->library("form_validation");
        $this->load->model('Settings_model'); // TAMBAH INI
    }

    // ==================== AUTHENTICATION APIs ====================

    /**
     * Register new user
     * POST /api/register
     */
    public function register()
    {
        header('Content-Type: application/json');
        $input = json_decode($this->input->raw_input_stream, true) ?: [];

        log_message("DEBUG", '[REGISTER] Raw input: ' . print_r($input, true));

        $errors = [];

        // ==========================
        // Validasi username
        // ==========================
        if (!isset($input['username']) || empty(trim($input['username']))) {
            $errors[] = 'Username is required';
        } else {
            $username = trim($input['username']);
            $this->db->where('username', $username);
            if ($this->db->count_all_results('users') > 0) {
                $errors[] = 'Username already exists';
            }
        }

        // ==========================
        // Validasi email
        // ==========================
        if (!isset($input['email']) || empty(trim($input['email']))) {
            $errors[] = 'Email is required';
        } else if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email is not valid';
        } else {
            $email = trim($input['email']);
            $this->db->where('email', $email);
            if ($this->db->count_all_results('users') > 0) {
                $errors[] = 'Email already exists';
            }
        }

        // ==========================
        // Validasi nomor handphone
        // ==========================
        if (!isset($input['phone']) || empty(trim($input['phone']))) {
            $errors[] = 'Phone number is required';
        } else {
            // Normalisasi sederhana: buang spasi
            $phone = preg_replace('/\s+/', '', $input['phone']);

            // Contoh validasi: hanya angka, panjang 10–15 digit
            if (!preg_match('/^[0-9]{10,15}$/', $phone)) {
                $errors[] = 'Phone number is not valid';
            } else {
                // Cek unik di tabel users
                $this->db->where('phone', $phone);
                if ($this->db->count_all_results('users') > 0) {
                    $errors[] = 'Phone number already exists';
                }
            }
        }

        // ==========================
        // Validasi password
        // ==========================
        if (!isset($input['password']) || empty($input['password'])) {
            $errors[] = 'Password is required';
        } else if (strlen($input['password']) < 6) {
            $errors[] = 'Password must be at least 6 characters';
        }

        // Jika ada error validasi
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode([
                'status'  => FALSE,
                'message' => implode(', ', $errors)
            ]);
            return;
        }

        // Hash password
        $password_hash = password_hash($input['password'], PASSWORD_DEFAULT);

        // Pastikan $phone & $email & $username sudah terisi dari blok di atas
        $data = [
            'username'                 => $username,
            'email'                    => $email,
            'phone'                    => $phone,
            'password'                 => $password_hash,
            'balance'                  => 0,
            'total_correct_predictions' => 0,
            'total_predictions'        => 0,
            'created_at'               => date('Y-m-d H:i:s'),
            'role'                     => 'user'
        ];

        if ($this->User_model->create_user($data)) {
            $user_id = $this->db->insert_id();

            echo json_encode([
                'status'  => TRUE,
                'message' => 'Registration successful',
                'data'    => [
                    'user_id'  => $user_id,
                    'username' => $data['username'],
                    'email'    => $data['email'],
                    'phone'    => $data['phone'],
                    'role'     => $data['role']
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'status'  => FALSE,
                'message' => 'Registration failed'
            ]);
        }
    }

    /**
     * User login
     * POST /api/login
     */
    public function login()
    {
        header('Content-Type: application/json');
        $input = json_decode($this->input->raw_input_stream, true) ?: [];

        log_message("DEBUG", '[LOGIN] Raw input: ' . print_r($input, true));

        $errors = [];

        if (!isset($input['username']) || empty(trim($input['username']))) {
            $errors[] = 'Username is required';
        }

        if (!isset($input['password']) || empty($input['password'])) {
            $errors[] = 'Password is required';
        }

        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode([
                'status' => FALSE,
                'message' => implode(', ', $errors)
            ]);
            return;
        }

        $username = trim($input['username']);
        $password = $input['password'];

        $user = $this->User_model->get_user_by_username($username);

        // Debug password verification
        if ($user) {
            log_message("DEBUG", '[LOGIN] User found, verifying password...');
            $verify_result = password_verify($password, $user->password);
            log_message("DEBUG", '[LOGIN] Password verify result: ' . ($verify_result ? 'TRUE' : 'FALSE'));
        }

        if ($user && password_verify($password, $user->password)) {
            echo json_encode([
                'status' => TRUE,
                'message' => 'Login successful',
                'data' => [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'balance' => $user->balance,
                    'role' => $user->role,
                    'total_predictions' => $user->total_predictions,
                    'total_correct_predictions' => $user->total_correct_predictions
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode([
                'status' => FALSE,
                'message' => 'Invalid username or password'
            ]);
        }
    }

    /**
     * Get user profile
     * GET /api/profile?user_id=1
     */
    public function profile()
    {
        header('Content-Type: application/json');

        $user_id = $this->input->get('user_id');

        if (!$user_id) {
            http_response_code(400);
            echo json_encode([
                "status" => FALSE,
                "message" => "User ID is required"
            ]);
            return;
        }

        $user = $this->User_model->get_user_by_id($user_id);

        if ($user) {
            echo json_encode([
                "status" => TRUE,
                "data" => [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'balance' => $user->balance,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'role' => $user->role,
                    'total_predictions' => $user->total_predictions,
                    'total_correct_predictions' => $user->total_correct_predictions,
                    'accuracy' => $user->total_predictions > 0 ?
                        round(($user->total_correct_predictions / $user->total_predictions) * 100, 2) : 0
                ]
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                "status" => FALSE,
                "message" => "User not found"
            ]);
        }
    }

    // ==================== MATCH APIs ====================

    /**
     * Get all matches
     * GET /api/matches
     */
    public function matches()
    {
        header('Content-Type: application/json');

        $this->db->select('*');
        $this->db->from('matches');
        $this->db->order_by('match_date', 'DESC');
        $this->db->order_by('match_time', 'DESC');
        $matches = $this->db->get()->result();

        echo json_encode([
            "status" => TRUE,
            "data" => $matches
        ]);
    }

    /**
     * Get upcoming matches
     * GET /api/matches/upcoming
     */
    public function upcoming_matches()
    {
        header('Content-Type: application/json');

        $this->db->select('
        matches.*,
        leagues.name AS league_name,
        home_team.name AS home_team_name,
        home_team.logo AS home_logo,
        away_team.name AS away_team_name,
        away_team.logo AS away_logo
    ');
        $this->db->from('matches');

        // JOIN leagues
        $this->db->join('leagues', 'leagues.id = matches.league_id', 'left');

        // JOIN teams as home & away
        $this->db->join('teams AS home_team', 'home_team.id = matches.home_team_id', 'left');
        $this->db->join('teams AS away_team', 'away_team.id = matches.away_team_id', 'left');

        $this->db->where('matches.status', 'scheduled');
        $this->db->where('matches.match_date >=', date('Y-m-d'));
        $this->db->order_by('matches.match_date', 'ASC');
        $this->db->order_by('matches.match_time', 'ASC');

        $matches = $this->db->get()->result();

        // Convert logo path → full URL
        foreach ($matches as &$m) {
            if (!empty($m->home_logo) && strpos($m->home_logo, 'http') !== 0) {
                $m->home_logo = base_url($m->home_logo);
            }
            if (!empty($m->away_logo) && strpos($m->away_logo, 'http') !== 0) {
                $m->away_logo = base_url($m->away_logo);
            }
        }

        echo json_encode([
            "status" => TRUE,
            "data"   => $matches
        ]);
    }



    /**
     * Get finished matches
     * GET /api/matches/finished
     */
    public function finished_matches()
    {
        header('Content-Type: application/json');

        $this->db->select('
        matches.*,
        leagues.name AS league_name,
        home_team.name AS home_team_name,
        home_team.logo AS home_logo,
        away_team.name AS away_team_name,
        away_team.logo AS away_logo
    ');
        $this->db->from('matches');

        // JOIN leagues
        $this->db->join('leagues', 'leagues.id = matches.league_id', 'left');

        // JOIN home & away teams
        $this->db->join('teams AS home_team', 'home_team.id = matches.home_team_id', 'left');
        $this->db->join('teams AS away_team', 'away_team.id = matches.away_team_id', 'left');

        $this->db->where('matches.status', 'finished');
        $this->db->order_by('matches.match_date', 'DESC');
        $this->db->order_by('matches.match_time', 'DESC');

        $matches = $this->db->get()->result();

        // Convert logo path → absolute URL
        foreach ($matches as &$m) {
            if (!empty($m->home_logo) && strpos($m->home_logo, 'http') !== 0) {
                $m->home_logo = base_url($m->home_logo);
            }
            if (!empty($m->away_logo) && strpos($m->away_logo, 'http') !== 0) {
                $m->away_logo = base_url($m->away_logo);
            }
        }

        echo json_encode([
            "status" => TRUE,
            "data"   => $matches
        ]);
    }


    /**
     * Get single match
     * GET /api/matches/:id
     */
    public function get_match($id)
    {
        header('Content-Type: application/json');

        $match = $this->Match_model->get_match_by_id($id);

        if ($match) {
            echo json_encode([
                "status" => TRUE,
                "data" => $match
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                "status" => FALSE,
                "message" => "Match not found"
            ]);
        }
    }

    // ==================== PREDICTION APIs ====================

    /**
     * Submit prediction
     * POST /api/predictions
     * GET  /api/predictions?user_id=XX
     */
    public function predictions()
    {
        header('Content-Type: application/json');

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $input = json_decode($this->input->raw_input_stream, true) ?: [];

            log_message("DEBUG", '[PREDICTION] Raw input: ' . print_r($input, true));

            $errors = [];

            if (!isset($input['user_id']) || empty($input['user_id'])) {
                $errors[] = 'User ID is required';
            }

            if (!isset($input['match_id']) || empty($input['match_id'])) {
                $errors[] = 'Match ID is required';
            }

            // Validasi predicted_result
            if (!isset($input['prediction']) || empty($input['prediction'])) {
                $errors[] = 'Predicted result is required';
            } else {
                $valid_results = ['home_win', 'away_win', 'draw'];
                if (!in_array($input['prediction'], $valid_results)) {
                    $errors[] = 'Predicted result must be: home_win, away_win, or draw';
                }
            }

            if (!empty($errors)) {
                http_response_code(400);
                echo json_encode([
                    'status'  => FALSE,
                    'message' => implode(', ', $errors)
                ]);
                return;
            }

            // Check if match exists and is upcoming
            $match = $this->Match_model->get_match_by_id($input['match_id']);
            if (!$match) {
                http_response_code(404);
                echo json_encode([
                    'status'  => FALSE,
                    'message' => 'Match not found'
                ]);
                return;
            }

            // Ambil setting dari system_settings
            $prediction_deadline_minutes = (int) $this->Settings_model->get_setting('prediction_deadline_minutes');

            // Gabungkan match_date dan match_time dari database
            $match_datetime = $match->match_date . ' ' . $match->match_time;

            // DEBUG: Log untuk verifikasi
            log_message("DEBUG", "[PREDICTION_DEADLINE] Combined match datetime: " . $match_datetime);
            log_message("DEBUG", "[PREDICTION_DEADLINE] Current server time: " . date('Y-m-d H:i:s'));

            $match_timestamp    = strtotime($match_datetime);
            $current_timestamp  = time();
            $deadline_timestamp = $match_timestamp - ($prediction_deadline_minutes * 60);

            log_message("DEBUG", "[PREDICTION_DEADLINE] Match timestamp: " . date('Y-m-d H:i:s', $match_timestamp));
            log_message("DEBUG", "[PREDICTION_DEADLINE] Deadline timestamp: " . date('Y-m-d H:i:s', $deadline_timestamp));
            log_message("DEBUG", "[PREDICTION_DEADLINE] Minutes until deadline: " . round(($deadline_timestamp - $current_timestamp) / 60, 2));

            if ($current_timestamp > $deadline_timestamp) {
                http_response_code(400);
                echo json_encode([
                    'status'                    => FALSE,
                    'message'                   => "Cannot update prediction. Deadline has passed (closes $prediction_deadline_minutes minutes before match)",
                    'predictionDeadlineMinutes' => $prediction_deadline_minutes,
                ]);
                return;
            }

            if ($match->status !== 'scheduled') {
                http_response_code(400);
                echo json_encode([
                    'status'  => FALSE,
                    'message' => 'Cannot predict on ongoing or finished matches'
                ]);
                return;
            }

            // Check if prediction already exists
            $existing_prediction = $this->Prediction_model->get_prediction($input['user_id'], $input['match_id']);

            // Process prediction
            $prediction_data = [
                'user_id'          => $input['user_id'],
                'match_id'         => $input['match_id'],
                'predicted_result' => $input['prediction']
            ];

            if ($existing_prediction) {
                // UPDATE existing prediction
                if ($this->Prediction_model->update_prediction($existing_prediction->id, $prediction_data)) {
                    echo json_encode([
                        'status'                    => TRUE,
                        'message'                   => 'Prediction updated successfully',
                        'predictionDeadlineMinutes' => $prediction_deadline_minutes,
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode([
                        'status'  => FALSE,
                        'message' => 'Failed to update prediction'
                    ]);
                }
            } else {
                // CREATE new prediction
                if ($this->Prediction_model->create_prediction($prediction_data)) {
                    echo json_encode([
                        'status'                    => TRUE,
                        'message'                   => 'Prediction submitted successfully',
                        'predictionDeadlineMinutes' => $prediction_deadline_minutes,
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode([
                        'status'  => FALSE,
                        'message' => 'Failed to submit prediction'
                    ]);
                }
            }
        } else {
            // GET request - get user predictions
            $user_id = $this->input->get('user_id');

            if (!$user_id) {
                http_response_code(400);
                echo json_encode([
                    "status"  => FALSE,
                    "message" => "User ID is required"
                ]);
                return;
            }

            $predictions = $this->Prediction_model->get_user_predictions($user_id);

            // Ambil setting untuk dikirim ke client juga
            $prediction_deadline_minutes = (int) $this->Settings_model->get_setting('prediction_deadline_minutes');

            echo json_encode([
                "status"                   => TRUE,
                "predictionDeadlineMinutes" => $prediction_deadline_minutes,
                "data"                     => $predictions
            ]);
        }
    }


    /**
     * Get user predictions with match details
     * GET /api/my_predictions?user_id=1
     */
    public function my_predictions()
    {
        header('Content-Type: application/json');

        $user_id = $this->input->get('user_id');

        if (!$user_id) {
            http_response_code(400);
            echo json_encode([
                "status" => FALSE,
                "message" => "User ID is required"
            ]);
            return;
        }

        $predictions = $this->Prediction_model->get_user_predictions_with_match_details($user_id);

        echo json_encode([
            "status" => TRUE,
            "data" => $predictions
        ]);
    }

    /**
     * Get prediction results (evaluated predictions)
     * GET /api/prediction_results?user_id=1
     */
    public function prediction_results()
    {
        header('Content-Type: application/json');

        $user_id = $this->input->get('user_id');

        if (!$user_id) {
            http_response_code(400);
            echo json_encode([
                "status" => FALSE,
                "message" => "User ID is required"
            ]);
            return;
        }

        // Jika table predictions, bukan user_predictions
        $this->db->select('
        p.*, 
        m.home_team_id, 
        m.away_team_id, 
        m.home_score, 
        m.away_score, 
        m.status as match_status,
        ht.name as home_team_name,
        at.name as away_team_name
    ');
        $this->db->from('user_predictions p'); // Ganti user_predictions menjadi predictions
        $this->db->join('matches m', 'p.match_id = m.id');
        $this->db->join('teams ht', 'm.home_team_id = ht.id', 'left');
        $this->db->join('teams at', 'm.away_team_id = at.id', 'left');
        $this->db->where('p.user_id', $user_id);
        $this->db->where('m.status', 'finished');
        $this->db->where('p.is_correct IS NOT NULL'); // Sesuaikan dengan nama table


        $results = $this->db->get()->result();

        echo json_encode([
            "status" => TRUE,
            "data" => $results
        ]);
    }

    /**
     * GET /api/leaderboard
     * Bisa pakai filter manual: week_number, league_id, season
     */
    public function leaderboard()
    {
        header('Content-Type: application/json');

        $week_number = $this->input->get('week_number');
        $league_id   = $this->input->get('league_id');
        $season      = $this->input->get('season') ?: '2024/2025';

        $leaderboard = $this->_build_leaderboard($week_number, $league_id, $season);

        echo json_encode([
            "status"  => TRUE,
            "type"    => "custom",
            "data"    => $leaderboard,
            "filters" => [
                "week_number" => $week_number,
                "league_id"   => $league_id,
                "season"      => $season
            ]
        ]);
    }
    /**
     * GET /api/leaderboard/weekly?league_id=1
     */
    public function leaderboard_weekly()
    {
        header('Content-Type: application/json');

        $league_id = (int) $this->input->get('league_id');
        if ($league_id <= 0) {
            echo json_encode(['status' => FALSE, 'message' => 'league_id is required']);
            return;
        }

        // ✅ kalau user pilih GW dari app, pakai ini
        $requestedWeek = $this->input->get('week_number');
        $requestedWeek = $requestedWeek !== null ? (int) $requestedWeek : null;

        // kalau tidak ada week_number, ambil yang latest berdasarkan finished
        if ($requestedWeek && $requestedWeek > 0) {
            $weekNumber = $requestedWeek;
        } else {
            $latestWeekRow = $this->db
                ->select('MAX(week_number) AS week_number', false)
                ->from('matches')
                ->where('league_id', $league_id)
                ->where('status', 'finished')
                ->get()
                ->row();

            $weekNumber = $latestWeekRow ? (int)$latestWeekRow->week_number : null;
        }

        if (!$weekNumber) {
            echo json_encode(['status' => FALSE, 'message' => 'No week found for this league']);
            return;
        }

        $this->db->select('
            u.id,
            u.username,
            u.email,
    
            COUNT(p.id) AS total_predictions,
            SUM(CASE WHEN p.is_correct = 1 THEN 1 ELSE 0 END) AS total_correct_predictions,
    
            SUM(CASE WHEN p.is_correct IS NULL THEN 1 ELSE 0 END) AS pending_predictions,
            SUM(CASE WHEN p.is_correct IS NOT NULL THEN 1 ELSE 0 END) AS played_predictions,
    
            SUM(CASE WHEN p.result_type = "exact" THEN 1 ELSE 0 END) AS exact_predictions,
            SUM(CASE WHEN p.result_type = "correct" THEN 1 ELSE 0 END) AS correct_predictions,
            COALESCE(SUM(p.points_earned), 0) AS points
        ', false);

        $this->db->from('users u');
        $this->db->join('user_predictions p', 'p.user_id = u.id', 'left');
        $this->db->join('matches m', 'm.id = p.match_id', 'left');

        $this->db->where('u.role', 'user');
        $this->db->where('m.league_id', $league_id);
        $this->db->where('m.week_number', $weekNumber);
        $this->db->where('p.id IS NOT NULL', null, false);

        $this->db->group_by('u.id');
        $this->db->order_by('points', 'DESC');
        $this->db->order_by('total_correct_predictions', 'DESC');
        $this->db->order_by('exact_predictions', 'DESC');
        $this->db->order_by('total_predictions', 'DESC');
        $this->db->limit(50);

        $rows = $this->db->get()->result();

        $rank = 1;
        foreach ($rows as $row) {
            $row->rank = $rank++;
            $played = (int) $row->played_predictions;
            $correct = (int) $row->total_correct_predictions;
            $row->accuracy = $played > 0 ? round(($correct / $played) * 100, 2) : 0;
        }

        echo json_encode([
            'status' => TRUE,
            'data'   => $rows,
            'filter' => [
                'type'        => 'weekly',
                'league_id'   => $league_id,
                'week_number' => $weekNumber,
            ],
        ]);
    }


    /**
     * GET /api/leaderboard/season?league_id=1&season=2024/2025
     */
    public function leaderboard_season()
    {
        header('Content-Type: application/json');

        $league_id = (int) $this->input->get('league_id');
        $season    = $this->input->get('season') ?: '2024/2025';

        if ($league_id <= 0) {
            echo json_encode([
                'status'  => FALSE,
                'message' => 'league_id is required',
            ]);
            return;
        }

        $this->db->select('
        u.id,
        u.username,
        u.email,
        COUNT(p.id) AS total_predictions,
        SUM(CASE WHEN p.is_correct = 1 THEN 1 ELSE 0 END) AS total_correct_predictions,
        SUM(CASE WHEN p.result_type = "exact" THEN 1 ELSE 0 END) AS exact_predictions,
        SUM(CASE WHEN p.result_type = "correct" THEN 1 ELSE 0 END) AS correct_predictions,
        COALESCE(SUM(p.points_earned), 0) AS total_points
    ');
        $this->db->from('users u');
        $this->db->join('user_predictions p', 'p.user_id = u.id', 'left');
        $this->db->join('matches m', 'm.id = p.match_id', 'left');

        $this->db->where('u.role', 'user');
        $this->db->where('m.league_id', $league_id);
        $this->db->where('m.status', 'finished');
        $this->db->where('m.season', $season);

        $this->db->group_by('u.id');
        $this->db->order_by('total_points', 'DESC');
        $this->db->order_by('total_correct_predictions', 'DESC');
        $this->db->order_by('exact_predictions', 'DESC');
        $this->db->limit(50);

        $rows = $this->db->get()->result();

        $rank = 1;
        foreach ($rows as $row) {
            $row->rank     = $rank++;
            $row->accuracy = $row->total_predictions > 0
                ? round(($row->total_correct_predictions / $row->total_predictions) * 100, 2)
                : 0;
        }

        echo json_encode([
            'status' => TRUE,
            'data'   => $rows,
            'filter' => [
                'type'      => 'season',
                'league_id' => $league_id,
                'season'    => $season,
            ],
        ]);
    }

    // ==================== UTILITY APIs ====================

    /**
     * Health check
     * GET /api/health
     */
    public function health()
    {
        header('Content-Type: application/json');

        echo json_encode([
            "status" => TRUE,
            "message" => "API is running",
            "timestamp" => date('Y-m-d H:i:s'),
            "version" => "1.0.0"
        ]);
    }

    /**
     * Get user stats
     * GET /api/user_stats?user_id=1
     */
    public function user_stats()
    {
        header('Content-Type: application/json');

        $user_id = (int) $this->input->get('user_id');

        if ($user_id <= 0) {
            http_response_code(400);
            echo json_encode([
                "status"  => FALSE,
                "message" => "User ID is required"
            ]);
            return;
        }

        $user = $this->User_model->get_user_by_id($user_id);

        if (!$user) {
            http_response_code(404);
            echo json_encode([
                "status"  => FALSE,
                "message" => "User not found"
            ]);
            return;
        }

        // Get additional stats
        $this->db->where('user_id', $user_id);
        $total_predictions = $this->db->count_all_results('user_predictions');

        $this->db->where('user_id', $user_id);
        $this->db->where('is_correct', 1);
        $correct_predictions = $this->db->count_all_results('user_predictions');

        $this->db->select_sum('points_earned');
        $this->db->where('user_id', $user_id);
        $total_points_result = $this->db->get('user_predictions')->row();
        $total_points = $total_points_result->points_earned ?? 0;

        // hitung akurasi
        $accuracy = 0;
        if ($total_predictions > 0) {
            $accuracy = round(($correct_predictions / $total_predictions) * 100, 2);
        }

        $stats = [
            'user_id'            => (int) $user->id,
            'username'           => $user->username,
            'total_predictions'  => (int) $total_predictions,
            'correct_predictions' => (int) $correct_predictions,
            'total_points'       => (float) $total_points,
            'accuracy'           => $accuracy,
            'rank'               => 'N/A', // TODO: implement ranking logic
            'balance'            => isset($user->balance)       // NEW  saldo dari tabel users
                ? (float) $user->balance
                : 0.0,
        ];

        echo json_encode([
            "status" => TRUE,
            "data"   => $stats
        ]);
    }


    /**
     * Base builder leaderboard
     * USED BY: /api/leaderboard, /api/leaderboard/weekly, /api/leaderboard/season
     */
    private function _build_leaderboard($week_number = null, $league_id = null, $season = '2024/2025')
    {
        // Base query
        $this->db->select('
        u.id, 
        u.username, 
        u.email,
        COUNT(p.id) as total_predictions, 
        SUM(CASE WHEN p.is_correct = 1 THEN 1 ELSE 0 END) as total_correct_predictions,
        SUM(CASE WHEN p.result_type = "exact" THEN 1 ELSE 0 END) as exact_predictions,
        SUM(CASE WHEN p.result_type = "correct" THEN 1 ELSE 0 END) as correct_predictions,
        COALESCE(SUM(p.points_earned), 0) as total_points
    ');

        $this->db->from('users u');
        $this->db->join('user_predictions p', 'p.user_id = u.id', 'left');
        $this->db->join('matches m', 'm.id = p.match_id', 'left');

        // Apply filters
        if ($week_number) {
            $this->db->where('m.week_number', $week_number);
        }

        if ($league_id) {
            $this->db->where('m.league_id', $league_id);
        }

        if ($season) {
            $this->db->where('m.season', $season);
        }

        $this->db->where('u.role', 'user');
        $this->db->where('m.status', 'finished'); // Only finished matches
        $this->db->group_by('u.id');

        // Order by points (DESC) lalu correct predictions (DESC)
        $this->db->order_by('total_correct_predictions', 'DESC');
        $this->db->order_by('exact_predictions', 'DESC');
        $this->db->limit(20);

        $leaderboard = $this->db->get()->result();

        // Calculate accuracy and add rank
        $rank = 1;
        foreach ($leaderboard as $user) {
            $user->rank = $rank++;
            $user->accuracy = $user->total_predictions > 0
                ? round(($user->total_correct_predictions / $user->total_predictions) * 100, 2)
                : 0;
        }

        return $leaderboard;
    }

    public function withdraw_request()
    {
        header('Content-Type: application/json');

        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            $this->output->set_status_header(405);
            echo json_encode([
                'status'  => false,
                'message' => 'Method not allowed',
            ]);
            return;
        }

        $input = json_decode($this->input->raw_input_stream, true) ?: [];

        log_message("DEBUG", '[WITHDRAW] Raw input: ' . print_r($input, true));

        $errors = [];

        $user_id        = isset($input['user_id']) ? (int)$input['user_id'] : 0;
        $bank_name      = trim($input['bank_name'] ?? '');
        $account_number = trim($input['account_number'] ?? '');
        $account_owner  = trim($input['account_owner'] ?? '');
        // Kalau mau support nominal custom:
        // $amount         = isset($input['amount']) ? (float)$input['amount'] : 0;

        // Validasi dasar
        if ($user_id <= 0) {
            $errors[] = 'User ID is required';
        }

        if ($bank_name === '') {
            $errors[] = 'Nama bank wajib diisi';
        }

        if ($account_number === '') {
            $errors[] = 'Nomor rekening wajib diisi';
        }

        if ($account_owner === '') {
            $errors[] = 'Nama pemilik rekening wajib diisi';
        }

        // Cek user & saldo
        if ($user_id > 0) {
            $user = $this->db->get_where('users', ['id' => $user_id])->row();
            if (!$user) {
                $errors[] = 'User tidak ditemukan';
            } else {
                // asumsi di tabel users ada kolom "balance"
                $balance = (float)$user->balance;
                if ($balance <= 0) {
                    $errors[] = 'Saldo tidak mencukupi untuk ditarik';
                }
            }
        }

        if (!empty($errors)) {
            $this->output->set_status_header(422);
            echo json_encode([
                'status'  => false,
                'message' => 'Validasi gagal',
                'errors'  => $errors,
            ]);
            return;
        }

        // Kalau amount tidak dikirim dari client, pakai full balance user
        $amount = (float)$user->balance;

        $now = date('Y-m-d H:i:s');

        $data = [
            'user_id'        => $user_id,
            'bank_name'      => $bank_name,
            'account_number' => $account_number,
            'account_owner'  => $account_owner,
            'amount'         => $amount,
            'status'         => 'pending',
            'created_at'     => $now,
            'updated_at'     => $now,
        ];

        $ok = $this->db->insert('withdraw_requests', $data);

        if (!$ok) {
            $this->output->set_status_header(500);
            echo json_encode([
                'status'  => false,
                'message' => 'Gagal menyimpan permintaan penarikan',
            ]);
            return;
        }

        $id = $this->db->insert_id();

        // NOTE:
        // Saldo user TIDAK langsung dipotong di sini.
        // Biasanya dipotong saat admin "approve" di backend.
        // Kalau mau langsung potong, di sini tambahkan update users.balance.

        echo json_encode([
            'status'  => true,
            'message' => 'Permintaan penarikan berhasil dibuat. Menunggu persetujuan admin.',
            'data'    => [
                'id'            => $id,
                'amount'        => $amount,
                'status'        => 'pending',
                'bank_name'     => $bank_name,
                'account_owner' => $account_owner,
            ],
        ]);
    }

    public function withdraw_requests()
    {
        header('Content-Type: application/json');

        if ($this->input->server('REQUEST_METHOD') !== 'GET') {
            $this->output->set_status_header(405);
            echo json_encode([
                'status'  => false,
                'message' => 'Method not allowed',
            ]);
            return;
        }

        $user_id = (int)$this->input->get('user_id');
        $status  = $this->input->get('status'); // optional: pending/approved/rejected

        if ($user_id <= 0) {
            $this->output->set_status_header(422);
            echo json_encode([
                'status'  => false,
                'message' => 'user_id is required',
            ]);
            return;
        }

        $this->db->from('withdraw_requests');
        $this->db->where('user_id', $user_id);
        if (!empty($status)) {
            $this->db->where('status', $status);
        }
        $this->db->order_by('created_at', 'DESC');
        $rows = $this->db->get()->result_array();

        echo json_encode([
            'status'  => true,
            'message' => 'OK',
            'data'    => $rows,
        ]);
    }

    /**
     * GET /api/promos
     * Ambil daftar promo/banner untuk home
     */
    public function promos()
    {
        header('Content-Type: application/json');

        if ($this->input->server('REQUEST_METHOD') !== 'GET') {
            http_response_code(405);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Method not allowed'
            ]);
            return;
        }

        try {
            $limit = (int) $this->input->get('limit');
            if ($limit <= 0 || $limit > 50) {
                $limit = 10;
            }

            $rows = $this->promo->get_active_promos($limit);

            echo json_encode([
                'status' => 'success',
                'data'   => $rows,
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Server error',
            ]);
        }
    }

    /**
     * Forgot password / Reset password by phone
     * POST /api/forgot_password
     *
     * Body JSON:
     * {
     *   "phone": "081287222400",
     *   "password": "passwordbaru",
     *   "password_confirm": "passwordbaru"
     * }
     */
    public function forgot_password()
    {
        header('Content-Type: application/json');

        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'status'  => FALSE,
                'message' => 'Method not allowed'
            ]);
            return;
        }

        $input = json_decode($this->input->raw_input_stream, true) ?: [];

        log_message("DEBUG", '[FORGOT_PASSWORD] Raw input: ' . print_r($input, true));

        $errors = [];

        // ==========================
        // Ambil dan normalisasi input
        // ==========================
        $phone            = isset($input['phone']) ? trim($input['phone']) : '';
        $password         = isset($input['password']) ? (string)$input['password'] : '';
        $password_confirm = isset($input['password_confirm']) ? (string)$input['password_confirm'] : '';

        // Buang spasi, dash, titik, kurung
        $phone = preg_replace('/[\s\-\.\(\)]/', '', $phone);

        // Kalau user input +62, ubah jadi 0
        // contoh: +6281287222400 -> 081287222400
        if (strpos($phone, '+62') === 0) {
            $phone = '0' . substr($phone, 3);
        }

        // Kalau user input 62 tanpa plus, ubah jadi 0
        // contoh: 6281287222400 -> 081287222400
        if (strpos($phone, '62') === 0) {
            $phone = '0' . substr($phone, 2);
        }

        // ==========================
        // Validasi phone
        // ==========================
        if ($phone === '') {
            $errors[] = 'Phone number is required';
        } elseif (!preg_match('/^[0-9]{10,15}$/', $phone)) {
            $errors[] = 'Phone number is not valid';
        }

        // ==========================
        // Validasi password
        // ==========================
        if ($password === '') {
            $errors[] = 'Password is required';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters';
        }

        if ($password_confirm === '') {
            $errors[] = 'Password confirmation is required';
        } elseif ($password !== $password_confirm) {
            $errors[] = 'Password confirmation does not match';
        }

        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode([
                'status'  => FALSE,
                'message' => implode(', ', $errors),
                'errors'  => $errors,
            ]);
            return;
        }

        // ==========================
        // Cari user berdasarkan phone
        // ==========================
        $user = $this->db
            ->where('phone', $phone)
            ->get('users')
            ->row();

        if (!$user) {
            http_response_code(404);
            echo json_encode([
                'status'  => FALSE,
                'message' => 'Phone number is not registered'
            ]);
            return;
        }

        // ==========================
        // Update password
        // ==========================
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $update_data = [
            'password' => $password_hash,
        ];

        // Kalau tabel users punya kolom updated_at, aktifkan ini.
        // Kalau tidak ada kolom updated_at, biarkan dikomentari.
        // $update_data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('id', $user->id);
        $ok = $this->db->update('users', $update_data);

        if (!$ok) {
            http_response_code(500);
            echo json_encode([
                'status'  => FALSE,
                'message' => 'Failed to update password'
            ]);
            return;
        }

        echo json_encode([
            'status'  => TRUE,
            'message' => 'Password updated successfully',
            'data'    => [
                'user_id'  => (int)$user->id,
                'username' => $user->username,
                'phone'    => $phone,
            ],
        ]);
    }

    /**
     * Register FCM token
     * POST /api/fcm/register_token
     *
     * Body JSON:
     * {
     *   "user_id": 1,
     *   "fcm_token": "xxxxx",
     *   "device_type": "android",
     *   "device_name": "Samsung A54"
     * }
     */
    public function register_fcm_token()
    {
        header('Content-Type: application/json');

        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'status'  => FALSE,
                'message' => 'Method not allowed'
            ]);
            return;
        }

        $input = json_decode($this->input->raw_input_stream, true) ?: [];

        log_message("DEBUG", '[REGISTER_FCM_TOKEN] Raw input: ' . print_r($input, true));

        $user_id     = isset($input['user_id']) ? (int)$input['user_id'] : 0;
        $fcm_token   = trim($input['fcm_token'] ?? '');
        $device_type = trim($input['device_type'] ?? '');
        $device_name = trim($input['device_name'] ?? '');

        $errors = [];

        if ($user_id <= 0) {
            $errors[] = 'User ID is required';
        }

        if ($fcm_token === '') {
            $errors[] = 'FCM token is required';
        }

        if ($user_id > 0) {
            $user = $this->db->get_where('users', ['id' => $user_id])->row();
            if (!$user) {
                $errors[] = 'User not found';
            }
        }

        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode([
                'status'  => FALSE,
                'message' => implode(', ', $errors),
                'errors'  => $errors,
            ]);
            return;
        }

        $existing = $this->db
            ->where('user_id', $user_id)
            ->where('fcm_token', $fcm_token)
            ->get('user_fcm_tokens')
            ->row();

        $now = date('Y-m-d H:i:s');

        if ($existing) {
            $this->db->where('id', $existing->id);
            $ok = $this->db->update('user_fcm_tokens', [
                'device_type' => $device_type,
                'device_name' => $device_name,
                'is_active'   => 1,
                'updated_at'  => $now,
            ]);

            if (!$ok) {
                http_response_code(500);
                echo json_encode([
                    'status'  => FALSE,
                    'message' => 'Failed to update FCM token',
                ]);
                return;
            }

            echo json_encode([
                'status'  => TRUE,
                'message' => 'FCM token updated',
                'data'    => [
                    'id'      => (int)$existing->id,
                    'user_id' => $user_id,
                ],
            ]);
            return;
        }

        $ok = $this->db->insert('user_fcm_tokens', [
            'user_id'     => $user_id,
            'fcm_token'   => $fcm_token,
            'device_type' => $device_type,
            'device_name' => $device_name,
            'is_active'   => 1,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        if (!$ok) {
            http_response_code(500);
            echo json_encode([
                'status'  => FALSE,
                'message' => 'Failed to save FCM token',
            ]);
            return;
        }

        echo json_encode([
            'status'  => TRUE,
            'message' => 'FCM token saved',
            'data'    => [
                'id'      => (int)$this->db->insert_id(),
                'user_id' => $user_id,
            ],
        ]);
    }

    private function _get_firebase_access_token()
    {
        $serviceAccountPath = APPPATH . 'third_party/firebase/service-account.json';

        if (!file_exists($serviceAccountPath)) {
            throw new Exception('Firebase service account file not found: ' . $serviceAccountPath);
        }

        $autoload = FCPATH . 'vendor/autoload.php';

        if (!file_exists($autoload)) {
            $autoload = APPPATH . '../vendor/autoload.php';
        }

        if (!file_exists($autoload)) {
            throw new Exception('Composer autoload not found. Run composer require google/auth');
        }

        require_once $autoload;

        $credentials = new Google\Auth\Credentials\ServiceAccountCredentials(
            ['https://www.googleapis.com/auth/firebase.messaging'],
            $serviceAccountPath
        );

        $token = $credentials->fetchAuthToken();

        if (!isset($token['access_token'])) {
            throw new Exception('Failed to get Firebase access token');
        }

        return $token['access_token'];
    }

    private function _get_firebase_project_id()
    {
        $serviceAccountPath = APPPATH . 'third_party/firebase/service-account.json';

        if (!file_exists($serviceAccountPath)) {
            throw new Exception('Firebase service account file not found');
        }

        $json = json_decode(file_get_contents($serviceAccountPath), true);

        if (!isset($json['project_id']) || empty($json['project_id'])) {
            throw new Exception('Firebase project_id not found in service account');
        }

        return $json['project_id'];
    }

    private function _send_fcm_to_token($token, $title, $body, $data = [])
    {
        $accessToken = $this->_get_firebase_access_token();
        $projectId   = $this->_get_firebase_project_id();

        $url = 'https://fcm.googleapis.com/v1/projects/' . $projectId . '/messages:send';

        // FCM data wajib string semua
        $cleanData = [];
        foreach ($data as $key => $value) {
            $cleanData[$key] = (string) $value;
        }

        $payload = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                'data' => $cleanData,
                'android' => [
                    'priority' => 'HIGH',
                    'notification' => [
                        'sound'      => 'default',
                        'channel_id' => 'mks_default_channel',
                    ],
                ],
            ],
        ];

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 20,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);

        curl_close($ch);

        if ($response === false) {
            return [
                'success'   => false,
                'http_code' => $httpCode,
                'response'  => $curlErr,
            ];
        }

        $decoded = json_decode($response, true);

        return [
            'success'   => ($httpCode >= 200 && $httpCode < 300),
            'http_code' => $httpCode,
            'response'  => $decoded ?: $response,
        ];
    }

    /**
     * Broadcast notification to all active FCM tokens
     * POST /api/send_broadcast_notification
     */
    public function send_broadcast_notification()
    {
        header('Content-Type: application/json');

        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'status'  => false,
                'message' => 'Method not allowed',
            ]);
            return;
        }

        $input = json_decode($this->input->raw_input_stream, true) ?: [];

        log_message("DEBUG", '[SEND_BROADCAST_NOTIFICATION] Raw input: ' . print_r($input, true));

        $title  = trim($input['title'] ?? '');
        $body   = trim($input['body'] ?? '');
        $type   = trim($input['type'] ?? 'broadcast');
        $screen = trim($input['screen'] ?? 'home');

        $errors = [];

        if ($title === '') {
            $errors[] = 'Title is required';
        }

        if ($body === '') {
            $errors[] = 'Body is required';
        }

        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode([
                'status'  => false,
                'message' => implode(', ', $errors),
                'errors'  => $errors,
            ]);
            return;
        }

        $data = [
            'type'   => $type,
            'screen' => $screen,
        ];

        if (!empty($input['league_id'])) {
            $data['league_id'] = (string) $input['league_id'];
        }

        if (!empty($input['match_id'])) {
            $data['match_id'] = (string) $input['match_id'];
        }

        if (!empty($input['week_number'])) {
            $data['week_number'] = (string) $input['week_number'];
        }

        $tokens = $this->db
            ->select('id, user_id, fcm_token')
            ->from('user_fcm_tokens')
            ->where('is_active', 1)
            ->get()
            ->result();

        if (empty($tokens)) {
            echo json_encode([
                'status'  => false,
                'message' => 'No active FCM tokens found',
            ]);
            return;
        }

        /*
    |--------------------------------------------------------------------------
    | Insert master notification
    |--------------------------------------------------------------------------
    */
        $this->db->insert('notifications', [
            'title'          => $title,
            'body'           => $body,
            'type'           => $type,
            'target_type'    => $input['target_type'] ?? 'all',
            'target_user_id' => !empty($input['target_user_id']) ? (int) $input['target_user_id'] : null,
            'league_id'      => !empty($input['league_id']) ? (int) $input['league_id'] : null,
            'match_id'       => !empty($input['match_id']) ? (int) $input['match_id'] : null,
            'image_url'      => $input['image_url'] ?? null,
            'data_json'      => json_encode($data),
            'status'         => 'sent',
            'scheduled_at'   => null,
            'sent_at'        => date('Y-m-d H:i:s'),
            'created_by'     => !empty($input['created_by']) ? (int) $input['created_by'] : null,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        $notificationId = (int) $this->db->insert_id();

        if ($notificationId > 0) {
            $data['notification_id'] = (string) $notificationId;

            $this->db->where('id', $notificationId);
            $this->db->update('notifications', [
                'data_json' => json_encode($data),
            ]);
        }

        $success = 0;
        $failed  = 0;
        $results = [];

        foreach ($tokens as $row) {
            $result = $this->_send_fcm_to_token(
                $row->fcm_token,
                $title,
                $body,
                $data
            );

            if ($result['success']) {
                $success++;
            } else {
                $failed++;

                $responseText = json_encode($result['response']);

                if (
                    strpos($responseText, 'UNREGISTERED') !== false ||
                    strpos($responseText, 'not a valid FCM registration token') !== false ||
                    strpos($responseText, 'INVALID_ARGUMENT') !== false
                ) {
                    $this->db->where('id', $row->id);
                    $this->db->update('user_fcm_tokens', [
                        'is_active'  => 0,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            /*
        |--------------------------------------------------------------------------
        | Insert user notification log
        |--------------------------------------------------------------------------
        */
            if ($notificationId > 0) {
                $this->db->insert('notification_user_logs', [
                    'notification_id' => $notificationId,
                    'user_id'         => (int) $row->user_id,
                    'fcm_token'       => $row->fcm_token,
                    'status'          => $result['success'] ? 'sent' : 'failed',
                    'response'        => json_encode($result['response']),
                    'sent_at'         => date('Y-m-d H:i:s'),
                    'read_at'         => null,
                    'created_at'      => date('Y-m-d H:i:s'),
                ]);
            }

            $results[] = [
                'token_id'  => (int) $row->id,
                'user_id'   => (int) $row->user_id,
                'success'   => $result['success'],
                'http_code' => $result['http_code'],
                'response'  => $result['response'],
            ];
        }

        echo json_encode([
            'status'  => true,
            'message' => 'Broadcast processed',
            'summary' => [
                'notification_id' => $notificationId,
                'total'           => count($tokens),
                'success'         => $success,
                'failed'          => $failed,
            ],
            'data' => $results,
        ]);
    }

    /**
     * Get notification list for user
     * GET /api/notifications?user_id=8&limit=30
     */
    public function notifications()
    {
        header('Content-Type: application/json');

        if ($this->input->server('REQUEST_METHOD') !== 'GET') {
            http_response_code(405);
            echo json_encode([
                'status'  => false,
                'message' => 'Method not allowed',
            ]);
            return;
        }

        $userId = (int) $this->input->get('user_id');
        $limit  = (int) $this->input->get('limit');

        if ($userId <= 0) {
            http_response_code(400);
            echo json_encode([
                'status'  => false,
                'message' => 'user_id is required',
            ]);
            return;
        }

        if ($limit <= 0) {
            $limit = 30;
        }

        if ($limit > 100) {
            $limit = 100;
        }

        $rows = $this->db
            ->select('
            n.id,
            n.title,
            n.body,
            n.type,
            n.target_type,
            n.target_user_id,
            n.league_id,
            n.match_id,
            n.image_url,
            n.data_json,
            n.status,
            n.sent_at,
            n.created_at,
            l.id AS log_id,
            l.status AS user_status,
            l.read_at
        ')
            ->from('notification_user_logs l')
            ->join('notifications n', 'n.id = l.notification_id', 'inner')
            ->where('l.user_id', $userId)
            ->order_by('l.id', 'DESC')
            ->limit($limit)
            ->get()
            ->result();

        $data = [];

        foreach ($rows as $row) {
            $payload = [];

            if (!empty($row->data_json)) {
                $decoded = json_decode($row->data_json, true);
                if (is_array($decoded)) {
                    $payload = $decoded;
                }
            }

            $data[] = [
                'id'          => (int) $row->id,
                'log_id'      => (int) $row->log_id,
                'title'       => $row->title,
                'body'        => $row->body,
                'type'        => $row->type,
                'screen'      => $payload['screen'] ?? 'home',
                'target_type' => $row->target_type,
                'league_id'   => $row->league_id !== null ? (int) $row->league_id : null,
                'match_id'    => $row->match_id !== null ? (int) $row->match_id : null,
                'image_url'   => $row->image_url,
                'payload'     => $payload,
                'status'      => $row->user_status,
                'is_read'     => !empty($row->read_at),
                'read_at'     => $row->read_at,
                'sent_at'     => $row->sent_at,
                'created_at'  => $row->created_at,
            ];
        }

        echo json_encode([
            'status'  => true,
            'message' => 'Notifications loaded',
            'data'    => $data,
        ]);
    }

    /**
     * Mark notification as read
     * POST /api/notification_mark_read
     *
     * Body:
     * {
     *   "user_id": 8,
     *   "log_id": 1
     * }
     */
    public function notification_mark_read()
    {
        header('Content-Type: application/json');

        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'status'  => false,
                'message' => 'Method not allowed',
            ]);
            return;
        }

        $input = json_decode($this->input->raw_input_stream, true) ?: [];

        $userId = (int) ($input['user_id'] ?? 0);
        $logId  = (int) ($input['log_id'] ?? 0);

        if ($userId <= 0 || $logId <= 0) {
            http_response_code(400);
            echo json_encode([
                'status'  => false,
                'message' => 'user_id and log_id are required',
            ]);
            return;
        }

        $exists = $this->db
            ->select('id')
            ->from('notification_user_logs')
            ->where('id', $logId)
            ->where('user_id', $userId)
            ->get()
            ->row();

        if (!$exists) {
            http_response_code(404);
            echo json_encode([
                'status'  => false,
                'message' => 'Notification log not found',
            ]);
            return;
        }

        $this->db
            ->where('id', $logId)
            ->where('user_id', $userId)
            ->update('notification_user_logs', [
                'read_at' => date('Y-m-d H:i:s'),
            ]);

        echo json_encode([
            'status'  => true,
            'message' => 'Notification marked as read',
        ]);
    }
}
