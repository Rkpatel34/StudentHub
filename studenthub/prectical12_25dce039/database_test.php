<?php
/**
 * database_test.php — StudentHub Database Connection Test
 * 
 * Practical 8: MySQL Schema Design, ER Model, and Database Connectivity
 * Course: ITUE203 — Web Development Frameworks
 * 
 * This page verifies that the PDO database connection works correctly.
 * It tests:
 * 1. Basic connection to MySQL
 * 2. Database selection
 * 3. Simple query execution
 * 4. Prepared statement with parameter binding
 * 5. Table existence verification
 * 
 * Access via browser: http://localhost/prectical7_25dce039/database_test.php
 * (Adjust path based on your XAMPP htdocs setup)
 */

require_once __DIR__ . '/db.php';

$testResults = [];
$overallSuccess = true;

// Helper to record test result
function addResult(array &$results, string $name, bool $passed, string $message = '', array $details = []) {
    $results[] = [
        'name'    => $name,
        'passed'  => $passed,
        'message' => $message,
        'details' => $details,
    ];
    return $passed;
}

// ============================================================
// TEST 1: PDO Connection Object Exists
// ============================================================
$passed = addResult($testResults, 'PDO Connection Object', isset($pdo) && $pdo instanceof PDO, 
    isset($pdo) ? 'PDO connection established successfully.' : 'PDO connection failed — check db.php error output.');
if (!$passed) $overallSuccess = false;

// ============================================================
// TEST 2: Simple Query (SELECT 1)
// ============================================================
if ($overallSuccess) {
    try {
        $stmt = $pdo->query('SELECT 1 AS test_value');
        $row = $stmt->fetch();
        $passed = addResult($testResults, 'Simple Query (SELECT 1)', 
            $row && $row['test_value'] == 1, 
            'Basic query executed and returned expected result.');
    } catch (PDOException $e) {
        $passed = addResult($testResults, 'Simple Query (SELECT 1)', false, 
            'Query failed: ' . $e->getMessage());
    }
    if (!$passed) $overallSuccess = false;
}

// ============================================================
// TEST 3: Database Name Verification
// ============================================================
if ($overallSuccess) {
    try {
        $stmt = $pdo->query('SELECT DATABASE() AS current_db');
        $row = $stmt->fetch();
        $expectedDb = 'studenthub_db';
        $passed = addResult($testResults, 'Correct Database Selected', 
            $row && $row['current_db'] === $expectedDb,
            "Connected to database: '{$row['current_db']}' (expected: '$expectedDb').",
            ['current_database' => $row['current_db'] ?? 'unknown']);
    } catch (PDOException $e) {
        $passed = addResult($testResults, 'Correct Database Selected', false, 
            'Failed to get database name: ' . $e->getMessage());
    }
    if (!$passed) $overallSuccess = false;
}

// ============================================================
// TEST 4: Table Existence Check
// ============================================================
$expectedTables = ['students', 'events', 'registrations'];
if ($overallSuccess) {
    try {
        $stmt = $pdo->query("SHOW TABLES");
        $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $missingTables = array_diff($expectedTables, $existingTables);
        $passed = addResult($testResults, 'Required Tables Exist', 
            empty($missingTables),
            empty($missingTables) 
                ? 'All required tables found: ' . implode(', ', $existingTables)
                : 'Missing tables: ' . implode(', ', $missingTables),
            ['existing_tables' => $existingTables, 'missing_tables' => array_values($missingTables)]);
    } catch (PDOException $e) {
        $passed = addResult($testResults, 'Required Tables Exist', false, 
            'SHOW TABLES failed: ' . $e->getMessage());
    }
    if (!$passed) $overallSuccess = false;
}

