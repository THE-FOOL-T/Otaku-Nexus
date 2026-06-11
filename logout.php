<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require 'config/db.php';

// Clear token within the cluster DB record before destroying session
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
}

// Flush all running server-side sessions
session_unset();
session_destroy();

// Completely destroy cookie lifetime mapping
if (isset($_COOKIE['remember_nexus'])) {
    setcookie('remember_nexus', '', time() - 3600, '/');
}

// Send back to portal entry point
header("Location: index.php");
exit;
?>