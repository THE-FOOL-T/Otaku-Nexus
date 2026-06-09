<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); } 

// --- AUTOMATED COOKIE-BASED LOGIN ENGINE ---
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_nexus'])) {
    require_once 'config/db.php';
    $token = $_COOKIE['remember_nexus'];
    
    // Scan database for a matching token
    $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ? AND status = 'approved'");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Automatically reconstitute user session state
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
    } else {
        // Trash invalid or expired cookies
        setcookie('remember_nexus', '', time() - 3600, '/');
    }
}

// Normalize role for robust checking
$nav_role = isset($_SESSION['role']) ? strtolower(str_replace('_', ' ', $_SESSION['role'])) : '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Otaku Nexus</title>
    <link rel="stylesheet" href="animestyle.css">
</head>
<body>
    <div class="bg-slider" id="bg-slider"></div>
    <div class="bg-overlay"></div>

    <header id="nav-bar">
      <div class="logo-container">
        <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 2v6l-2-2-4 4 2 2H2v4h6l-2 2 4 4 2-2v6h4v-6l2 2 4-4-2-2h6v-4h-6l2-2-4-4-2 2V2h-4z"></path>
          <circle cx="12" cy="12" r="2"></circle>
        </svg>
        <div class="logo-text">OTAKU<span class="neon-text">NEXUS</span></div>
      </div>
      <nav>
        <ul class="nav-links" style="align-items: center;">
          <li><a href="index.php#home">Home</a></li>
          <li><a href="index.php#about">About</a></li>
          <li><a href="index.php#achievements">Achievements</a></li>
          <li><a href="index.php#events">Events</a></li>
          <li><a href="index.php#team">Team</a></li>
          
          <?php if(isset($_SESSION['user_id'])): ?>
            <li><a href="discussions.php" class="neon-text">Discussions</a></li>
            
            <?php if(in_array($nav_role, ['admin', 'moderator', 'event coordinator'])): ?>
              <li><a href="admin.php" class="nav-active">Dashboard</a></li>
            <?php endif; ?>
            
            <!-- UPDATED: Profile Button with removed blue color overrides -->
            <li>
              <a href="profile.php?id=<?= $_SESSION['user_id'] ?>" class="nav-btn" style="white-space: nowrap; display: inline-flex; align-items: center; justify-content: center; width: auto; min-width: max-content;">
                Profile
              </a>
            </li>
            
            <!-- Log Out Button -->
            <li>
              <a href="logout.php" class="nav-btn" style="white-space: nowrap; display: inline-flex; align-items: center; justify-content: center; width: auto; min-width: max-content;">
                Log Out
              </a>
            </li>
          <?php else: ?>
            <li><a href="login.php" class="nav-btn" style="white-space: nowrap; display: inline-flex; align-items: center; justify-content: center; width: auto; min-width: max-content;">Join Guild</a></li>
          <?php endif; ?>
        </ul>
        <div class="burger" id="burger-mnu">
          <div class="line1"></div>
          <div class="line2"></div>
          <div class="line3"></div>
        </div>
      </nav>
    </header>