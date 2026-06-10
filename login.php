<?php
include 'includes/header.php';
require 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['password'])) {
        if ($user['status'] === 'pending') {
            $error = "Your entry application is still pending executive review.";
        } elseif ($user['status'] === 'rejected') {
            $error = "Your entry application was declined by the committee.";
        } else {
            // Reconstitute session state
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['rank'] = $user['rank_title'];
            
            // COOKIE SETTING BLOCK
            if (isset($_POST['remember'])) {
                // Generate an unguessable cryptographic hex token
                $token = bin2hex(random_bytes(32));
                
                // Save token hash to database mapping structure
                $cookie_stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                $cookie_stmt->execute([$token, $user['id']]);
                
                // Set cookie expiration timestamp to 1 Month (30 days = 2,592,000 seconds)
                $cookie_expiration = time() + (30 * 24 * 60 * 60);
                
                // Set secure, HTTP-only cookie parameters
                setcookie('remember_nexus', $token, $cookie_expiration, "/", "", false, true);
            }

            header("Location: index.php");
            exit;
        }
    } else {
        $error = "Invalid credential combinations.";
    }
}
?>
<section class="container" style="min-height: 85vh; display: flex; justify-content: center; align-items: center; padding-top: 8rem;">
    <div class="glass-card" style="padding: 3rem; width: 100%; max-width: 420px; text-align: center;">
        <h2 class="neon-text" style="margin-bottom: 2rem;">Gate Check</h2>
        <?php if(isset($error)) echo "<p style='color:var(--neon-primary); margin-bottom:1rem;'>$error</p>"; ?>
        <form method="POST" style="display:flex; flex-direction:column; gap:1.2rem;">
            <input type="email" name="email" placeholder="Student Email" required style="width:100%; padding:12px; background:rgba(0,0,0,0.6); color:white; border:1px solid var(--surface-border); border-radius:6px;">
            <input type="password" name="password" placeholder="Password" required style="width:100%; padding:12px; background:rgba(0,0,0,0.6); color:white; border:1px solid var(--surface-border); border-radius:6px;">
            <label style="color: #a0aec0; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem; cursor: pointer; font-size: 0.9rem;">
            <input type="checkbox" name="remember" style="accent-color: var(--neon-primary); cursor: pointer;"> 
                Remember me for a week
            </label>
            <button type="submit" class="btn btn-primary" style="border:none; cursor:pointer;">Log In</button>
        </form>
        <p style="margin-top: 1.5rem; color: var(--text-muted);">New student? <a href="register.php" class="neon-text" style="text-decoration:none;">Register Here</a></p>
    </div>
</section>
<?php include 'includes/footer.php'; ?>