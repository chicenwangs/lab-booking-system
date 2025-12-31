<?php
/**
 * ============================================================================
 * SQUAD 1: CORE FUNCTIONS & SECURITY
 * ============================================================================
 * The "Security Guard" - All authentication and helper functions
 * This file contains ALL the reusable functions for the entire application
 */

// ============================================================================
// SECURITY & SANITIZATION
// ============================================================================

/**
 * Clean and sanitize user input
 * Prevents XSS attacks by removing scripts and malicious code
 * 
 * @param mixed $data - Input data to clean (string or array)
 * @return mixed - Cleaned data
 */
function clean($data) {
    if (is_array($data)) {
        return array_map('clean', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email format
 * 
 * @param string $email - Email address to validate
 * @return bool - True if valid, false otherwise
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 * Returns array of error messages (empty if valid)
 * 
 * @param string $password - Password to validate
 * @return array - Array of error messages
 */
function validatePassword($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    if (!preg_match("/[A-Z]/", $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    if (!preg_match("/[a-z]/", $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    if (!preg_match("/[0-9]/", $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    return $errors;
}

/**
 * Hash password securely using bcrypt
 * 
 * @param string $password - Plain text password
 * @return string - Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 * 
 * @param string $password - Plain text password
 * @param string $hash - Hashed password from database
 * @return bool - True if password matches
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// ============================================================================
// SESSION & AUTHENTICATION
// ============================================================================

/**
 * Check if user is logged in
 * 
 * @return bool - True if logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if current user is admin
 * 
 * @return bool - True if admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if current user is member
 * 
 * @return bool - True if member
 */
function isMember() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'member';
}

/**
 * Get current user ID
 * 
 * @return int|null - User ID or null if not logged in
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user name
 * 
 * @return string - User name or 'Guest'
 */
function getCurrentUserName() {
    return $_SESSION['user_name'] ?? 'Guest';
}

/**
 * Get current user role
 * 
 * @return string|null - User role or null
 */
function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

/**
 * Require user to be logged in (redirect if not)
 * Use this at the top of pages that need authentication
 */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlash('Please login to continue', 'error');
        redirect('auth/login.php');
    }
}

/**
 * Require user to be admin (redirect if not)
 * Use this at the top of admin pages
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        setFlash('Access denied. Admin privileges required.', 'error');
        redirect('member/dashboard.php');
    }
}

/**
 * Require user to be member (redirect if admin tries to access)
 * Use this at the top of member-only pages
 */
function requireMember() {
    requireLogin();
    if (!isMember()) {
        setFlash('This page is for members only.', 'error');
        redirect('admin/dashboard.php');
    }
}

/**
 * Login user - sets session variables
 * 
 * @param int $userId - User ID from database
 * @param string $userName - User's full name
 * @param string $role - User role (admin/member)
 */
function loginUser($userId, $userName, $role) {
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $userName;
    $_SESSION['role'] = $role;
    $_SESSION['login_time'] = time();
}

/**
 * Logout user - destroys session
 */
function logoutUser() {
    session_unset();
    session_destroy();
}

// ============================================================================
// FLASH MESSAGES (Temporary notifications)
// ============================================================================

/**
 * Set flash message for next page load
 * Types: success, error, warning, info
 * 
 * @param string $message - Message to display
 * @param string $type - Type of message (success/error/warning/info)
 */
function setFlash($message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Display flash message and clear it
 * Call this where you want the message to appear
 */
function displayFlash() {
    if (isset($_SESSION['flash_message'])) {
        $message = clean($_SESSION['flash_message']);
        $type = $_SESSION['flash_type'] ?? 'info';
        
        $alertClass = 'alert-info';
        switch($type) {
            case 'success':
                $alertClass = 'alert-success';
                break;
            case 'error':
                $alertClass = 'alert-error';
                break;
            case 'warning':
                $alertClass = 'alert-warning';
                break;
        }
        
        echo "<div class='alert {$alertClass}'>{$message}</div>";
        
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}

/**
 * Check if there's a flash message waiting
 * 
 * @return bool - True if flash message exists
 */
function hasFlash() {
    return isset($_SESSION['flash_message']);
}

// ============================================================================
// NAVIGATION & ROUTING
// ============================================================================

/**
 * Redirect to another page
 * 
 * @param string $url - URL to redirect to
 */
function redirect($url) {
    // If URL doesn't start with http, treat as relative
    if (!preg_match('/^https?:\/\//', $url)) {
        // Remove leading slash if present
        $url = ltrim($url, '/');
        // Add base path
        $url = '/lab-booking-system/' . $url;
    }
    header("Location: $url");
    exit();
}

/**
 * Get current page name
 * 
 * @return string - Current filename
 */
function getCurrentPage() {
    return basename($_SERVER['PHP_SELF']);
}

/**
 * Check if current page matches given page
 * 
 * @param string $page - Page name to check
 * @return bool - True if matches
 */
function isCurrentPage($page) {
    return getCurrentPage() === $page;
}

/**
 * Generate navigation link with active class
 * 
 * @param string $url - Link URL
 * @param string $text - Link text
 * @param string $activeClass - CSS class for active link
 * @return string - HTML link element
 */
function navLink($url, $text, $activeClass = 'active') {
    $current = getCurrentPage();
    $class = (basename($url) === $current) ? " class='{$activeClass}'" : '';
    return "<a href='{$url}'{$class}>{$text}</a>";
}

// ============================================================================
// FORMATTING HELPERS
// ============================================================================

/**
 * Format currency
 * 
 * @param float $amount - Amount to format
 * @return string - Formatted currency string
 */
function formatCurrency($amount) {
    return '$' . number_format((float)$amount, 2);
}

/**
 * Format date
 * 
 * @param string $date - Date string
 * @param string $format - Date format
 * @return string - Formatted date
 */
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Format time
 * 
 * @param string $time - Time string
 * @param string $format - Time format
 * @return string - Formatted time
 */
function formatTime($time, $format = 'g:i A') {
    return date($format, strtotime($time));
}

/**
 * Format date and time together
 * 
 * @param string $datetime - DateTime string
 * @param string $format - DateTime format
 * @return string - Formatted datetime
 */
function formatDateTime($datetime, $format = 'M d, Y g:i A') {
    return date($format, strtotime($datetime));
}

/**
 * Get time ago (e.g., "2 hours ago")
 * 
 * @param string $datetime - DateTime string
 * @return string - Relative time string
 */
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return formatDate($datetime);
    }
}

// ============================================================================
// STATUS & BADGE HELPERS
// ============================================================================

/**
 * Get badge class for booking status
 * 
 * @param string $status - Booking status
 * @return string - CSS class name
 */
function getStatusBadgeClass($status) {
    $badges = [
        'confirmed' => 'badge-success',
        'pending' => 'badge-warning',
        'cancelled' => 'badge-danger',
        'completed' => 'badge-info'
    ];
    return $badges[$status] ?? 'badge-info';
}

/**
 * Display status badge HTML
 * 
 * @param string $status - Status text
 * @return string - HTML badge element
 */
function displayStatusBadge($status) {
    $class = getStatusBadgeClass($status);
    $label = ucfirst($status);
    return "<span class='badge {$class}'>{$label}</span>";
}

/**
 * Get lab status badge class
 * 
 * @param string $status - Lab status
 * @return string - CSS class name
 */
function getLabStatusBadgeClass($status) {
    $badges = [
        'active' => 'badge-success',
        'inactive' => 'badge-danger',
        'maintenance' => 'badge-warning'
    ];
    return $badges[$status] ?? 'badge-info';
}

// ============================================================================
// VALIDATION HELPERS
// ============================================================================

/**
 * Validate required fields
 * 
 * @param array $fields - Array of field => label pairs
 * @param array $data - Posted form data
 * @return array - Array of error messages
 */
function validateRequired($fields, $data) {
    $errors = [];
    foreach ($fields as $field => $label) {
        if (empty($data[$field])) {
            $errors[] = "{$label} is required";
        }
    }
    return $errors;
}

/**
 * Validate date is not in the past
 * 
 * @param string $date - Date string
 * @return bool - True if valid future date
 */
function isValidFutureDate($date) {
    $inputDate = strtotime($date);
    $today = strtotime(date('Y-m-d'));
    return $inputDate >= $today;
}

/**
 * Validate time range (end time after start time)
 * 
 * @param string $startTime - Start time
 * @param string $endTime - End time
 * @return bool - True if valid range
 */
function isValidTimeRange($startTime, $endTime) {
    return strtotime($endTime) > strtotime($startTime);
}

// ============================================================================
// ERROR HANDLING
// ============================================================================

/**
 * Display errors in formatted list
 * 
 * @param array $errors - Array of error messages
 * @return string - HTML error display
 */
function displayErrors($errors) {
    if (empty($errors)) {
        return '';
    }
    
    $html = '<div class="alert alert-error"><ul style="margin: 0; padding-left: 1.5rem;">';
    foreach ($errors as $error) {
        $html .= '<li>' . clean($error) . '</li>';
    }
    $html .= '</ul></div>';
    return $html;
}

/**
 * Log error to file (for debugging)
 * 
 * @param string $message - Error message
 * @param string $file - Log file name
 */
function logError($message, $file = 'error.log') {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] {$message}\n";
    error_log($logMessage, 3, $file);
}

// ============================================================================
// CSRF PROTECTION (For Squad 1 - Security)
// ============================================================================

/**
 * Generate CSRF token
 * 
 * @return string - CSRF token
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * 
 * @param string $token - Token to validate
 * @return bool - True if valid
 */
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Output CSRF token input field
 * 
 * @return string - Hidden input HTML
 */
function csrfField() {
    $token = generateCsrfToken();
    return "<input type='hidden' name='csrf_token' value='{$token}'>";
}

/**
 * Check CSRF token from POST request
 * Automatically validates and redirects if invalid
 */
function checkCsrfToken() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!validateCsrfToken($token)) {
            setFlash('Invalid security token. Please try again.', 'error');
            redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
        }
    }
}

// ============================================================================
// DEBUGGING HELPERS (Remove in production!)
// ============================================================================

/**
 * Pretty print variable (for debugging)
 * 
 * @param mixed $var - Variable to dump
 * @param bool $exit - Whether to exit after dump
 */
function dd($var, $exit = true) {
    echo '<pre style="background: #f4f4f4; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">';
    print_r($var);
    echo '</pre>';
    if ($exit) {
        exit();
    }
}

/**
 * Dump variable and continue
 * 
 * @param mixed $var - Variable to dump
 */
function dump($var) {
    dd($var, false);
}
?>