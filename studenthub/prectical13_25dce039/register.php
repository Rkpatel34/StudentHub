<?php
/**
 * register.php — Secure User Registration with MySQLi (Practical 9)
 * 
 * Practical 9: Secure User Registration with Database Integration
 * Course: ITUE203 — Web Development Frameworks
 * 
 * Features:
 * - Server-side validation & sanitization of inputs
 * - Duplicate username/email checks using MySQLi prepared statements
 * - Secure password hashing with password_hash()
 * - MySQL database storage (users table)
 * - Detailed success and error message reporting
 * - Responsive UI matching Student Hub Portal theme
 * 
 * Uses MySQLi prepared statements as required by Practical 9.
 * Reuses the same database configuration from db.php (Practical 8).
 */

require_once __DIR__ . '/db.php';

// Initialize response variables
$errors = [];
$successMessage = '';
$formData = [
    'name'     => '',
    'email'    => '',
    'phone'    => '',
    'username' => '',
    'gender'   => '',
    'course'   => '',
    'year'     => '',
    'terms'    => false
];

// Helper function to sanitize string inputs
function cleanInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Retrieve and sanitize inputs
    $name     = isset($_POST['name']) ? cleanInput($_POST['name']) : '';
    $email    = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
    $phone    = isset($_POST['phone']) ? cleanInput($_POST['phone']) : '';
    $username = isset($_POST['username']) ? cleanInput($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : ''; // Don't sanitize password - hash it directly
    $confirm  = isset($_POST['confirm']) ? $_POST['confirm'] : '';
    $gender   = isset($_POST['gender']) ? cleanInput($_POST['gender']) : '';
    $course   = isset($_POST['course']) ? cleanInput($_POST['course']) : '';
    $year     = isset($_POST['year']) ? cleanInput($_POST['year']) : '';
    $terms    = isset($_POST['terms']);

    // Retain sanitized form data for sticky form (except passwords)
    $formData = [
        'name'     => $name,
        'email'    => $email,
        'phone'    => $phone,
        'username' => $username,
        'gender'   => $gender,
        'course'   => $course,
        'year'     => $year,
        'terms'    => $terms
    ];

    // 2. Server-side Validation
    // Validate Name
    if (empty($name)) {
        $errors['name'] = 'Full Name is required.';
    } elseif (!preg_match("/^[a-zA-Z\s]{3,50}$/", $name)) {
        $errors['name'] = 'Full Name must contain only letters and spaces (3-50 characters).';
    }

    // Validate Email
    if (empty($email)) {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please provide a valid email address.';
    }

    // Validate Phone Number
    if (empty($phone)) {
        $errors['phone'] = 'Phone number is required.';
    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
        $errors['phone'] = 'Phone number must be exactly 10 digits.';
    }

    // Validate Username
    if (empty($username)) {
        $errors['username'] = 'Username is required.';
    } elseif (!preg_match("/^[a-zA-Z0-9_]{3,20}$/", $username)) {
        $errors['username'] = 'Username must be 3-20 characters long (letters, numbers, and underscores only).';
    }

    // Validate Password
    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters long.';
    }

    // Validate Confirm Password
    if (empty($confirm)) {
        $errors['confirm'] = 'Please confirm your password.';
    } elseif ($password !== $confirm) {
        $errors['confirm'] = 'Passwords do not match.';
    }

    // Validate Gender
    $validGenders = ['Male', 'Female', 'Other'];
    if (empty($gender)) {
        $errors['gender'] = 'Please select your gender.';
    } elseif (!in_array($gender, $validGenders)) {
        $errors['gender'] = 'Invalid gender selected.';
    }

    // Validate Course
    $validCourses = ['Web Development', 'Data Science', 'Cyber Security', 'Programming'];
    if (empty($course)) {
        $errors['course'] = 'Please select a course.';
    } elseif (!in_array($course, $validCourses)) {
        $errors['course'] = 'Invalid course selected.';
    }

    // Validate Year
    $validYears = ['1', '2', '3', '4'];
    if (empty($year)) {
        $errors['year'] = 'Please select your academic year.';
    } elseif (!in_array($year, $validYears)) {
        $errors['year'] = 'Invalid year selected.';
    }

    // Validate Terms & Conditions
    if (!$terms) {
        $errors['terms'] = 'You must agree to the Terms and Conditions.';
    }

    // 3. Check for Duplicate Username or Email using MySQLi prepared statements
    if (empty($errors)) {
        try {
            // Check username (case-insensitive)
            $existingUser = db_mysqli_select(
                "SELECT user_id FROM `users` WHERE LOWER(`username`) = LOWER(?) LIMIT 1",
                [$username]
            );
            if (!empty($existingUser)) {
                $errors['username'] = 'Username is already taken. Please choose another.';
            }

            // Check email (case-insensitive)
            if (empty($errors)) {
                $existingEmail = db_mysqli_select(
                    "SELECT user_id FROM `users` WHERE LOWER(`email`) = LOWER(?) LIMIT 1",
                    [$email]
                );
                if (!empty($existingEmail)) {
                    $errors['email'] = 'Email is already registered. Please login or use another email.';
                }
            }
        } catch (Exception $e) {
            // Safe error handling — don't expose database details
            error_log("StudentHub Registration Duplicate Check Failed: " . $e->getMessage());
            $errors['system'] = 'Unable to verify account details. Please try again later.';
        }
    }

    // 4. Save New User if Validation Passes
    if (empty($errors)) {
        try {
            // Hash the password securely
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // Insert using MySQLi prepared statement
            $newUserId = db_mysqli_insert(
                "INSERT INTO `users` (`username`, `email`, `password_hash`, `full_name`, `phone`, `gender`, `course`, `year`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [$username, $email, $passwordHash, $name, $phone, $gender, $course, $year]
            );

            if ($newUserId) {
                $successMessage = "Registration successful! Welcome to StudentHub, <strong>" . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "</strong>. Your account has been created securely.";
                // Reset sticky form data
                $formData = [
                    'name'     => '',
                    'email'    => '',
                    'phone'    => '',
                    'username' => '',
                    'gender'   => '',
                    'course'   => '',
                    'year'     => '',
                    'terms'    => false
                ];
            } else {
                $errors['system'] = 'Registration failed. Please try again.';
            }
        } catch (Exception $e) {
            // Safe error handling — don't expose database details
            error_log("StudentHub Registration Insert Failed: " . $e->getMessage());
            
            // Check for duplicate entry error (race condition)
            $errorMsg = $e->getMessage();
            if (strpos($errorMsg, 'Duplicate entry') !== false) {
                if (strpos($errorMsg, 'uq_users_username') !== false || strpos($errorMsg, 'username') !== false) {
                    $errors['username'] = 'Username is already taken. Please choose another.';
                } elseif (strpos($errorMsg, 'uq_users_email') !== false || strpos($errorMsg, 'email') !== false) {
                    $errors['email'] = 'Email is already registered. Please login or use another email.';
                } else {
                    $errors['system'] = 'This account already exists. Please try a different username or email.';
                }
            } else {
                $errors['system'] = 'Unable to create account. Please try again later.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration (MySQLi) - StudentHub</title>
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
                <li><a href="profile.php">👤 Profile</a></li>
                <li><a href="admin.php">⚙️ Admin</a></li>
                <li><a href="students.php">👥 Students (PHP)</a></li>
                <li><a href="register.php" class="active">📋 Register (MySQLi)</a></li>
                <li><a href="contact.php">💬 Contact (PHP)</a></li>
            </ul>
            <div class="nav-actions">
                <button class="theme-toggle-btn" onclick="changeTheme()">🌓 Theme</button>
                <a href="login.php" class="theme-toggle-btn" style="text-decoration:none; background:var(--primary)!important; color:white!important;">🔐 Login</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-wrapper">
        <div class="form-box">
            <h1>📋 Student Registration (MySQLi)</h1>
            <p>Secure backend validation, password hashing & MySQL database storage with prepared statements</p>

            <!-- Success Alert -->
            <?php if (!empty($successMessage)): ?>
                <div class="alert-box alert-success" style="display:block;">
                    <?= $successMessage ?>
                </div>
            <?php endif; ?>

            <!-- Error Summary -->
            <?php if (!empty($errors['system'])): ?>
                <div class="alert-box alert-danger" style="display:block;">
                    <?= htmlspecialchars($errors['system']) ?>
                </div>
            <?php elseif (!empty($errors)): ?>
                <div class="alert-box alert-danger" style="display:block;">
                    ⚠️ Please correct the errors highlighted below.
                </div>
            <?php endif; ?>

            <!-- Registration Form -->
            <form id="registerForm" method="POST" action="register.php" novalidate>

                <label for="name">Full Name: *</label>
                <input type="text"
                       id="name"
                       name="name"
                       placeholder="Enter your full name"
                       value="<?= htmlspecialchars($formData['name']) ?>">
                <?php if (isset($errors['name'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['name']) ?></span>
                <?php endif; ?>

                <label for="email">Email Address: *</label>
                <input type="email"
                       id="email"
                       name="email"
                       placeholder="Enter your email address"
                       value="<?= htmlspecialchars($formData['email']) ?>">
                <?php if (isset($errors['email'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['email']) ?></span>
                <?php endif; ?>

                <label for="phone">Phone Number (10 Digits): *</label>
                <input type="tel"
                       id="phone"
                       name="phone"
                       maxlength="10"
                       placeholder="Enter 10-digit mobile number"
                       value="<?= htmlspecialchars($formData['phone']) ?>">
                <?php if (isset($errors['phone'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['phone']) ?></span>
                <?php endif; ?>

                <label for="username">Username: *</label>
                <input type="text"
                       id="username"
                       name="username"
                       placeholder="Choose a username"
                       value="<?= htmlspecialchars($formData['username']) ?>">
                <?php if (isset($errors['username'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['username']) ?></span>
                <?php endif; ?>

                <label for="password">Password (min 6 chars): *</label>
                <input type="password"
                       id="password"
                       name="password"
                       placeholder="Enter strong password"
                       autocomplete="new-password">
                <?php if (isset($errors['password'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['password']) ?></span>
                <?php endif; ?>

                <label for="confirm">Confirm Password: *</label>
                <input type="password"
                       id="confirm"
                       name="confirm"
                       placeholder="Re-enter password"
                       autocomplete="new-password">
                <?php if (isset($errors['confirm'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['confirm']) ?></span>
                <?php endif; ?>

                <label>Gender: *</label>
                <div class="radio-group">
                    <label><input type="radio" name="gender" value="Male" <?= ($formData['gender'] === 'Male') ? 'checked' : '' ?>> Male</label>
                    <label><input type="radio" name="gender" value="Female" <?= ($formData['gender'] === 'Female') ? 'checked' : '' ?>> Female</label>
                    <label><input type="radio" name="gender" value="Other" <?= ($formData['gender'] === 'Other') ? 'checked' : '' ?>> Other</label>
                </div>
                <?php if (isset($errors['gender'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['gender']) ?></span>
                <?php endif; ?>

                <label for="course">Course: *</label>
                <select id="course" name="course">
                    <option value="">-- Select Course --</option>
                    <option value="Web Development" <?= ($formData['course'] === 'Web Development') ? 'selected' : '' ?>>Web Development</option>
                    <option value="Data Science" <?= ($formData['course'] === 'Data Science') ? 'selected' : '' ?>>Data Science</option>
                    <option value="Cyber Security" <?= ($formData['course'] === 'Cyber Security') ? 'selected' : '' ?>>Cyber Security</option>
                    <option value="Programming" <?= ($formData['course'] === 'Programming') ? 'selected' : '' ?>>Programming</option>
                </select>
                <?php if (isset($errors['course'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['course']) ?></span>
                <?php endif; ?>

                <label for="year">Year of Study: *</label>
                <select id="year" name="year">
                    <option value="">-- Select Year --</option>
                    <option value="1" <?= ($formData['year'] === '1') ? 'selected' : '' ?>>First Year (1st)</option>
                    <option value="2" <?= ($formData['year'] === '2') ? 'selected' : '' ?>>Second Year (2nd)</option>
                    <option value="3" <?= ($formData['year'] === '3') ? 'selected' : '' ?>>Third Year (3rd)</option>
                    <option value="4" <?= ($formData['year'] === '4') ? 'selected' : '' ?>>Fourth Year (4th)</option>
                </select>
                <?php if (isset($errors['year'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['year']) ?></span>
                <?php endif; ?>

                <div style="margin: 1rem 0 0.5rem; text-align:left;">
                    <label style="cursor:pointer; display:inline-flex; align-items:center; font-weight:normal;">
                        <input type="checkbox" id="terms" name="terms" <?= $formData['terms'] ? 'checked' : '' ?>>
                        I agree to the Terms and Conditions
                    </label>
                </div>
                <?php if (isset($errors['terms'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['terms']) ?></span>
                <?php endif; ?>

                <button type="submit" class="btn-success" style="margin-top: 1rem;">
                    🚀 Register Account
                </button>

                <button type="button" class="btn-secondary" onclick="window.location.href='register.php'">
                    🔄 Reset Form
                </button>

            </form>

            <div style="margin-top: 1.5rem; text-align: center; font-size: 0.9rem;">
                <p>Already registered?</p>
                <a href="login.html" style="color:var(--primary); font-weight:bold;">Login to your portal ➔</a>
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