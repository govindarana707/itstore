<?php
// ==========================
// config.php - Auto Port Detection
// ==========================

if (!defined('CONFIG_LOADED')) {
    define('CONFIG_LOADED', true);

    // Database configuration
    $host = '127.0.0.1';
    $user = 'root';
    $pass = ''; // change if needed
    $db   = 'it_store';

    // Try ports 3306 and 3307
    $possiblePorts = [3306, 3307];
    $conn = null;

    foreach ($possiblePorts as $port) {
        $conn = @mysqli_connect($host, $user, $pass, $db, $port);
        if ($conn) {
            break; // success
        }
    }

    if (!$conn) {
        // Debug: show real error during development
        die('Database connection error: ' . mysqli_connect_error());
        // For production, just use:
        // die('Database connection error. Please try again later.');
    }

    // Set charset
    mysqli_set_charset($conn, 'utf8mb4');

    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Flash message helper
    if (!function_exists('flash')) {
        function flash($msg = null) {
            if ($msg === null) {
                if (isset($_SESSION['flash'])) {
                    $m = $_SESSION['flash'];
                    unset($_SESSION['flash']);
                    return $m;
                }
                return null;
            } else {
                $_SESSION['flash'] = $msg;
            }
        }
    }

    // Escape output helper
    if (!function_exists('e')) {
        function e($str) {
            return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
        }
    }
}

// Extra session check (just in case)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
