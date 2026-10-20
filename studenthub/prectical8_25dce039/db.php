<?php
/**
 * db.php — StudentHub Database Connection (PDO)
 * 
 * Practical 8: MySQL Schema Design, ER Model, and Database Connectivity
 * Course: ITUE203 — Web Development Frameworks
 * 
 * This file provides a reusable PDO connection to the MySQL database.
 * Include this file in other PHP scripts using:
 *     require_once __DIR__ . '/db.php';
 * Then use the $pdo variable for database operations.
 * 
 * Configuration is set for standard XAMPP default MySQL settings.
 * For production, use environment variables or a separate config file.
 */

// ============================================================
// Database Configuration — Adjust for your local XAMPP setup
// ============================================================
$dbConfig = [
    'host'       => '127.0.0.1',      // XAMPP MySQL host
    'port'       => 3306,             // Default MySQL port
    'dbname'     => 'studenthub_db',  // Database name from Practical 8
    'username'   => 'root',           // XAMPP default username
    'password'   => '',               // XAMPP default password is EMPTY
    'charset'    => 'utf8mb4',        // Full Unicode support
];

// ============================================================
// PDO Connection Options
// ============================================================
$pdoOptions = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // Return associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                    // Use native prepared statements
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$dbConfig['charset']} COLLATE utf8mb4_unicode_ci",
];

// ============================================================
// Build DSN (Data Source Name)
// ============================================================
$dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";

// ============================================================
// Create PDO Connection with Error Handling
// ============================================================
try {
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $pdoOptions);
    // Connection successful — $pdo is ready to use
} catch (PDOException $e) {
    // ========================================================
    // SAFE ERROR HANDLING — No sensitive details exposed
    // ========================================================
    
    // Log the detailed error for debugging (server-side only)
    error_log("StudentHub DB Connection Failed: " . $e->getMessage());
    
    // User-friendly message — no credentials, stack traces, or server paths
    $errorMessage = "Unable to connect to the database. Please check that MySQL is running in XAMPP and the database 'studenthub_db' exists.";
    
    // If this script is accessed directly (not included), show HTML error page
    if (php_sapi_name() !== 'cli' && !headers_sent()) {
        http_response_code(503); // Service Unavailable
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Database Connection Error - StudentHub</title>
            <style>
                body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 40px 20px; }
                .error-box { max-width: 600px; margin: 0 auto; background: white; border: 2px solid #dc3545; border-radius: 8px; padding: 30px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .error-icon { font-size: 48px; margin-bottom: 15px; }
                h1 { color: #dc3545; margin: 0 0 15px; font-size: 24px; }
                p { color: #333; line-height: 1.6; margin: 10px 0; }
                .details { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 15px; margin: 20px 0; font-size: 14px; color: #666; text-align: left; }
                .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; }
                .btn:hover { background: #0056b3; }
            </style>
        </head>
        <body>
            <div class="error-box">
                <div class="error-icon">🔌</div>
                <h1>Database Connection Failed</h1>
                <p><?= htmlspecialchars($errorMessage) ?></p>
                <div class="details">
                    <strong>Troubleshooting Steps:</strong>
                    <ol style="text-align:left; padding-left:20px;">
                        <li>Open <strong>XAMPP Control Panel</strong> and ensure <strong>MySQL</strong> is <strong>Running</strong> (green).</li>
                        <li>Open <code>http://localhost/phpmyadmin/</code> and verify database <code>studenthub_db</code> exists.</li>
                        <li>If database is missing, import <code>database/studenthub_db.sql</code> via phpMyAdmin.</li>
                        <li>Check <code>db.php</code> credentials match your XAMPP MySQL settings (default: root / empty password).</li>
                    </ol>
                </div>
                <a href="javascript:history.back()" class="btn">← Go Back</a>
            </div>
        </body>
        </html>
        <?php
    }
    
    // Stop execution — calling scripts should not continue without DB
    exit;
}

// ============================================================
// Helper Function: Run a Prepared Statement Safely
// ============================================================
/**
 * Execute a prepared SELECT query and return all results.
 * 
 * @param string $sql    SQL with placeholders (e.g., "SELECT * FROM students WHERE course = ?")
 * @param array  $params Values to bind to placeholders
 * @return array         Array of result rows (associative arrays)
 */
function db_select(string $sql, array $params = []): array {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Execute a prepared INSERT/UPDATE/DELETE query.
 * 
 * @param string $sql    SQL with placeholders
 * @param array  $params Values to bind to placeholders
 * @return int           Number of affected rows
 */
function db_execute(string $sql, array $params = []): int {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

/**
 * Insert a record and return the new auto-increment ID.
 * 
 * @param string $sql    INSERT SQL with placeholders
 * @param array  $params Values to bind
 * @return int|false     Last insert ID or false on failure
 */
function db_insert(string $sql, array $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute($params);
    return $success ? $pdo->lastInsertId() : false;
}

// ============================================================
// Example Usage (uncomment to test):
// ============================================================
// $students = db_select("SELECT student_id, full_name, email, course FROM students WHERE year = ?", [2]);
// foreach ($students as $s) { echo $s['full_name'] . " - " . $s['course'] . "<br>"; }

// ============================================================
// End of db.php
// ============================================================