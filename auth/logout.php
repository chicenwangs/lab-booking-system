<?php
/**
 * ============================================================================
 * SQUAD 1: LOGOUT PAGE
 * ============================================================================
 * Destroys user session and redirects to home page
 */

session_start();
require_once '../includes/functions.php';

// Get user name before destroying session
$userName = getCurrentUserName();

// Destroy the session
logoutUser();

// Set farewell message
session_start(); // Restart session just for the flash message
setFlash("Goodbye, {$userName}! You have been logged out successfully.", 'success');

// Redirect to home page
redirect('../index.php');
?>