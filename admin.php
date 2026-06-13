<?php
include 'includes/header.php';
require 'config/db.php';

// Normalize role to prevent capitalization/underscore bugs
$current_role = isset($_SESSION['role']) ? strtolower(str_replace('_', ' ', $_SESSION['role'])) : '';

// Access Control: Allow Admins, Moderators, and Event Coordinators
if (!in_array($current_role, ['admin', 'moderator', 'event coordinator'])) {
    die("<section class='container' style='padding-top:12rem;'><h2 class='neon-text'>Access Violation: Security clearance insufficient.</h2></section>");
}

// --- FORM ACTION SUBMISSION CONTROLLER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Membership Approval Engine (Admins & Moderators)
    if (isset($_POST['process_user']) && in_array($current_role, ['admin', 'moderator'])) {
        $target_id = $_POST['target_user_id'];
        $decision = $_POST['decision']; // 'approved' or 'rejected'
        $rank = ($decision === 'approved') ? 'Member' : 'Pending';
        
        $stmt = $pdo->prepare("UPDATE users SET status = ?, rank_title = ? WHERE id = ?");
        $stmt->execute([$decision, $rank, $target_id]);
    }

    // 2. Kick / Eviction Handler (Admins & Moderators)
    if (isset($_POST['kick_user']) && in_array($current_role, ['admin', 'moderator'])) {
        $target_id = $_POST['target_user_id'];
        
        $chk = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $chk->execute([$target_id]);
        $target_profile = $chk->fetch();
        
        if ($target_profile) {
            $can_kick = false;
            // Admin can kick anyone except another admin
            if ($current_role === 'admin' && strtolower($target_profile['role']) !== 'admin') {
                $can_kick = true;
            }
            // Moderators can only kick standard members
            if ($current_role === 'moderator' && strtolower($target_profile['role']) === 'member') {
                $can_kick = true;
            }

            if ($can_kick) {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$target_id]);
            } else {
                echo "<script>alert('Privilege Failure: You do not have permission to kick this user.');</script>";
            }
        }
    }

    // 3. Achievement Publisher (Admin Only)
    if (isset($_POST['create_achievement']) && $current_role === 'admin') {
        $title = htmlspecialchars($_POST['title']);
        $desc = htmlspecialchars($_POST['description']);
        
        $target_dir = "uploads/";
        $db_image_path = "";
        if (!empty($_FILES["achievement_image"]["name"])) {
            $file_name = time() . '_' . basename($_FILES["achievement_image"]["name"]);
            $target_file = $target_dir . $file_name;
            if (move_uploaded_file($_FILES["achievement_image"]["tmp_name"], $target_file)) {
                $db_image_path = $target_file;
            }
        }
        
        $stmt = $pdo->prepare("INSERT INTO achievements (title, description, image_path) VALUES (?, ?, ?)");
        $stmt->execute([$title, $desc, $db_image_path]);
    }

    // 4. Event Deployment (Admin & Event Coordinator)
    if (isset($_POST['create_event']) && in_array($current_role, ['admin', 'event coordinator'])) {
        $title = htmlspecialchars($_POST['title']);
        $desc = htmlspecialchars($_POST['description']);
        $dt = $_POST['event_date'];
        $loc = htmlspecialchars($_POST['location']);
        
        // Convert Days & Hours into total minutes for the database
        $days = isset($_POST['duration_days']) ? intval($_POST['duration_days']) : 0;
        $hours = isset($_POST['duration_hours']) ? intval($_POST['duration_hours']) : 0;
        $total_duration_minutes = ($days * 24 * 60) + ($hours * 60); 

        $deadline = $_POST['registration_deadline'];    
        
        // Handle Event Image Upload
        $target_dir = "uploads/";
        $db_event_image_path = "";
        if (!empty($_FILES["event_image"]["name"])) {
            $file_name = time() . '_event_' . basename($_FILES["event_image"]["name"]);
            $target_file = $target_dir . $file_name;
            if (move_uploaded_file($_FILES["event_image"]["tmp_name"], $target_file)) {
                $db_event_image_path = $target_file;
            }
        }
        
        $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, location, image_path, duration_minutes, registration_deadline) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $desc, $dt, $loc, $db_event_image_path, $total_duration_minutes, $deadline]);
    }

    // 5. User Role Update (Admin Only)
    if (isset($_POST['update_role']) && $current_role === 'admin') {
        $target_id = $_POST['target_user_id'];
        $new_role = strtolower($_POST['new_role']);
        $allowed_roles = ['member', 'event coordinator', 'moderator', 'admin'];
        
        if (in_array($new_role, $allowed_roles)) {
            $new_rank_title = ucwords($new_role); // Capitalizes the words for visual display
            $stmt = $pdo->prepare("UPDATE users SET role = ?, rank_title = ? WHERE id = ?");
            $stmt->execute([$new_role, $new_rank_title, $target_id]);
        }
    }
    
    // Redirect to clear POST data
    header("Location: admin.php");
    exit;
}
?>

