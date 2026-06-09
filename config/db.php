<?php
// config/db.php
$host = 'localhost';
$dbname = 'otaku_nexus';
$username = 'root';
$password = ''; // Leave blank by default for XAMPP environments

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database engine connectivity loss: " . $e->getMessage());
}
?>