// ============================================================
// TEST 5: Prepared Statement with Parameter Binding (Security Demo)
// ============================================================
if ($overallSuccess) {
    try {
        // Test parameterized query — prevents SQL injection
        $testCourse = 'Web Development';
        $stmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM students WHERE course = ?');
        $stmt->execute([$testCourse]);
        $row = $stmt->fetch();
        $count = (int)$row['cnt'];
        
        $passed = addResult($testResults, 'Prepared Statement (Parameter Binding)', 
            $count >= 0, // Query executed without error
            "Prepared statement executed safely. Found $count student(s) in course '$testCourse'.",
            ['test_course' => $testCourse, 'result_count' => $count]);
        
        // Demonstrate that the same query with different parameter works
        $testCourse2 = 'Data Science';
        $stmt->execute([$testCourse2]);
        $row2 = $stmt->fetch();
        $count2 = (int)$row2['cnt'];
        $details = [
            'first_query'  => ['course' => $testCourse,  'count' => $count],
            'second_query' => ['course' => $testCourse2, 'count' => $count2],
        ];
        $testResults[count($testResults)-1]['details'] = $details;
        
    } catch (PDOException $e) {
        $passed = addResult($testResults, 'Prepared Statement (Parameter Binding)', false, 
            'Prepared statement failed: ' . $e->getMessage());
    }
    if (!$passed) $overallSuccess = false;
}

// ============================================================
// TEST 6: Foreign Key Relationship Verification
// ============================================================
if ($overallSuccess) {
    try {
        // Join students -> registrations -> events
        $sql = "
            SELECT s.full_name, e.title, r.status
            FROM registrations r
            JOIN students s ON r.student_id = s.student_id
            JOIN events e ON r.event_id = e.event_id
            ORDER BY s.full_name, e.event_date
            LIMIT 5
        ";
        $rows = db_select($sql);
        $passed = addResult($testResults, 'Foreign Key Relationships Work', 
            is_array($rows) && count($rows) > 0,
            'JOIN query across all three tables succeeded. ' . count($rows) . ' registration record(s) returned.',
            ['sample_rows' => $rows]);
    } catch (PDOException $e) {
        $passed = addResult($testResults, 'Foreign Key Relationships Work', false, 
            'JOIN query failed: ' . $e->getMessage());
    }
    if (!$passed) $overallSuccess = false;
}

// ============================================================
// TEST 7: Character Set Verification
// ============================================================
if ($overallSuccess) {
    try {
        $stmt = $pdo->query('SHOW VARIABLES LIKE "character_set_database"');
        $row = $stmt->fetch();
        $charset = $row['Value'] ?? 'unknown';
        $passed = addResult($testResults, 'Character Set (utf8mb4)', 
            strtolower($charset) === 'utf8mb4',
            "Database character set: $charset",
            ['character_set' => $charset]);
    } catch (PDOException $e) {
        $passed = addResult($testResults, 'Character Set (utf8mb4)', false, 
            'Character set check failed: ' . $e->getMessage());
    }
    if (!$passed) $overallSuccess = false;
}

