<?php
/**
 * logout.php — Secure Logout (Practical 10)
 * 
 * Practical 10: Secure Login/Logout with Sessions, Role-Based Access,
 * Timeout, and Remember-Me Token
 * Course: ITUE203 — Web Development Frameworks
 * 
 * Features:
 * - Clears session data
 * - Expires session cookie
 * - Destroys session
 * - Clears remember-me token from database and cookie
 * - Redirects to login page with success message
 */

require_once __DIR__ . '/auth.php';

// Perform secure logout
logout_user();

// Redirect to login with success message
header('Location: login.php?logout=1');
exit;