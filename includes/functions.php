<?php
// includes/functions.php

// Flash message functions

/**
 * Display the flash message (if any) and then clear it.
 */
function show_flash() {
    if (isset($_SESSION['flash_message'])) {
        echo '<div class="alert alert-info mb-3">' . htmlspecialchars($_SESSION['flash_message']) . '</div>';
        unset($_SESSION['flash_message']);
    }
}

/**
 * Set a flash message to show on the next page load.
 * @param string $message The message to display
 */
function set_flash($message) {
    $_SESSION['flash_message'] = $message;
}
?>
