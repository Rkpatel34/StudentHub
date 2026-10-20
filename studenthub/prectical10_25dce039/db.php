<?php
/**
 * db.php — StudentHub Database Connection (PDO + MySQLi)
 * 
 * Practical 8: MySQL Schema Design, ER Model, and Database Connectivity (PDO)
 * Practical 9: Secure User Registration with MySQLi Prepared Statements
 * Course: ITUE203 — Web Development Frameworks
 * 
 * This file provides BOTH PDO and MySQLi connections using the SAME configuration.
 * Include this file in other PHP scripts using:
 *     require_once __DIR__ . '/db.php';
 * Then use $pdo (PDO) or get_mysqli_connection() (MySQLi) for database operations.
 * 
 * Configuration is set for standard XAMPP default MySQL settings.
 * For production, use environment variables or a separate config file.
 */

// ============================================================
// Database Configuration — Single source of truth for both PDO and MySQLi
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
// PDO Connection (Practical 8)
// ============================================================
$pdoOptions = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$dbConfig['charset']} COLLATE utf8mb4_unicode_ci",
];

$dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";

try {
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $pdoOptions);
} catch (PDOException $e) {
    error_log("StudentHub PDO Connection Failed: " . $e->getMessage());
    $errorMessage = "Unable to connect to the database (PDO). Please check that MySQL is running in XAMPP and the database 'studenthub_db' exists.";
    if (php_sapi_name() !== 'cli' && !headers_sent()) {
        http_response_code(503);
        ?>
        <!DOCTYPE html>
        <html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Connection Error - StudentHub</title>
        <style>body{font-family:Arial,sans-serif;background:#f5f5f5;margin:0;padding:40px 20px}.error-box{max-width:600px;margin:0 auto;background:white;border:2px solid #dc3545;border-radius:8px;padding:30px;text-align:center;box-shadow:0 2px 10px rgba(0,0,0,0.1)}.error-icon{font-size:48px;margin-bottom:15px}h1{color:#dc3545;margin:0 0 15px;font-size:24px}p{color:#333;line-height:1.6;margin:10px 0}.details{background:#f8f9fa;border:1px solid #dee2e6;border-radius:4px;padding:15px;margin:20px 0;font-size:14px;color:#666;text-align:left}.btn{display:inline-block;padding:10px 20px;background:#007bff;color:white;text-decoration:none;border-radius:4px;font-weight:bold}.btn:hover{background:#0056b3}</style></head>
        <body><div class="error-box"><div class="error-icon">🔌</div><h1>Database Connection Failed</h1><p><?= htmlspecialchars($errorMessage) ?></p><div class="details"><strong>Troubleshooting:</strong><ol style="text-align:left;padding-left:20px"><li>Open <strong>XAMPP Control Panel</strong> and ensure <strong>MySQL</strong> is <strong>Running</strong> (green).</li><li>Open <code>http://localhost/phpmyadmin/</code> and verify database <code>studenthub_db</code> exists.</li><li>If missing, import <code>database/studenthub_db.sql</code> via phpMyAdmin.</li><li>Check <code>db.php</code> credentials (default: root / empty password).</li></ol></div><a href="javascript:history.back()" class="btn">← Go Back</a></div></body></html>
        <?php
    }
    exit;
}

// ============================================================
// MySQLi Connection (Practical 9) — Uses SAME $dbConfig
// ============================================================
/**
 * Get a MySQLi connection instance.
 * Uses the same $dbConfig as PDO — no duplicate configuration.
 * Call this function when you need MySQLi (e.g., Practical 9 registration).
 * 
 * @return mysqli Connected MySQLi instance
 * @throws Exception If connection fails
 */
function get_mysqli_connection(): mysqli {
    global $dbConfig;
    
    $mysqli = new mysqli(
        $dbConfig['host'],
        $dbConfig['username'],
        $dbConfig['password'],
        $dbConfig['dbname'],
        $dbConfig['port']
    );
    
    if ($mysqli->connect_errno) {
        error_log("StudentHub MySQLi Connection Failed: " . $mysqli->connect_error);
        throw new Exception("Unable to connect to the database (MySQLi). Please check that MySQL is running in XAMPP and the database 'studenthub_db' exists.");
    }
    
    // Set charset
    if (!$mysqli->set_charset($dbConfig['charset'])) {
        error_log("MySQLi charset setting failed: " . $mysqli->error);
    }
    
    // Enable strict error reporting for prepared statements
    $mysqli->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;
    
    return $mysqli;
}

/**
 * Close a MySQLi connection safely.
 * 
 * @param mysqli|null $mysqli MySQLi instance to close
 */
function close_mysqli_connection(?mysqli $mysqli): void {
    if ($mysqli instanceof mysqli && !$mysqli->connect_errno) {
        $mysqli->close();
    }
}

// ============================================================
// PDO Helper Functions (Practical 8)
// ============================================================
function db_select(string $sql, array $params = []): array {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function db_execute(string $sql, array $params = []): int {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

function db_insert(string $sql, array $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute($params);
    return $success ? $pdo->lastInsertId() : false;
}

// ============================================================
// MySQLi Helper Functions (Practical 9) — Prefixed to avoid conflicts
// ============================================================

/**
 * Execute a MySQLi prepared SELECT and return all results as associative arrays.
 * 
 * @param string $sql    SQL with ? placeholders
 * @param array  $params Values to bind (referenced for bind_param)
 * @return array         Array of result rows
 * @throws Exception     On SQL error
 */
function db_mysqli_select(string $sql, array $params = []): array {
    $mysqli = get_mysqli_connection();
    try {
        $stmt = $mysqli->prepare($sql);
        if ($params) {
            $types = str_repeat('s', count($params)); // All strings for simplicity
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows;
    } finally {
        close_mysqli_connection($mysqli);
    }
}

/**
 * Execute a MySQLi prepared INSERT/UPDATE/DELETE.
 * 
 * @param string $sql    SQL with ? placeholders
 * @param array  $params Values to bind
 * @return int           Number of affected rows
 * @throws Exception     On SQL error
 */
function db_mysqli_execute(string $sql, array $params = []): int {
    $mysqli = get_mysqli_connection();
    try {
        $stmt = $mysqli->prepare($sql);
        if ($params) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    } finally {
        close_mysqli_connection($mysqli);
    }
}

/**
 * Execute a MySQLi prepared INSERT and return the new auto-increment ID.
 * 
 * @param string $sql    INSERT SQL with ? placeholders
 * @param array  $params Values to bind
 * @return int|false     Last insert ID or false on failure
 * @throws Exception     On SQL error
 */
function db_mysqli_insert(string $sql, array $params = []) {
    $mysqli = get_mysqli_connection();
    try {
        $stmt = $mysqli->prepare($sql);
        if ($params) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $insertId = $stmt->insert_id;
        $stmt->close();
        return $insertId ?: false;
    } finally {
        close_mysqli_connection($mysqli);
    }
}

/**
 * Check if a value exists in a column (for duplicate checking).
 * Uses MySQLi prepared statement.
 * 
 * @param string $table   Table name
 * @param string $column  Column name
 * @param mixed  $value   Value to check
 * @return bool           True if exists, false otherwise
 * @throws Exception      On SQL error
 */
function db_mysqli_exists(string $table, string $column, $value): bool {
    $sql = "SELECT 1 FROM `$table` WHERE `$column` = ? LIMIT 1";
    $rows = db_mysqli_select($sql, [$value]);
    return !empty($rows);
}

// ============================================================
// End of db.php
// ============================================================