<?php
/**
 * contact.php — Contact Form Processing (Practical 7)
 * StudentHub — Simple contact form with server-side handling
 */

$errors = [];
$successMessage = '';
$formData = [
    'name'    => '',
    'email'   => '',
    'phone'   => '',
    'subject' => '',
    'message' => ''
];

function cleanInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = isset($_POST['name']) ? cleanInput($_POST['name']) : '';
    $email   = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
    $phone   = isset($_POST['phone']) ? cleanInput($_POST['phone']) : '';
    $subject = isset($_POST['subject']) ? cleanInput($_POST['subject']) : '';
    $message = isset($_POST['message']) ? cleanInput($_POST['message']) : '';

    $formData = compact('name', 'email', 'phone', 'subject', 'message');

    // Validation
    if (empty($name)) $errors['name'] = 'Name is required.';
    if (empty($email)) $errors['email'] = 'Email is required.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email format.';
    if (empty($phone)) $errors['phone'] = 'Phone is required.';
    elseif (!preg_match('/^[0-9]{10}$/', $phone)) $errors['phone'] = 'Phone must be 10 digits.';
    if (empty($subject)) $errors['subject'] = 'Subject is required.';
    if (empty($message)) $errors['message'] = 'Message is required.';

    if (empty($errors)) {
        // In Practical 7, this would save to CSV/JSON
        // For Practical 8, we demonstrate the form works
        $successMessage = "Thank you, <strong>" . htmlspecialchars($name) . "</strong>! Your message has been received (demo mode).";
        $formData = ['name'=>'', 'email'=>'', 'phone'=>'', 'subject'=>'', 'message'=>''];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - StudentHub</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
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
                <li><a href="register.php">📋 Register (PHP)</a></li>
                <li><a href="contact.php" class="active">💬 Contact (PHP)</a></li>
            </ul>
            <div class="nav-actions">
                <button class="theme-toggle-btn" onclick="changeTheme()">🌓 Theme</button>
                <a href="login.php" class="theme-toggle-btn" style="text-decoration:none; background:var(--primary)!important; color:white!important;">🔐 Login</a>
            </div>
        </div>
    </nav>

    <main class="main-wrapper">
        <div class="form-box">
            <h1>💬 Contact Us</h1>
            <p>Send us a message — we'd love to hear from you!</p>

            <?php if (!empty($successMessage)): ?>
                <div class="alert-box alert-success" style="display:block;">
                    <?= $successMessage ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors['system'])): ?>
                <div class="alert-box alert-danger" style="display:block;">
                    <?= htmlspecialchars($errors['system']) ?>
                </div>
            <?php elseif (!empty($errors)): ?>
                <div class="alert-box alert-danger" style="display:block;">
                    ⚠️ Please correct the errors highlighted below.
                </div>
            <?php endif; ?>

            <form method="POST" action="contact.php" novalidate>
                <label for="name">Full Name: *</label>
                <input type="text" id="name" name="name" placeholder="Your full name" value="<?= htmlspecialchars($formData['name']) ?>">
                <?php if (isset($errors['name'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['name']) ?></span>
                <?php endif; ?>

                <label for="email">Email Address: *</label>
                <input type="email" id="email" name="email" placeholder="your@email.com" value="<?= htmlspecialchars($formData['email']) ?>">
                <?php if (isset($errors['email'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['email']) ?></span>
                <?php endif; ?>

                <label for="phone">Phone Number (10 digits): *</label>
                <input type="tel" id="phone" name="phone" maxlength="10" placeholder="10-digit mobile number" value="<?= htmlspecialchars($formData['phone']) ?>">
                <?php if (isset($errors['phone'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['phone']) ?></span>
                <?php endif; ?>

                <label for="subject">Subject: *</label>
                <select id="subject" name="subject">
                    <option value="">-- Select Subject --</option>
                    <option value="General Inquiry" <?= ($formData['subject'] === 'General Inquiry') ? 'selected' : '' ?>>General Inquiry</option>
                    <option value="Technical Support" <?= ($formData['subject'] === 'Technical Support') ? 'selected' : '' ?>>Technical Support</option>
                    <option value="Feedback" <?= ($formData['subject'] === 'Feedback') ? 'selected' : '' ?>>Feedback</option>
                    <option value="Other" <?= ($formData['subject'] === 'Other') ? 'selected' : '' ?>>Other</option>
                </select>
                <?php if (isset($errors['subject'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['subject']) ?></span>
                <?php endif; ?>

                <label for="message">Message: *</label>
                <textarea id="message" name="message" rows="5" placeholder="Your message..."><?= htmlspecialchars($formData['message']) ?></textarea>
                <?php if (isset($errors['message'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['message']) ?></span>
                <?php endif; ?>

                <button type="submit" class="btn-success" style="margin-top: 1rem;">📤 Send Message</button>
                <button type="button" class="btn-secondary" onclick="window.location.href='contact.php'">🔄 Reset</button>
            </form>
        </div>
    </main>

    <footer class="site-footer">
        <p>© 2026 StudentHub Portal • Built with ❤️ for Students • <a href="contact.php">Contact Support</a></p>
    </footer>

    <script src="Js/script.js"></script>
</body>
</html>