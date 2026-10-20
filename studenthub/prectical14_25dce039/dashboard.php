<?php
/**
 * dashboard.php — Student Dashboard (Protected, Practical 10)
 * 
 * Practical 10: Secure Login/Logout with Sessions, Role-Based Access,
 * Timeout, and Remember-Me Token
 * Course: ITUE203 — Web Development Frameworks
 * 
 * Requires student role. Redirects to login if not authenticated.
 * Redirects to admin.php if user is admin.
 */

require_once __DIR__ . '/auth.php';

// Require student role (admins get redirected to admin dashboard)
require_role('student');

$user = auth_get_current_user();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - StudentHub</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.html" class="brand-logo">🎓 StudentHub</a>
            <ul class="nav-links">
                <li><a href="index.html">🏠 Home</a></li>
                <li><a href="dashboard.php" class="active">📊 Dashboard</a></li>
                <li><a href="Attendance.html">📅 Attendance</a></li>
                <li><a href="assignments.html">📝 Assignments</a></li>
                <li><a href="courses.html">📚 Courses</a></li>
                <li><a href="Timetable.html">🗓️ Timetable</a></li>
                <li><a href="result.html">🏆 Result</a></li>
                <li><a href="profile.php">👤 Profile</a></li>
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
        <div class="box box-full">
            <h1>Student Dashboard</h1>
            <h2>Welcome Back, <?= htmlspecialchars($user['full_name']) ?> 👋</h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
                
                <!-- Profile Card -->
                <div class="card" style="margin: 0;">
                    <h3>👤 Student Profile</h3>
                    <p><b>Name:</b> <?= htmlspecialchars($user['full_name']) ?></p>
                    <p><b>Email:</b> <?= htmlspecialchars($user['email']) ?></p>
                    <p><b>Username:</b> <?= htmlspecialchars($user['username']) ?></p>
                    <p><b>Role:</b> <span style="color:var(--primary); font-weight:bold; text-transform:capitalize;"><?= htmlspecialchars($user['role']) ?></span></p>
                    <a href="profile.php" class="action" style="display:block; text-align:center; padding:0.6rem; border-radius:8px; color:white; margin-top:1rem;">View Full Profile</a>
                </div>

                <!-- Courses Card -->
                <div class="card" style="margin: 0;">
                    <h3>📚 Enrolled Courses</h3>
                    <ul>
                        <li><span>🌐 Web Development</span> <span style="color:var(--success); font-weight:bold;">Active</span></li>
                        <li><span>📊 Data Science</span> <span style="color:var(--success); font-weight:bold;">Active</span></li>
                        <li><span>💻 Programming</span> <span style="color:var(--success); font-weight:bold;">Active</span></li>
                    </ul>
                    <button class="action" onclick="showCourses()">Show Course Count</button>
                    <p id="courseMessage" style="font-weight: bold; margin-top: 0.5rem;"></p>
                </div>

                <!-- Progress Card -->
                <div class="card" style="margin: 0;">
                    <h3>📈 Course Progress</h3>
                    <p>Completed Courses: <b id="progress" style="color:var(--primary); font-size:1.2rem;">3</b></p>
                    <button class="action" onclick="showProgress()">Check Progress Details</button>
                    <p id="progressMessage" style="font-weight: bold; margin-top: 0.5rem;"></p>
                </div>

                <!-- Notifications Card -->
                <div class="card" style="margin: 0;">
                    <h3>🔔 Notifications</h3>
                    <p id="notificationText">Check latest announcements</p>
                    <button class="action" onclick="showNotification()">View Notifications</button>
                    <div id="notification">
                        📢 You have a new assignment deadline this week!
                    </div>
                </div>

            </div>

            <!-- Session Info -->
            <div style="margin-top: 2rem; padding: 1rem; background: var(--light); border-radius: 8px; border: 1px solid var(--border);">
                <h3 style="margin-top: 0;">🔒 Session Information</h3>
                <p><b>Session Started:</b> <?= date('Y-m-d H:i:s', $_SESSION['created'] ?? time()) ?></p>
                <p><b>Last Activity:</b> <?= date('Y-m-d H:i:s', $_SESSION['last_activity'] ?? time()) ?></p>
                <p><b>Timeout:</b> <?= SESSION_TIMEOUT / 60 ?> minutes of inactivity</p>
                <p style="font-size: 0.85rem; color: var(--text-muted);">Your session will expire automatically after inactivity. <a href="logout.php">Logout now</a> to end session immediately.</p>
            </div>

            <!-- Bottom Actions -->
            <div style="margin-top: 2rem; display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
                <a href="logout.php" class="btn-danger" style="max-width: 200px; text-decoration: none; text-align: center;">🚪 Logout</a>
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