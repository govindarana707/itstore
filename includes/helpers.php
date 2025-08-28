<?php
if(session_status() === PHP_SESSION_NONE) session_start();

// Check if user is logged in
if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        return isset($_SESSION['user_id']);
    }
}

// Get flash message and clear it
if (!function_exists('flash')) {
    function flash() {
        $msg = $_SESSION['flash'] ?? '';
        unset($_SESSION['flash']);
        return $msg;
    }
}

// Set flash message
if (!function_exists('set_flash')) {
    function set_flash($msg) {
        $_SESSION['flash'] = $msg;
    }
}

// Escape HTML safely
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

// Redirect helper
if (!function_exists('redirect')) {
    function redirect($url) {
        header("Location: $url");
        exit;
    }
}

// Debug helper
if (!function_exists('dd')) {
    function dd($var) {
        echo '<pre>'; print_r($var); echo '</pre>'; exit;
    }
}

// Cart helpers
if (!function_exists('cart_total_qty')) {
    function cart_total_qty() {
        if(!isset($_SESSION['cart'])) return 0;
        return array_sum(array_column($_SESSION['cart'], 'qty'));
    }
}

if (!function_exists('cart_total_amount')) {
    function cart_total_amount() {
        if(!isset($_SESSION['cart'])) return 0;
        $total = 0;
        foreach($_SESSION['cart'] as $item){
            $total += ($item['price'] ?? 0) * ($item['qty'] ?? 0);
        }
        return $total;
    }
}

// Wishlist helpers
if (!function_exists('wishlist_count')) {
    function wishlist_count() {
        return isset($_SESSION['wishlist']) ? count($_SESSION['wishlist']) : 0;
    }
}
