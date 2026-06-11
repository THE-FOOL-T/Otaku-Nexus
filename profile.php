<?php
include 'includes/header.php';
require 'config/db.php';

// Route checks: Must be logged in to view profiles at all
if (!isset($_SESSION['user_id'])) {
    die("<section class='container' style='padding-top:12rem;'><h2 class='neon-text'>Access Denied: Authentication required.</h2><br><a href='login.php' class='btn btn-primary'>Login Here</a></section>");
}

// Check who we are looking at
$viewer_id = $_SESSION['user_id'];
$profile_id = isset($_GET['id']) ? intval($_GET['id']) : $viewer_id;
$is_own_profile = ($viewer_id === $profile_id);

// Profile processing module
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_own_profile) {
    $first_name = trim(htmlspecialchars($_POST['first_name']));
    $last_name = trim(htmlspecialchars($_POST['last_name']));
    $department = trim(htmlspecialchars($_POST['department']));
    $batch = trim(htmlspecialchars($_POST['batch']));
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $join_reason = trim(htmlspecialchars($_POST['join_reason']));
    
    // Dynamic avatar processing hook
    $db_pic_path = $_POST['current_profile_pic'];
    if (!empty($_FILES["profile_image"]["name"])) {
        $target_dir = "uploads/";
        $file_name = time() . '_avatar_' . basename($_FILES["profile_image"]["name"]);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            $db_pic_path = $target_file;
        }
    }

    $up_stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, department = ?, batch = ?, dob = ?, gender = ?, join_reason = ?, profile_pic = ? WHERE id = ?");
    $up_stmt->execute([$first_name, $last_name, $department, $batch, $dob, $gender, $join_reason, $db_pic_path, $viewer_id]);
    
    header("Location: profile.php?id=" . $viewer_id);
    exit;
}

// Fetch Profile Data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$profile_id]);
$user = $stmt->fetch();

if (!$user) {
    die("<section class='container' style='padding-top:12rem;'><h2 class='neon-text'>Error: User profile signature not found.</h2></section>");
}
?>

