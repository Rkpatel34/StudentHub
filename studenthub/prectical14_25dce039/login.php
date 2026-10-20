<?php
/**
 * login.php — Secure Login with Sessions & Remember-Me (Practical 10)
 * 
 * Practical 10: Secure Login/Logout with Sessions, Role-Based Access,
 * Timeout, and Remember-Me Token
 * Course: ITUE203 — Web Development Frameworks
 * 
 * Features:
 * - Server-side validation
 * - Database verification with prepared statements
 * - password_verify() for secure password checking
 * - Session management with timeout
 * - Remember-me token (secure, hashed in DB)
 * - Role-based redirect (student → dashboard.php, admin → admin.php)
 * - Safe error messages (no username/password enumeration)
 */

require_once __DIR__ . '/auth.php';

// If already logged in, redirect to appropriate dashboard
if (is_logged_in()) {
    $user = auth_get_current_user();
    header('Location: ' . get_dashboard_url($user));
    exit;
}

$errors = [];
$formData = ['identifier' => '', 'remember' => false];
$successMessage = '';

// Check for logout success
if (isset($_GET['logout']) && $_GET['logout'] === '1') {
    $successMessage = 'You have been logged out successfully.';
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Retrieve and sanitize inputs
    $identifier = isset($_POST['identifier']) ? trim($_POST['identifier']) : '';
    $password   = isset($_POST['password']) ? $_POST['password'] : ''; // Don't sanitize - verify directly
    $remember   = isset($_POST['remember']);
    
    $formData = ['identifier' => $identifier, 'remember' => $remember];
    
    // 2. Server-side Validation
    if (empty($identifier)) {
        $errors['identifier'] = 'Username or email is required.';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    }
    
    // 3. Authenticate against database (if no validation errors)
    if (empty($errors)) {
        try {
            $user = find_user_by_identifier($identifier);
            
            if ($user && verify_password($password, $user['password_hash'])) {
                // Successful authentication
                login_user($user, $remember);
                
                // Redirect to appropriate dashboard
                header('Location: ' . get_dashboard_url($user));
                exit;
            } else {
                // Generic error - don't reveal if username or password was wrong
                $errors['auth'] = 'Invalid username/email or password. Please try again.';
            }
        } catch (Exception $e) {
            // Safe error handling - log details, show generic message
            error_log("StudentHub Login Failed: " . $e->getMessage());
            $errors['system'] = 'Unable to process login. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - StudentHub</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.html" class="brand-logo">🎓 StudentHub</a>
            <ul class="nav-links">
                <li><a href="index.html">🏠 Home</a></li>
                <li><a href="dashboard.php">📊 Dashboard</a></li>
                <li><a href="Attendance.html">📅 Attendance</a></li>
                <li><a href="assignments.html">📝 Assignments</a></li>
                <li><a href="courses.html">📚 Courses</a></li>
                <li><a href="Timetable.html">🗓️ Timetable</a></li>
                <li><a href="result.html">🏆 Result</a></li>
                <li><a href="profile.html">👤 Profile</a></li>
                <li><a href="admin.php">⚙️ Admin</a></li>
                <li><a href="students.php">👥 Students (PHP)</a></li>
                <li><a href="register.php">📋 Register</a></li>
                <li><a href="contact.php">💬 Contact</a></li>
            </ul>
            <div class="nav-actions">
                <button class="theme-toggle-btn" onclick="changeTheme()">🌓 Theme</button>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-wrapper">
        <div class="form-box" style="max-width: 420px;">
            <h1>🔐 Portal Login</h1>
            <p>Access your Student & Admin Account</p>

            <!-- System Error -->
            <?php if (!empty($errors['system'])): ?>
                <div class="alert-box alert-danger" style="display:block;">
                    <?= htmlspecialchars($errors['system']) ?>
                </div>
            <?php endif; ?>

            <!-- Auth Error (generic) -->
            <?php if (!empty($errors['auth'])): ?>
                <div class="alert-box alert-danger" style="display:block;">
                    ❌ <?= htmlspecialchars($errors['auth']) ?>
                </div>
            <?php endif; ?>

            <!-- Field Errors -->
            <?php if (!empty($errors['identifier']) || !empty($errors['password'])): ?>
                <div class="alert-box alert-danger" style="display:block;">
                    ⚠️ Please correct the errors below.
                </div>
            <?php endif; ?>

            <!-- Success Message -->
            <?php if (!empty($successMessage)): ?>
                <div class="alert-box alert-success" style="display:block;">
                    ✅ <?= htmlspecialchars($successMessage) ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="login.php" novalidate>

                <label for="identifier">Username or Email: *</label>
                <input type="text"
                       id="identifier"
                       name="identifier"
                       placeholder="Enter username or email"
                       value="<?= htmlspecialchars($formData['identifier']) ?>"
                       autocomplete="username">
                <?php if (isset($errors['identifier'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['identifier']) ?></span>
                <?php endif; ?>

                <label for="password">Password: *</label>
                <div style="position: relative;">
                    <input type="password"
                           id="password"
                           name="password"
                           placeholder="Enter your password"
                           autocomplete="current-password">
                    <button type="button" 
                            onclick="togglePassword('password', this)" 
                            style="position: absolute; right: 10px; top: 38px; background: none; border: none; cursor: pointer; color: #666;"
                            aria-label="Show password">
                        👁️
                    </button>
                </div>
                <?php if (isset($errors['password'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['password']) ?></span>
                <?php endif; ?>

                <div style="margin: 0.5rem 0 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" id="remember" name="remember" <?= $formData['remember'] ? 'checked' : '' ?>>
                    <label for="remember" style="font-weight: normal; cursor: pointer; margin: 0;">Remember me (30 days)</label>
                </div>

                <button type="submit" class="btn-success" style="width: 100%; margin-top: 0.5rem;">
                    Login Now ➔
                </button>

                <button type="button" class="btn-secondary" onclick="window.location.href='login.php'" style="width: 100%; margin-top: 0.5rem;">
                    🔄 Clear Form
                </button>

            </form>

            <div style="margin-top: 1.5rem; text-align: center; font-size: 0.9rem;">
                <p>Don't have an account yet?</p>
                <a href="register.php" style="color:var(--primary); font-weight:bold;">Create an account here ➔</a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <p>© 2026 StudentHub Portal • Built with ❤️ for Students • <a href="contact.php">Contact Support</a></p>
    </footer>

    <script>
        // Toggle password visibility
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                btn.textContent = '🙈';
            } else {
                input.type = 'password';
                btn.textContent = '👁️';
            }
        }
        
        // Theme toggle (from existing script.js)
        function changeTheme() {
            document.body.classList.toggle("dark");
            localStorage.setItem("theme", document.body.classList.contains("dark") ? "dark" : "light");
        }
        
        // Apply saved theme
        if (localStorage.getItem("theme") === "dark") {
            document.body.classList.add("dark");
        }
    </script>
</body>

</html>