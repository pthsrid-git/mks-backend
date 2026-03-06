<?php
/**
 * CodeIgniter - Fixed Version
 * No whitespace before this line!
 */

// Define application environment
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development');
}

// Error reporting
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Define paths
$system_path = 'system';
$application_folder = 'application';
$view_folder = '';

// Ensure no output before headers
if (ob_get_level()) ob_end_clean();
ob_start();

// Set current directory correctly for CLI requests
if (defined('STDIN')) {
    chdir(dirname(__FILE__));
}

if (realpath($system_path) !== FALSE) {
    $system_path = realpath($system_path) . '/';
}

$system_path = rtrim($system_path, '/') . '/';

if (!is_dir($system_path)) {
    header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
    echo 'Your system folder path does not appear to be set correctly. ';
    exit(3);
}

// Define the core CodeIgniter constants
define('BASEPATH', $system_path);
define('FCPATH', dirname(__FILE__) . '/');
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
define('EXT', '.php');
define('SYSDIR', trim(strrchr(trim(BASEPATH, '/'), '/'), '/'));

if (is_dir($application_folder)) {
    if (($_temp = realpath($application_folder)) !== FALSE) {
        $application_folder = $_temp;
    }
    define('APPPATH', $application_folder . DIRECTORY_SEPARATOR);
} else {
    if (!is_dir(BASEPATH . $application_folder . DIRECTORY_SEPARATOR)) {
        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
        echo 'Your application folder path does not appear to be set correctly. ';
        exit(3);
    }
    define('APPPATH', BASEPATH . $application_folder . DIRECTORY_SEPARATOR);
}

if (!is_dir($view_folder)) {
    if (!empty($view_folder) && is_dir(APPPATH . $view_folder . DIRECTORY_SEPARATOR)) {
        $view_folder = APPPATH . $view_folder;
    } elseif (!is_dir(APPPATH . 'views' . DIRECTORY_SEPARATOR)) {
        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
        echo 'Your view folder path does not appear to be set correctly. ';
        exit(3);
    } else {
        $view_folder = APPPATH . 'views';
    }
}

if (($_temp = realpath($view_folder)) !== FALSE) {
    $view_folder = $_temp . DIRECTORY_SEPARATOR;
} else {
    $view_folder = rtrim($view_folder, '/\\\\') . DIRECTORY_SEPARATOR;
}

define('VIEWPATH', $view_folder);

// Clean any output before loading CodeIgniter
ob_clean();

// Load the CodeIgniter framework
require_once BASEPATH . 'core/CodeIgniter.php';