// ============================================================
// Output HTML Report
// ============================================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Test - StudentHub Practical 8</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .test-container { max-width: 900px; margin: 0 auto; padding: 20px; }
        .test-header { text-align: center; margin-bottom: 30px; }
        .test-header h1 { color: #1a3c5e; margin-bottom: 10px; }
        .test-header p { color: #666; }
        .status-badge { display: inline-block; padding: 8px 16px; border-radius: 20px; font-weight: bold; font-size: 14px; }
        .status-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .status-fail { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .test-card { background: white; border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 15px; overflow: hidden; }
        .test-card-header { padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .test-card-header.pass { background: #f8fff9; border-bottom: 1px solid #c3e6cb; }
        .test-card-header.fail { background: #fff8f8; border-bottom: 1px solid #f5c6cb; }
        .test-name { font-weight: 600; font-size: 16px; color: #333; }
        .test-message { padding: 0 20px 15px; color: #555; line-height: 1.5; }
        .test-details { background: #f8f9fa; border-top: 1px solid #dee2e6; padding: 15px 20px; font-size: 13px; color: #666; overflow-x: auto; }
        .test-details pre { margin: 0; white-space: pre-wrap; word-wrap: break-word; }
        .summary { text-align: center; padding: 20px; margin-top: 20px; border-radius: 8px; }
        .summary.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .summary.fail { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .nav-link { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #1a3c5e; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; }
        .nav-link:hover { background: #0d2840; }
        .code { font-family: 'Consolas', 'Monospace'; background: #f1f1f1; padding: 2px 6px; border-radius: 3px; }
    </style>
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
                <li><a href="register.php">📋 Register (PHP)</a></li>
                <li><a href="contact.php">💬 Contact (PHP)</a></li>
            </ul>
        </div>
    </nav>

    <main class="main-wrapper">
        <div class="test-container">
            <div class="test-header">
                <h1>🔌 Database Connection Test</h1>
                <p>Practical 8 — MySQL Schema Design, ER Model, and Database Connectivity</p>
                <p style="font-size: 14px; color: #888;">Course: ITUE203 — Web Development Frameworks</p>
            </div>

            <div class="summary <?= $overallSuccess ? 'success' : 'fail' ?>">
                <h2><?= $overallSuccess ? '✅ ALL TESTS PASSED' : '❌ SOME TESTS FAILED' ?></h2>
                <p><?= $overallSuccess 
                    ? 'Database connection is working correctly. You can proceed with Practical 8 exercises.' 
                    : 'Please fix the failing tests above. Check XAMPP MySQL status and database import.' ?></p>
            </div>

            <?php foreach ($testResults as $test): ?>
                <div class="test-card">
                    <div class="test-card-header <?= $test['passed'] ? 'pass' : 'fail' ?>">
                        <span class="test-name"><?= htmlspecialchars($test['name']) ?></span>
                        <span class="status-badge <?= $test['passed'] ? 'status-success' : 'status-fail' ?>">
                            <?= $test['passed'] ? 'PASS' : 'FAIL' ?>
                        </span>
                    </div>
                    <div class="test-message">
                        <?= htmlspecialchars($test['message']) ?>
                    </div>
                    <?php if (!empty($test['details'])): ?>
                        <div class="test-details">
                            <strong>Details:</strong>
                            <pre><?= htmlspecialchars(json_encode($test['details'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <div style="text-align: center; margin-top: 30px;">
                <a href="index.html" class="nav-link">← Back to StudentHub Home</a>
            </div>

            <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px;">
                <h3 style="margin-top: 0; color: #1a3c5e;">📋 Prepared Statements — Why They Matter</h3>
                <p><strong>What is a prepared statement?</strong> A prepared statement is a SQL query template with placeholders (<code>?</code> or <code>:name</code>) that is sent to the database server <em>before</em> any user data is added.</p>
                <p><strong>How it prevents SQL injection:</strong></p>
                <ol style="text-align: left; max-width: 700px; margin: 10px auto; padding-left: 20px;">
                    <li>The SQL structure is parsed and compiled by the database <strong>first</strong>.</li>
                    <li>User input is sent <strong>separately</strong> as parameters, never mixed into the SQL string.</li>
                    <li>The database treats parameters as <strong>literal data only</strong> — never as executable SQL code.</li>
                    <li>Even if a user enters <code>' OR '1'='1</code>, it is searched for as text, not executed as logic.</li>
                </ol>
                <p><strong>Example from this test:</strong></p>
                <pre style="background: #f1f1f1; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>$stmt = $pdo->prepare('SELECT * FROM students WHERE course = ?');
$stmt->execute([$userInputCourse]); // Safe — $userInputCourse never touches SQL parsing</code></pre>
                <p style="margin-bottom: 0;"><strong>Never do this (unsafe concatenation):</strong></p>
                <pre style="background: #f1f1f1; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>$sql = "SELECT * FROM students WHERE course = '" . $_POST['course'] . "'"; // DANGEROUS!</code></pre>
            </div>
        </div>
    </main>

    <footer class="site-footer">
        <p>© 2026 StudentHub Portal • Practical 8 Database Test • <a href="contact.php">Contact Support</a></p>
    </footer>

    <script src="Js/script.js"></script>
</body>
</html>