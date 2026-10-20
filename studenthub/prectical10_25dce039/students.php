<?php
/**
 * students.php - Student Records Management & Processing (Protected, Practical 10)
 * 
 * Features:
 * - Reads and displays student records from students.json
 * - Server-side validation & sanitization for adding new students
 * - Duplicate roll number prevention
 * - Calculates student statistics (Total count, average CGPA)
 * - Built-in search, department filtering, and sorting
 * - REST JSON endpoint support for AJAX / API integration
 * - Modern responsive Student Hub UI with dark/light mode
 * 
 * Practical 10: Requires authentication
 */

require_once __DIR__ . '/auth.php';
require_login(); // Any authenticated user can access

$jsonFile = __DIR__ . DIRECTORY_SEPARATOR . 'students.json';

// Initialize variables
$errors = [];
$successMessage = '';
$newStudentData = [
    'rollNo'     => '',
    'name'       => '',
    'department' => '',
    'cgpa'       => '',
    'city'       => ''
];

// Helper to sanitize text
function cleanInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// 1. Read existing students from JSON file
$students = [];
if (file_exists($jsonFile)) {
    $raw = file_get_contents($jsonFile);
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $students = $decoded;
    }
}

// 2. Handle REST API / JSON Output Requests (e.g. ?action=list)
if (isset($_GET['action']) && $_GET['action'] === 'list') {
    header('Content-Type: application/json');
    echo json_encode($students, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// 3. Process Add Student Form Submission (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rollNo     = isset($_POST['rollNo']) ? strtoupper(cleanInput($_POST['rollNo'])) : '';
    $name       = isset($_POST['name']) ? cleanInput($_POST['name']) : '';
    $department = isset($_POST['department']) ? cleanInput($_POST['department']) : '';
    $cgpaInput  = isset($_POST['cgpa']) ? trim($_POST['cgpa']) : '';
    $city       = isset($_POST['city']) ? cleanInput($_POST['city']) : '';

    $newStudentData = [
        'rollNo'     => $rollNo,
        'name'       => $name,
        'department' => $department,
        'cgpa'       => $cgpaInput,
        'city'       => $city
    ];

    // Server-side Validation
    // Validate Roll Number
    if (empty($rollNo)) {
        $errors['rollNo'] = 'Roll Number is required.';
    } elseif (!preg_match("/^[A-Z0-9]{5,12}$/", $rollNo)) {
        $errors['rollNo'] = 'Roll Number format is invalid (e.g., 25DCE039).';
    }

    // Validate Name
    if (empty($name)) {
        $errors['name'] = 'Student Name is required.';
    } elseif (!preg_match("/^[a-zA-Z\s]{3,50}$/", $name)) {
        $errors['name'] = 'Name must be 3-50 characters with letters and spaces only.';
    }

    // Validate Department
    $validDepartments = ['Computer', 'IT', 'Civil', 'AI', 'Mechanical', 'Electrical'];
    if (empty($department)) {
        $errors['department'] = 'Please select a department.';
    } elseif (!in_array($department, $validDepartments)) {
        $errors['department'] = 'Invalid department selected.';
    }

    // Validate CGPA
    if ($cgpaInput === '') {
        $errors['cgpa'] = 'CGPA is required.';
    } elseif (!is_numeric($cgpaInput)) {
        $errors['cgpa'] = 'CGPA must be a valid number.';
    } else {
        $cgpaVal = floatval($cgpaInput);
        if ($cgpaVal < 0.0 || $cgpaVal > 10.0) {
            $errors['cgpa'] = 'CGPA must be between 0.0 and 10.0.';
        }
    }

    // Validate City
    if (empty($city)) {
        $errors['city'] = 'City is required.';
    } elseif (!preg_match("/^[a-zA-Z\s]{2,40}$/", $city)) {
        $errors['city'] = 'City must only contain letters and spaces.';
    }

    // Check for Duplicate Roll Number
    if (empty($errors)) {
        foreach ($students as $student) {
            if (isset($student['rollNo']) && strtoupper($student['rollNo']) === $rollNo) {
                $errors['rollNo'] = "Roll Number '$rollNo' already exists in the records.";
                break;
            }
        }
    }

    // Save record if valid
    if (empty($errors)) {
        $newId = 1;
        if (!empty($students)) {
            $ids = array_column($students, 'id');
            $newId = (!empty($ids)) ? max($ids) + 1 : 1;
        }

        $newStudent = [
            'id'         => $newId,
            'rollNo'     => $rollNo,
            'name'       => $name,
            'department' => $department,
            'cgpa'       => round(floatval($cgpaInput), 2),
            'city'       => $city
        ];

        $students[] = $newStudent;

        $saveResult = file_put_contents(
            $jsonFile,
            json_encode($students, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            LOCK_EX
        );

        if ($saveResult !== false) {
            $successMessage = "Student <strong>" . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . " (" . htmlspecialchars($rollNo, ENT_QUOTES, 'UTF-8') . ")</strong> successfully added to <code>students.json</code>!";
            // Reset input values
            $newStudentData = [
                'rollNo'     => '',
                'name'       => '',
                'department' => '',
                'cgpa'       => '',
                'city'       => ''
            ];
        } else {
            $errors['system'] = 'Error saving data to students.json. Please check file write permissions.';
        }
    }
}

// 4. Calculate Summary Statistics
$totalStudents = count($students);
$avgCGPA = 0.0;
$departmentCounts = [];

if ($totalStudents > 0) {
    $cgpaSum = array_sum(array_column($students, 'cgpa'));
    $avgCGPA = round($cgpaSum / $totalStudents, 2);

    foreach ($students as $s) {
        $dept = $s['department'] ?? 'Other';
        $departmentCounts[$dept] = ($departmentCounts[$dept] ?? 0) + 1;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Records Management (PHP) - StudentHub</title>
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
                <li><a href="students.php" class="active">👥 Students (PHP)</a></li>
                <li><a href="register.php">📋 Register (PHP)</a></li>
                <li><a href="contact.php">💬 Contact (PHP)</a></li>
            </ul>
            <div class="nav-actions">
                <button class="theme-toggle-btn" onclick="changeTheme()">🌓 Theme</button>
                <a href="logout.php" class="theme-toggle-btn btn-danger" style="text-decoration:none;">🚪 Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-wrapper">
        <div class="box box-full">
            <h1>👥 Student Records Management (PHP & JSON)</h1>
            <p>Perform live database operations using JSON persistence with server-side processing</p>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?= $totalStudents ?></div>
                    <div class="stat-label">Total Registered Students</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $avgCGPA ?></div>
                    <div class="stat-label">Average Batch CGPA</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= count($departmentCounts) ?></div>
                    <div class="stat-label">Active Departments</div>
                </div>
            </div>

            <!-- Success Alert -->
            <?php if (!empty($successMessage)): ?>
                <div class="alert-box alert-success" style="display:block;">
                    <?= $successMessage ?>
                </div>
            <?php endif; ?>

            <!-- Error Alert -->
            <?php if (!empty($errors['system'])): ?>
                <div class="alert-box alert-danger" style="display:block;">
                    <?= htmlspecialchars($errors['system']) ?>
                </div>
            <?php elseif (!empty($errors)): ?>
                <div class="alert-box alert-danger" style="display:block;">
                    ⚠️ Please fix the highlighted errors in the form below.
                </div>
            <?php endif; ?>

            <!-- Add Student Card -->
            <div class="card" style="max-width: 100%; margin: 1.5rem 0;">
                <h3 style="margin-top:0;">➕ Add New Student Record</h3>
                <form method="POST" action="students.php" novalidate style="background:transparent; border:none; box-shadow:none; padding:0; max-width:100%;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div>
                            <label for="rollNo">Roll Number: *</label>
                            <input type="text"
                                   id="rollNo"
                                   name="rollNo"
                                   placeholder="e.g. 25DCE040"
                                   value="<?= htmlspecialchars($newStudentData['rollNo']) ?>">
                            <?php if (isset($errors['rollNo'])): ?>
                                <span class="error-text"><?= htmlspecialchars($errors['rollNo']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="name">Student Name: *</label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   placeholder="e.g. Rahul Sharma"
                                   value="<?= htmlspecialchars($newStudentData['name']) ?>">
                            <?php if (isset($errors['name'])): ?>
                                <span class="error-text"><?= htmlspecialchars($errors['name']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="department">Department: *</label>
                            <select id="department" name="department">
                                <option value="">-- Choose Dept --</option>
                                <option value="Computer" <?= ($newStudentData['department'] === 'Computer') ? 'selected' : '' ?>>Computer</option>
                                <option value="IT" <?= ($newStudentData['department'] === 'IT') ? 'selected' : '' ?>>IT</option>
                                <option value="Civil" <?= ($newStudentData['department'] === 'Civil') ? 'selected' : '' ?>>Civil</option>
                                <option value="AI" <?= ($newStudentData['department'] === 'AI') ? 'selected' : '' ?>>AI</option>
                                <option value="Mechanical" <?= ($newStudentData['department'] === 'Mechanical') ? 'selected' : '' ?>>Mechanical</option>
                                <option value="Electrical" <?= ($newStudentData['department'] === 'Electrical') ? 'selected' : '' ?>>Electrical</option>
                            </select>
                            <?php if (isset($errors['department'])): ?>
                                <span class="error-text"><?= htmlspecialchars($errors['department']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="cgpa">CGPA (0.0 - 10.0): *</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   max="10"
                                   id="cgpa"
                                   name="cgpa"
                                   placeholder="e.g. 9.25"
                                   value="<?= htmlspecialchars($newStudentData['cgpa']) ?>">
                            <?php if (isset($errors['cgpa'])): ?>
                                <span class="error-text"><?= htmlspecialchars($errors['cgpa']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="city">City: *</label>
                            <input type="text"
                                   id="city"
                                   name="city"
                                   placeholder="e.g. Ahmedabad"
                                   value="<?= htmlspecialchars($newStudentData['city']) ?>">
                            <?php if (isset($errors['city'])): ?>
                                <span class="error-text"><?= htmlspecialchars($errors['city']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="display: flex; gap: 1rem; margin-top: 1rem; max-width: 400px;">
                        <button type="submit" class="btn-success">➕ Add Student</button>
                        <button type="button" class="btn-secondary" onclick="window.location.href='students.php'">🔄 Reset</button>
                    </div>
                </form>
            </div>

            <!-- Interactive Filters -->
            <div class="controls">
                <input type="text" id="liveSearch" placeholder="🔍 Live filter table..." onkeyup="filterPhpTable()">
                <select id="filterDept" onchange="filterPhpTable()">
                    <option value="All">🏢 All Departments</option>
                    <option value="Computer">💻 Computer</option>
                    <option value="IT">🌐 IT</option>
                    <option value="Civil">🏗️ Civil</option>
                    <option value="AI">🤖 AI</option>
                    <option value="Mechanical">⚙️ Mechanical</option>
                    <option value="Electrical">⚡ Electrical</option>
                </select>
                <a href="students.php?action=list" target="_blank" class="btn btn-secondary" style="width:auto; padding:0.6rem 1rem;">
                    📄 Export JSON
                </a>
            </div>

            <!-- Student Records Table -->
            <div class="table-responsive">
                <table id="recordsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Roll Number</th>
                            <th>Student Name</th>
                            <th>Department</th>
                            <th>CGPA</th>
                            <th>City</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="6" style="padding:2rem; color:var(--text-muted);">No student records found in <code>students.json</code>.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $student): ?>
                                <?php
                                    $badge = 'badge-primary';
                                    $dept = $student['department'] ?? '';
                                    if ($dept === 'IT') $badge = 'badge-success';
                                    if ($dept === 'AI') $badge = 'badge-warning';
                                    if ($dept === 'Civil') $badge = 'badge-danger';
                                ?>
                                <tr>
                                    <td><b>#<?= htmlspecialchars($student['id'] ?? '') ?></b></td>
                                    <td><span style="font-weight:700; color:var(--primary);"><?= htmlspecialchars($student['rollNo'] ?? '') ?></span></td>
                                    <td style="font-weight:600;"><?= htmlspecialchars($student['name'] ?? '') ?></td>
                                    <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($dept) ?></span></td>
                                    <td><span style="font-weight:700; color:var(--success); font-size:1rem;"><?= htmlspecialchars($student['cgpa'] ?? '') ?></span></td>
                                    <td>📍 <?= htmlspecialchars($student['city'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <p>© 2026 StudentHub Portal • Built with ❤️ for Students • <a href="contact.php">Contact Support</a></p>
    </footer>

    <script src="Js/script.js"></script>
    <script>
        function filterPhpTable() {
            let search = document.getElementById("liveSearch").value.toLowerCase();
            let dept = document.getElementById("filterDept").value;
            let rows = document.querySelectorAll("#recordsTable tbody tr");

            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                let matchesSearch = text.includes(search);
                let matchesDept = (dept === "All") || text.includes(dept.toLowerCase());

                if (matchesSearch && matchesDept) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }
    </script>

</body>

</html>
