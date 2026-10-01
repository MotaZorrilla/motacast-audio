<?php
$sqlitePath = '/home/motazorrilla/apps/audiolibros/database/database.sqlite';
if (!file_exists($sqlitePath)) {
    $sqlitePath = '/app/database/database.sqlite';
}
$pdo = new PDO('sqlite:' . $sqlitePath);

echo "=== USERS ===\n";
foreach ($pdo->query('SELECT id, name, email, created_at FROM users') as $row) {
    echo "ID " . $row['id'] . ": " . $row['name'] . " (" . $row['email'] . ")\n";
}

echo "\n=== BOOKS ===\n";
foreach ($pdo->query('SELECT id, title, total_chapters, processed_chapters, status, created_at FROM books') as $row) {
    echo "ID " . $row['id'] . ": " . $row['title'] . " - " . $row['status'] . " - Ch: " . $row['processed_chapters'] . "/" . $row['total_chapters'] . "\n";
}

echo "\n=== CHAPTERS COUNT ===\n";
echo $pdo->query('SELECT count(*) FROM chapters')->fetchColumn() . " chapters\n";

echo "\n=== MIGRATIONS ===\n";
foreach ($pdo->query('SELECT migration, batch FROM migrations') as $row) {
    echo "Migration: " . $row['migration'] . " (Batch " . $row['batch'] . ")\n";
}
