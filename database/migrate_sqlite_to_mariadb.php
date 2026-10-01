<?php

/**
 * MotaCastAudio - SQLite to MariaDB Data Migration Script
 * Preserves all users, books, chapters, and media references.
 */

$sqliteFile = getenv('SQLITE_PATH') ?: __DIR__ . '/database.sqlite';
if (!file_exists($sqliteFile)) {
    $sqliteFile = '/app/database/database.sqlite';
}

if (!file_exists($sqliteFile)) {
    fwrite(STDERR, "ERROR: SQLite database file not found at $sqliteFile\n");
    exit(1);
}

$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_DATABASE') ?: 'audiolibros';
$dbUser = getenv('DB_USERNAME') ?: 'audiolibros';
$dbPass = getenv('DB_PASSWORD') ?: '';

echo "Connecting to SQLite: $sqliteFile ...\n";
$sqlite = new PDO("sqlite:$sqliteFile");
$sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Connecting to MariaDB: $dbHost:$dbPort (db: $dbName, user: $dbUser) ...\n";
$dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4";
$mariadb = new PDO($dsn, $dbUser, $dbPass);
$mariadb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Connection successful!\n\n";

$tablesToMigrate = ['users', 'books', 'chapters', 'failed_jobs'];

$mariadb->exec("SET FOREIGN_KEY_CHECKS = 0;");

foreach ($tablesToMigrate as $table) {
    echo "--- Migrating table: $table ---\n";
    
    // Check if table exists in SQLite
    $checkSqlite = $sqlite->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$table'")->fetch();
    if (!$checkSqlite) {
        echo "Table $table does not exist in SQLite, skipping.\n";
        continue;
    }

    // Get rows from SQLite
    $stmt = $sqlite->query("SELECT * FROM \"$table\"");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count = count($rows);
    echo "Found $count records in SQLite.\n";

    if ($count === 0) {
        continue;
    }

    // Truncate destination table in MariaDB to avoid duplicates
    $mariadb->exec("TRUNCATE TABLE `$table`");

    // Prepare insert
    $columns = array_keys($rows[0]);
    $colList = implode('`, `', $columns);
    $paramList = implode(', ', array_fill(0, count($columns), '?'));
    $insertSql = "INSERT INTO `$table` (`$colList`) VALUES ($paramList)";
    $insertStmt = $mariadb->prepare($insertSql);

    $inserted = 0;
    foreach ($rows as $row) {
        $insertStmt->execute(array_values($row));
        $inserted++;
    }
    echo "Successfully inserted $inserted records into MariaDB `$table`.\n";

    // Update Auto-Increment to max(id) + 1
    if (in_array('id', $columns)) {
        $maxId = $mariadb->query("SELECT COALESCE(MAX(id), 0) FROM `$table`")->fetchColumn();
        $nextAuto = $maxId + 1;
        $mariadb->exec("ALTER TABLE `$table` AUTO_INCREMENT = $nextAuto");
        echo "Reset `$table` AUTO_INCREMENT to $nextAuto.\n";
    }
    echo "\n";
}

$mariadb->exec("SET FOREIGN_KEY_CHECKS = 1;");

echo "========================================\n";
echo "VERIFICATION SUMMARY:\n";
echo "========================================\n";
foreach ($tablesToMigrate as $table) {
    $sqCount = $sqlite->query("SELECT count(*) FROM \"$table\"")->fetchColumn();
    $maCount = $mariadb->query("SELECT count(*) FROM `$table`")->fetchColumn();
    $status = ($sqCount == $maCount) ? "MATCH [OK]" : "MISMATCH [WARNING]";
    echo sprintf("%-15s | SQLite: %4d | MariaDB: %4d | %s\n", $table, $sqCount, $maCount, $status);
}

echo "\nMigration finished successfully!\n";
