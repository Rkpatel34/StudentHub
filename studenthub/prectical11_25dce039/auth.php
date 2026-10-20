<?php
/**
 * auth.php — StudentHub Authentication & Session Management (Practical 10)
 * 
 * Practical 10: Secure Login/Logout with Sessions, Role-Based Access,
 * Timeout, and Remember-Me Token
 * Course: ITUE203 — Web Development Frameworks
 * 
 * This file provides centralized authentication logic:
 * - Secure session configuration
 * - Login/logout functions
 * - Role-based access control (RBAC)
 * - Session timeout handling
 * - Remember-me token validation
 * - Password verification
 * 
 * Include this file in protected pages:
 *     require_once __DIR__ . '/auth.php';
 *     require_login();           // For any authenticated user
 *     require_role('admin');     // For admin-only pages
 * 
 * Configuration constants at top for easy adjustment.
 */

// ============================================================
// Configuration — Adjust these for your environment
// ============================================================
define('SESSION_TIMEOUT', 15 * 60);           // 15 minutes in seconds (change for testing)
define('REMEMBER_ME_LIFETIME', 30 * 24 * 60 * 60); // 30 days in seconds
define('REMEMBER_COOKIE_NAME', 'studenthub_remember');
define('SESSION_NAME', 'studenthub_session');

// ============================================================
// Database Connection (reuse existing db.php)
// ============================================================
require_once __DIR__ . '/db.php';

// ============================================================
// Secure Session Configuration
// ============================================================
/**
 * Initialize secure session settings.
 * Must be called before session_start().
 */
function init_secure_session(): void {
    // Use secure cookies only on HTTPS (set to true in production)
    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    
    // Session cookie parameters
    session_set_cookie_params([
        'lifetime' => 0,              // Session cookie (expires on browser close)
        'path' => '/',                // Available on all paths
        'domain' => '',               // Current domain
        'secure' => $secure,          // HTTPS only in production
        'httponly' => true,           // No JavaScript access
        'samesite' => 'Lax',          // CSRF protection
    ]);
    
    // Session configuration
    ini_set('session.use_strict_mode', '1');      // Prevent uninitialized session IDs
    ini_set('session.use_only_cookies', '1');     // No session IDs in URLs
    ini_set('session.cookie_httponly', '1');      // HttpOnly flag
    ini_set('session.cookie_secure', $secure ? '1' : '0'); // Secure flag
    ini_set('session.cookie_samesite', 'Lax');    // SameSite attribute
    
    // Start session
    session_start();
}

/**
 * Regenerate session ID securely (call after login).
 */
function regenerate_session(): void {
    session_regenerate_id(true); // Delete old session
    $_SESSION['last_activity'] = time();
    $_SESSION['created'] = time();
}

/**
 * Check if session has timed out.
 * Updates last_activity on each request.
 */