<section class="container" style="padding-top: 10rem; min-height: 100vh;">
    <div class="section-header">
        <h2>Club <span class="neon-text">Dashboard</span></h2>
        <p>Active Rights: Management Tier -> <?= strtoupper($current_role) ?></p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-bottom: 3rem;">
        
        <?php if($current_role === 'admin'): ?>
        <div class="glass-card" style="padding: 2rem;">
            <h3 class="neon-text" style="margin-bottom:1rem;">Add Club Achievement</h3>
            <form method="POST" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:1rem;">
                <input type="hidden" name="create_achievement" value="1">
                <input type="text" name="title" placeholder="Achievement Title" required style="padding:10px; background:rgba(0,0,0,0.4); color:white; border:1px solid var(--surface-border); border-radius:4px;">
                <input type="file" name="achievement_image" accept="image/*" style="padding:10px; color:white; border:1px solid var(--surface-border); border-radius:4px;">
                <textarea name="description" placeholder="Achievement narrative text details..." rows="3" required style="padding:10px; background:rgba(0,0,0,0.4); color:white; border:1px solid var(--surface-border); border-radius:4px; font-family:inherit;"></textarea>
                <button type="submit" class="btn btn-primary" style="border:none; cursor:pointer;">Publish Achievement</button>
            </form>
        </div>
        <?php endif; ?>

        <?php if(in_array($current_role, ['admin', 'event coordinator'])): ?>
        <div class="glass-card" style="padding: 2rem;">
            <h3 class="neon-text" style="margin-bottom:1rem;">Schedule New Event</h3>
            <form method="POST" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:1rem;">
                <input type="hidden" name="create_event" value="1">
                <input type="text" name="title" placeholder="Event Name" required style="padding:10px; background:rgba(0,0,0,0.4); color:white; border:1px solid var(--surface-border); border-radius:4px;">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <label style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 5px; display: block;">Event Start</label>
                        <input type="datetime-local" name="event_date" required style="width: 100%; padding:10px; background:rgba(0,0,0,0.4); color:white; border:1px solid var(--surface-border); border-radius:4px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 5px; display: block;">Duration</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="number" name="duration_days" placeholder="Days" min="0" value="0" style="width: 50%; padding:10px; background:rgba(0,0,0,0.4); color:white; border:1px solid var(--surface-border); border-radius:4px; box-sizing: border-box;">
                            <input type="number" name="duration_hours" placeholder="Hours" min="0" value="2" required style="width: 50%; padding:10px; background:rgba(0,0,0,0.4); color:white; border:1px solid var(--surface-border); border-radius:4px; box-sizing: border-box;">
                        </div>
                    </div>
                </div>

                <div>
                    <label style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 5px; display: block;">Registration Deadline</label>
                    <input type="datetime-local" name="registration_deadline" required style="width: 100%; padding:10px; background:rgba(0,0,0,0.4); color:white; border:1px solid var(--surface-border); border-radius:4px; box-sizing: border-box;">
                </div>

                <input type="text" name="location" placeholder="Venue Location" required style="padding:10px; background:rgba(0,0,0,0.4); color:white; border:1px solid var(--surface-border); border-radius:4px;">
                
                <div>
                    <label style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 5px; display: block;">Event Banner Image</label>
                    <input type="file" name="event_image" accept="image/*" style="width: 100%; padding:10px; color:white; border:1px solid var(--surface-border); border-radius:4px; box-sizing: border-box;">
                </div>
                
                <textarea name="description" placeholder="Provide event details..." rows="2" required style="padding:10px; background:rgba(0,0,0,0.4); color:white; border:1px solid var(--surface-border); border-radius:4px; font-family:inherit;"></textarea>
                
                <button type="submit" class="btn btn-secondary" style="border:none; cursor:pointer;">Deploy Event</button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <?php if(in_array($current_role, ['admin', 'moderator'])): ?>
    <div class="glass-card" style="padding: 2rem; margin-bottom: 3rem;">
        <h3 class="neon-text" style="margin-bottom: 1rem;">Pending Access Applications</h3>
        <div style="overflow-x: auto;">
            <table style="width:100%; border-collapse:collapse; text-align:left;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--surface-border); color: var(--text-muted);">
                        <th style="padding:10px;">Username</th>
                        <th style="padding:10px;">Email</th>
                        <th style="padding:10px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $p_stmt = $pdo->query("SELECT * FROM users WHERE status = 'pending'");
                    $pendings = $p_stmt->fetchAll();
                    if(empty($pendings)) {
                        echo "<tr><td colspan='3' style='padding:15px; text-align:center; color:var(--text-muted);'>No outstanding entry permissions requested.</td></tr>";
                    }
                    foreach($pendings as $p): ?>
                        <tr style="border-bottom: 1px solid var(--surface-border);">
                            <!-- Hyperlinked Applicant Profile Metric Hook -->
                            <td style="padding:12px;">
                                <a href="profile.php?id=<?= $p['id'] ?>" style="color: var(--neon-secondary); text-decoration: none; font-weight: bold; transition: color 0.2s;" onmouseover="this.style.color='var(--neon-primary)'" onmouseout="this.style.color='var(--neon-secondary)'">
                                    <?= htmlspecialchars($p['username']) ?> 🔍
                                </a>
                            </td>
                            <td style="padding:12px;"><?= htmlspecialchars($p['email']) ?></td>
                            <td style="padding:12px;">
                                <form method="POST" style="display:inline-block; margin-right:5px;">
                                    <input type="hidden" name="process_user" value="1">
                                    <input type="hidden" name="target_user_id" value="<?= $p['id'] ?>">
                                    <input type="hidden" name="decision" value="approved">
                                    <button type="submit" style="background:var(--neon-secondary); color:black; border:none; padding:6px 12px; font-weight:bold; cursor:pointer; border-radius:4px;">Approve</button>
                                </form>
                                <form method="POST" style="display:inline-block;">
                                    <input type="hidden" name="process_user" value="1">
                                    <input type="hidden" name="target_user_id" value="<?= $p['id'] ?>">
                                    <input type="hidden" name="decision" value="rejected">
                                    <button type="submit" style="background:var(--neon-primary); color:white; border:none; padding:6px 12px; font-weight:bold; cursor:pointer; border-radius:4px;">Deny</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <div class="glass-card" style="padding: 2rem;">
        <h3 style="color:var(--neon-primary); margin-bottom:1rem;">Member Roster</h3>
        <div style="overflow-x: auto;">
            <table style="width:100%; border-collapse:collapse; text-align:left;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--surface-border); color: var(--text-muted);">
                        <th style="padding:10px;">Account Holder</th>
                        <th style="padding:10px;">Classification Tier</th>
                        <th style="padding:10px;">Administrative Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $u_stmt = $pdo->prepare("SELECT * FROM users WHERE id != ? AND status='approved' ORDER BY role DESC");
                    $u_stmt->execute([$_SESSION['user_id']]);
                    while($u = $u_stmt->fetch()): 
                        $u_role = strtolower($u['role']);
                        $has_clearance = false;
                        if ($current_role === 'admin' && $u_role !== 'admin') $has_clearance = true;
                        if ($current_role === 'moderator' && $u_role === 'member') $has_clearance = true;
                    ?>
                        <tr style="border-bottom: 1px solid var(--surface-border); opacity: <?= $has_clearance ? '1' : '0.6' ?>;">
                            <!-- Hyperlinked Active Roster Profile Hook -->
                            <td style="padding:12px; font-weight:bold;">
                                <a href="profile.php?id=<?= $u['id'] ?>" style="color: #fff; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--neon-secondary)'" onmouseout="this.style.color='#fff'">
                                    <?= htmlspecialchars($u['username']) ?>
                                </a>
                            </td>
                            <td style="padding:12px;">
                                <?php if($current_role === 'admin'): ?>
                                    <form method="POST" style="margin: 0;">
                                        <input type="hidden" name="update_role" value="1">
                                        <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                        <select name="new_role" onchange="this.form.submit()" style="background:rgba(0,0,0,0.6); color:white; border:1px solid var(--surface-border); border-radius:4px; padding:6px; font-family:inherit; cursor:pointer;">
                                            <option value="member" <?= $u_role === 'member' ? 'selected' : '' ?>>Member</option>
                                            <option value="event coordinator" <?= $u_role === 'event coordinator' ? 'selected' : '' ?>>Event Coordinator</option>
                                            <option value="moderator" <?= $u_role === 'moderator' ? 'selected' : '' ?>>Moderator</option>
                                            <option value="admin" <?= $u_role === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                    </form>
                                <?php else: ?>
                                    <span style="color: <?= ($u_role === 'moderator') ? 'yellow' : (($u_role === 'admin') ? 'orange' : (($u_role === 'event coordinator') ? '#05d9e8' : 'white')) ?>;">
                                        <?= strtoupper($u_role) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:12px;">
                                <?php if($has_clearance): ?>
                                    <form method="POST" onsubmit="return confirm('Remove this user from the club?');" style="display:inline-block;">
                                        <input type="hidden" name="kick_user" value="1">
                                        <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                        <button type="submit" style="background:none; border:1px solid var(--neon-primary); color:var(--neon-primary); font-weight:bold; padding:6px 12px; cursor:pointer; border-radius:4px;">Remove Member</button>
                                    </form>
                                <?php else: ?>
                                    <span style="font-size:0.85rem; color:var(--text-muted);">Locked</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>