<section class="container" style="padding-top: 10rem; min-height: 100vh; padding-bottom: 4rem;">
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; align-items: start;">
        
        <div class="glass-card" style="padding: 2.5rem; text-align: center; display: flex; flex-direction: column; align-items: center;">
            <div style="width: 150px; height: 150px; border-radius: 50%; overflow: hidden; border: 3px solid var(--neon-secondary); margin-bottom: 1.5rem; background: #000;">
                <?php 
                $avatar = (!empty($user['profile_pic']) && file_exists($user['profile_pic'])) ? $user['profile_pic'] : 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=300&q=80'; 
                ?>
                <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar" style="width:100%; height:100%; object-fit:cover;">
            </div>

            <h2 style="font-size: 1.6rem; margin-bottom: 0.2rem;"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h2>
            <p style="color: var(--neon-secondary); font-size: 0.9rem; font-weight: bold; margin-bottom: 1rem;">@<?= htmlspecialchars($user['username']) ?></p>
            
            <div style="background: var(--surface-color); border: 1px solid var(--surface-border); padding: 6px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; color: #fff; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1.5rem;">
                Rank: <span style="color: var(--neon-primary);"><?= htmlspecialchars($user['rank_title'] ?? 'Member') ?></span>
            </div>

            <div style="text-align: left; width: 100%; font-size: 0.9rem; color: var(--text-muted); display: flex; flex-direction: column; gap: 0.5rem; border-top: 1px solid var(--surface-border); padding-top: 1.5rem;">
                <div><strong>ID:</strong> <span style="color:#fff;"><?= htmlspecialchars($user['student_id'] ?? 'N/A') ?></span></div>
                <div><strong>Dept:</strong> <span style="color:#fff;"><?= htmlspecialchars($user['department'] ?? 'N/A') ?></span></div>
                <div><strong>Batch:</strong> <span style="color:#fff;"><?= htmlspecialchars($user['batch'] ?? 'N/A') ?></span></div>
                <div><strong>Gender:</strong> <span style="color:#fff;"><?= htmlspecialchars($user['gender'] ?? 'N/A') ?></span></div>
            </div>
        </div>

        <div class="glass-card" style="padding: 3rem;">
            <?php if ($is_own_profile): ?>
                <h3 class="neon-text" style="margin-bottom: 2rem;">Manage Profile Metrics</h3>
                
                <form method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <input type="hidden" name="current_profile_pic" value="<?= htmlspecialchars($user['profile_pic']) ?>">
                    
                    <div>
                        <label style="font-size:0.85rem; color:var(--text-muted); display:block; margin-bottom:6px;">Update Avatar Image</label>
                        <input type="file" name="profile_image" accept="image/*" style="color:white; border:1px solid var(--surface-border); padding:8px; width:100%; border-radius:4px;">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label style="font-size:0.85rem; color:var(--text-muted); display:block; margin-bottom:4px;">First Name</label>
                            <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required style="width:100%; padding:10px; background:rgba(0,0,0,0.4); color:white; border:1px solid var(--surface-border); border-radius:4px;">
                        </div>
                        <div>
                            <label style="font-size:0.85rem; color:var(--text-muted); display:block; margin-bottom:4px;">Last Name</label>
                            <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required style="width:100%; padding:10px; background:rgba(0,0,0,0.4); color:white; border:1px solid var(--surface-border); border-radius:4px;">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label style="font-size:0.85rem; color:var(--text-muted); display:block; margin-bottom:4px;">Department</label>
                            <input type="text" name="department" value="<?= htmlspecialchars($user['department'] ?? '') ?>" required style="width:100%; padding:10px; background:rgba(0,0,0,0.4); color:white; border:1px solid var(--surface-border); border-radius:4px;">
                        </div>
                        <div>
                            <label style="font-size:0.85rem; color:var(--text-muted); display:block; margin-bottom:4px;">Batch</label>
                            <input type="text" name="batch" value="<?= htmlspecialchars($user['batch'] ?? '') ?>" required style="width:100%; padding:10px; background:rgba(0,0,0,0.4); color:white; border:1px solid var(--surface-border); border-radius:4px;">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label style="font-size:0.85rem; color:var(--text-muted); display:block; margin-bottom:4px;">Date of Birth</label>
                            <input type="date" name="dob" value="<?= htmlspecialchars($user['dob'] ?? '') ?>" required style="width:100%; padding:10px; background:rgba(0,0,0,0.4); color:white; border:1px solid var(--surface-border); border-radius:4px;">
                        </div>
                        <div>
                            <label style="font-size:0.85rem; color:var(--text-muted); display:block; margin-bottom:4px;">Gender Status</label>
                            <select name="gender" style="width:100%; padding:10px; background:rgba(0,0,0,0.4); color:white; border:1px solid var(--surface-border); border-radius:4px;">
                                <option value="Male" <?= ($user['gender'] === 'Male') ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= ($user['gender'] === 'Female') ? 'selected' : '' ?>>Female</option>
                                <option value="Other" <?= ($user['gender'] === 'Other') ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label style="font-size:0.85rem; color:var(--text-muted); display:block; margin-bottom:4px;">Club Statement / Motivations</label>
                        <textarea name="join_reason" rows="4" style="width:100%; padding:10px; background:rgba(0,0,0,0.4); color:white; border:1px solid var(--surface-border); border-radius:4px; font-family:inherit; resize:none;"><?= htmlspecialchars($user['join_reason'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-secondary" style="border:none; cursor:pointer; padding:12px; font-weight:bold;">Update Information Block</button>
                </form>

            <?php else: ?>
                <h3 class="neon-text" style="margin-bottom: 1.5rem;">Club Portfolio Data</h3>
                
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <div>
                        <span style="font-size: 0.85rem; color: var(--text-muted); display: block; margin-bottom: 2px;">Full Legal Signature</span>
                        <p style="font-size: 1.2rem; color: #fff; font-weight: 500;"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></p>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <span style="font-size: 0.85rem; color: var(--text-muted); display: block; margin-bottom: 2px;">Campus Email</span>
                            <p style="color: #fff;"><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                        <div>
                            <span style="font-size: 0.85rem; color: var(--text-muted); display: block; margin-bottom: 2px;">Date of Birth</span>
                            <p style="color: #fff;"><?= date('F j, Y', strtotime($user['dob'])) ?></p>
                        </div>
                    </div>

                    <div style="border-top: 1px solid var(--surface-border); padding-top: 1.5rem;">
                        <span style="font-size: 0.85rem; color: var(--text-muted); display: block; margin-bottom: 6px;">Club Entrance Motivation Narrative</span>
                        <p style="color: #ddd; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 6px; line-height: 1.6; border-left: 3px solid var(--neon-secondary);">
                            <?= nl2br(htmlspecialchars($user['join_reason'] ?? 'No statement provided by user.')) ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php include 'includes/footer.php'; ?>