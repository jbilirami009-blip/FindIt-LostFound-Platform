<?php
$host = 'localhost';
$dbname = 'findit';
$user = 'root';
$password = 'root'; // MAMP default

try {
    $pdo = new PDO(
        "mysql:host=$host;port=8889;dbname=$dbname;charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
