<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Admin Routes
$route['admin'] = 'admin/index';
$route['admin/matches'] = 'admin/matches';
$route['admin/users'] = 'admin/users';
$route['admin/add-match'] = 'admin/add_match';
$route['admin/add-user'] = 'admin/add_user';
$route['admin/edit-match/(:num)'] = 'admin/edit_match/$1';
$route['admin/edit-user/(:num)'] = 'admin/edit_user/$1';
$route['admin/delete-match/(:num)'] = 'admin/delete_match/$1';
$route['admin/delete-user/(:num)'] = 'admin/delete_user/$1';
$route['admin/calculate-bonus'] = 'admin/calculate_bonus';
//$route['admin/login'] = 'auth/admin_login';
$route['admin/logout'] = 'auth/admin_logout';

// API Routes
// $route['api/auth/login']['post']= 'api/login';
// $route['api/auth/register'] = 'api/register';
// $route['api/matches'] = 'api/matches';
// $route['api/leagues'] = 'api/leagues';
// $route['api/prediction'] = 'api/prediction';
// $route['api/predictions'] = 'api/predictions';
// $route['api/stats'] = 'api/stats';


// API Routes
$route['api/register'] = 'api/register';
$route['api/login'] = 'api/login';
$route['api/profile'] = 'api/profile';
$route['api/user_stats'] = 'api/user_stats';

// Match Routes
$route['api/matches'] = 'api/matches';
$route['api/matches/upcoming'] = 'api/upcoming_matches';
$route['api/matches/finished'] = 'api/finished_matches';
$route['api/matches/(:num)'] = 'api/get_match/$1';

// Prediction Routes
$route['api/predictions'] = 'api/predictions';
$route['api/my_predictions'] = 'api/my_predictions';
$route['api/prediction_results'] = 'api/prediction_results';

// Leaderboard & Utility Routes
// Leaderboard
$route['api/leaderboard']['GET']          = 'api/leaderboard';
$route['api/leaderboard/weekly']['GET']   = 'api/leaderboard_weekly';
$route['api/leaderboard/season']['GET']   = 'api/leaderboard_season';
$route['api/health'] = 'api/health';

$route['api/withdraw/request']['post'] = 'api/withdraw_request';
$route['api/withdraw/requests']['get'] = 'api/withdraw_requests';
$route['api/promos'] = 'api/promos';


// atau kalau controllernya Wallet:
// $route['api/withdraw_request']['post'] = 'wallet/withdraw_request';


// Tambah routes untuk leagues dan teams
$route['admin/leagues'] = 'admin/leagues';
$route['admin/teams'] = 'admin/teams';
$route['admin/add-league'] = 'admin/add_league';
$route['admin/add-team'] = 'admin/add_team';
$route['admin/edit-league/(:num)'] = 'admin/edit_league/$1';
$route['admin/edit-team/(:num)'] = 'admin/edit_team/$1';
$route['admin/delete-league/(:num)'] = 'admin/delete_league/$1';
$route['admin/delete-team/(:num)'] = 'admin/delete_team/$1';

// Tambah routes untuk settings
$route['admin/settings'] = 'admin/settings';
$route['admin/update-settings'] = 'admin/update_settings';
$route['admin/update-bonus-settings'] = 'admin/update_bonus_settings';

$route['admin/bonus-reports'] = 'admin/bonus_reports';

// Admin Match Update Routes
$route['admin/match_update'] = 'match_update';
$route['admin/match_update/index'] = 'match_update/index';
$route['admin/match_update/get_status'] = 'match_update/get_status';
$route['admin/match_update/get_recent_matches'] = 'match_update/get_recent_matches';
$route['admin/match_update/get_update_logs'] = 'match_update/get_update_logs';
$route['admin/match_update/update_all'] = 'match_update/update_all';
$route['admin/match_update/update_single/(:num)'] = 'match_update/update_single/$1';
$route['admin/match_update/evaluate_predictions/(:num)'] = 'match_update/evaluate_predictions/$1';
$route['admin/match_update/toggle_auto_update'] = '/match_update/toggle_auto_update';

$route['admin/withdraw']          = 'withdraw/index';
$route['admin/withdraw/approve/(:num)'] = 'withdraw/approve/$1';
$route['admin/withdraw/reject/(:num)']  = 'withdraw/reject/$1';


$route['default_controller'] = 'auth/admin_login';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;