function check_session_timeout(): bool {
    if (!isset($_SESSION['last_activity'])) {
        return true; // No activity timestamp = timeout
    }
    
    $inactive = time() - $_SESSION['last_activity'];
    if ($inactive >= SESSION_TIMEOUT) {
        return true; // Session expired
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
    return false; // Session valid
}

/**
 * Destroy session completely (logout).
 */
function destroy_session(): void {
    // Clear session data
    $_SESSION = [];
    
    // Delete session cookie
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    
    // Destroy session
    session_destroy();
}

/**
 * Set remember-me cookie with token.
 * 
 * @param string $selector Selector part (public)
 * @param string $tokenHash Hashed token (validator)
 */
function set_remember_cookie(string $selector, string $tokenHash): void {
    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    $cookieValue = $selector . ':' . $tokenHash;
    
    setcookie(REMEMBER_COOKIE_NAME, $cookieValue, [
        'expires' => time() + REMEMBER_ME_LIFETIME,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

/**
 * Clear remember-me cookie.
 */
function clear_remember_cookie(): void {
    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    setcookie(REMEMBER_COOKIE_NAME, '', [
        'expires' => time() - 3600,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

/**
 * Parse remember-me cookie.
 * 
 * @return array|null ['selector' => ..., 'tokenHash' => ...] or null
 */
function parse_remember_cookie(): ?array {
    if (empty($_COOKIE[REMEMBER_COOKIE_NAME])) {
        return null;
    }
    
    $parts = explode(':', $_COOKIE[REMEMBER_COOKIE_NAME], 2);
    if (count($parts) !== 2) {
        return null;
    }
    
    return [
        'selector' => $parts[0],
        'tokenHash' => $parts[1],
    ];
}

// ============================================================
// Database Operations (MySQLi prepared statements)
// ============================================================

/**
 * Find user by username or email (case-insensitive).
 * 
 * @param string $identifier Username or email
 * @return array|null User data or null
 */
function find_user_by_identifier(string $identifier): ?array {
    $sql = "SELECT * FROM `users` WHERE (LOWER(`username`) = LOWER(?) OR LOWER(`email`) = LOWER(?)) AND `is_active` = 1 LIMIT 1";
    $rows = db_mysqli_select($sql, [$identifier, $identifier]);
    return $rows[0] ?? null;
}

/**
 * Find user by ID.
 * 
 * @param int $userId User ID
 * @return array|null User data or null
 */
function find_user_by_id(int $userId): ?array {
    $sql = "SELECT * FROM `users` WHERE `user_id` = ? AND `is_active` = 1 LIMIT 1";
    $rows = db_mysqli_select($sql, [$userId]);
    return $rows[0] ?? null;
}

/**
 * Verify user password.
 * 
 * @param string $password Plain password from form
 * @param string $hash Stored password hash
 * @return bool True if valid
 */
function verify_password(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

/**
 * Update last_login timestamp.
 * 
 * @param int $userId User ID
 * @return bool Success
 */
function update_last_login(int $userId): bool {
    $sql = "UPDATE `users` SET `last_login` = CURRENT_TIMESTAMP WHERE `user_id` = ?";
    return db_mysqli_execute($sql, [$userId]) > 0;
}

/**
 * Store remember-me token hash in database.
 * 
 * @param int $userId User ID
 * @param string $selector Selector (public identifier)
 * @param string $tokenHash SHA-256 hash of validator token
 * @return bool Success
 */
function store_remember_token(int $userId, string $selector, string $tokenHash): bool {
    $expires = date('Y-m-d H:i:s', time() + REMEMBER_ME_LIFETIME);
    $sql = "UPDATE `users` SET `remember_token_hash` = ?, `remember_token_expires` = ? WHERE `user_id` = ?";
    return db_mysqli_execute($sql, [$tokenHash, $expires, $userId]) > 0;
}

/**
 * Clear remember-me token from database.
 * 
 * @param int $userId User ID
 * @return bool Success
 */
function clear_remember_token(int $userId): bool {
    $sql = "UPDATE `users` SET `remember_token_hash` = NULL, `remember_token_expires` = NULL WHERE `user_id` = ?";
    return db_mysqli_execute($sql, [$userId]) > 0;
}

/**
 * Validate remember-me token against database.
 * 
 * @param string $selector Selector from cookie
 * @param string $tokenHash Hash from cookie
 * @return array|null User data if valid, null otherwise
 */
function validate_remember_token(string $selector, string $tokenHash): ?array {
    $sql = "SELECT * FROM `users` 
            WHERE `remember_token_hash` = ? 
            AND `remember_token_expires` > NOW() 
            AND `is_active` = 1 
            LIMIT 1";
    $rows = db_mysqli_select($sql, [$tokenHash]);
    
    if (empty($rows)) {
        return null;
    }
    
    $user = $rows[0];
    
    // Verify the token hash matches (timing-safe comparison)
    if (!hash_equals($user['remember_token_hash'], $tokenHash)) {
        return null;
    }
    
    return $user;
}

/**
 * Generate a secure random token pair (selector, validator).
 * 
 * @return array ['selector' => ..., 'validator' => ..., 'tokenHash' => ...]
 */
function generate_remember_token(): array {
    $selector = bin2hex(random_bytes(16));      // 32 chars, public
    $validator = bin2hex(random_bytes(32));     // 64 chars, secret
    $tokenHash = hash('sha256', $validator);    // Store only hash
    
    return [
        'selector' => $selector,
        'validator' => $validator,
        'tokenHash' => $tokenHash,
    ];
}

// ============================================================
// Authentication Functions
// ============================================================

/**
 * Log in a user (create session).
 * 
 * @param array $user User data from database
 * @param bool $remember Whether to set remember-me cookie
 * @return void
 */
function login_user(array $user, bool $remember = false): void {
    // Regenerate session ID for security
    regenerate_session();
    
    // Store minimal user data in session
    $_SESSION['user'] = [
        'user_id' => (int)$user['user_id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'full_name' => $user['full_name'],
        'role' => $user['role'],
        'last_activity' => time(),
        'created' => time(),
    ];
    
    // Handle remember-me
    if ($remember) {
        $token = generate_remember_token();
        if (store_remember_token($user['user_id'], $token['selector'], $token['tokenHash'])) {
            set_remember_cookie($token['selector'], $token['validator']);
        }
    }
    
    // Update last_login
    update_last_login($user['user_id']);
}

/**
 * Log out current user.
 * 
 * @return void
 */
function logout_user(): void {
    // Clear remember-me token from database if user is logged in
    if (is_logged_in()) {
        clear_remember_token($_SESSION['user']['user_id']);
        clear_remember_cookie();
    }
    
    // Destroy session
    destroy_session();
}

/**
 * Check if user is logged in (valid session).
 * Also handles remember-me token auto-login.
 * 
 * @return bool True if authenticated
 */
function is_logged_in(): bool {
    // Initialize secure session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        init_secure_session();
    }
    
    // Check existing session
    if (!empty($_SESSION['user']) && is_array($_SESSION['user'])) {
        // Check session timeout
        if (!check_session_timeout()) {
            return true;
        }
        
        // Session expired - destroy and try remember-me
        destroy_session();
    }
    
    // Try remember-me token
    $cookie = parse_remember_cookie();
    if ($cookie) {
        $user = validate_remember_token($cookie['selector'], $cookie['tokenHash']);
        if ($user) {
            // Valid token - log in user (rotate token for security)
            $newToken = generate_remember_token();
            if (store_remember_token($user['user_id'], $newToken['selector'], $newToken['tokenHash'])) {
                set_remember_cookie($newToken['selector'], $newToken['validator']);
            }
            login_user($user, true); // Keep remember-me active
            return true;
        } else {
            // Invalid/expired token - clear cookie
            clear_remember_cookie();
        }
    }
    
    return false;
}

/**
 * Get current user data from session.
 * 
 * @return array|null User data or null if not logged in
 */
function auth_get_current_user(): ?array {
    if (!is_logged_in()) {
        return null;
    }
    return $_SESSION['user'] ?? null;
}

/**
 * Check if current user has a specific role.
 * 
 * @param string $role Required role ('student' or 'admin')
 * @return bool True if user has role
 */
function has_role(string $role): bool {
    $user = auth_get_current_user();
    return $user && $user['role'] === $role;
}

/**
 * Require authentication - redirect to login if not logged in.
 * 
 * @param string $redirectUrl Login page URL
 * @return void
 */
function require_login(string $redirectUrl = 'login.php'): void {
    if (!is_logged_in()) {
        // Store attempted URL for redirect after login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/';
        header('Location: ' . $redirectUrl);
        exit;
    }
}

/**
 * Require specific role - redirect if not authorized.
 * 
 * @param string $role Required role
 * @param string $redirectUrl Where to redirect unauthorized users
 * @return void
 */
function require_role(string $role, string $redirectUrl = 'dashboard.php'): void {
    require_login();
    
    if (!has_role($role)) {
        // Log the unauthorized access attempt
        $user = get_current_user();
        error_log("StudentHub RBAC: User {$user['user_id']} ({$user['role']}) attempted to access $role-only page");
        
        header('Location: ' . $redirectUrl);
        exit;
    }
}

/**
 * Get appropriate dashboard URL for user's role.
 * 
 * @param array $user User data
 * @return string Dashboard URL
 */
function get_dashboard_url(array $user): string {
    return $user['role'] === 'admin' ? 'admin.php' : 'dashboard.php';
}

// ============================================================
// Initialize secure session on include
// ============================================================
init_secure_session();

// ============================================================
// End of auth.php
// ============================================================