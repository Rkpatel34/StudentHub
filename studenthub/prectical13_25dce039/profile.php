<?php
/**
 * profile.php — User Profile (Protected, Practical 10)
 * 
 * Practical 10: Secure Login/Logout with Sessions, Role-Based Access,
 * Timeout, and Remember-Me Token
 * Course: ITUE203 — Web Development Frameworks
 * 
 * Shows current user's profile information.
 * Requires authentication.
 */

require_once __DIR__ . '/auth.php';
require_login();

$user = auth_get_current_user();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - StudentHub</title>
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
                <li><a href="profile.php" class="active">👤 Profile</a></li>
                <li><a href="admin.php">⚙️ Admin</a></li>
                <li><a href="students.php">👥 Students (PHP)</a></li>
                <li><a href="register.php">📋 Register</a></li>
                <li><a href="contact.php">💬 Contact</a></li>
            </ul>
            <div class="nav-actions">
                <button class="theme-toggle-btn" onclick="changeTheme()">🌓 Theme</button>
                <a href="logout.php" class="theme-toggle-btn btn-danger" style="text-decoration:none; cursor:pointer;">🚪 Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-wrapper">
        <div class="box" style="max-width: 600px;">
            <h1>👤 User Profile</h1>
            <p>Your account information</p>

            <div class="card" style="margin: 1.5rem 0;">
                <h3>Account Details</h3>
                <table style="width: 100%;">
                    <tr>
                        <td style="font-weight: bold; width: 30%;">User ID:</td>
                        <td><?= htmlspecialchars($user['user_id']) ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Username:</td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Email:</td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Full Name:</td>
                        <td><?= htmlspecialchars($user['full_name']) ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Role:</td>
                        <td><span style="color:var(--primary); font-weight:bold; text-transform:capitalize;"><?= htmlspecialchars($user['role']) ?></span></td>
                    </tr>
                </table>
            </div>

            <div class="card" style="margin: 1.5rem 0;">
                <h3>Session Information</h3>
                <table style="width: 100%;">
                    <tr>
                        <td style="font-weight: bold; width: 30%;">Session Started:</td>
                        <td><?= date('Y-m-d H:i:s', $_SESSION['created'] ?? time()) ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Last Activity:</td>
                        <td><?= date('Y-m-d H:i:s', $_SESSION['last_activity'] ?? time()) ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Timeout Setting:</td>
                        <td><?= SESSION_TIMEOUT / 60 ?> minutes of inactivity</td>
                    </tr>
                </table>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 2rem;">
                <a href="dashboard.php" class="action" style="text-decoration:none;">← Back to Dashboard</a>
                <a href="logout.php" class="btn-danger" style="text-decoration:none;">🚪 Logout</a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <p>© 2026 StudentHub Portal • Built with ❤️ for Students • <a href="contact.php">Contact Support</a></p>
    </footer>

    <script src="Js/script.js"></script>
</body>

</html>