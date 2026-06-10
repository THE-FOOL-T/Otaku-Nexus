<?php
include 'includes/header.php';
require 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = trim(htmlspecialchars($_POST['username']));
    $email = trim(htmlspecialchars($_POST['email']));
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // New Profile Fields
    $first_name = trim(htmlspecialchars($_POST['first_name']));
    $last_name = trim(htmlspecialchars($_POST['last_name']));
    $student_id = trim(htmlspecialchars($_POST['student_id']));
    $department = trim(htmlspecialchars($_POST['department']));
    $batch = trim(htmlspecialchars($_POST['batch']));
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $join_reason = trim(htmlspecialchars($_POST['join_reason']));

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, first_name, last_name, student_id, department, batch, dob, gender, join_reason, status, rank_title) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'Pending Review')");
        $stmt->execute([$user, $email, $pass, $first_name, $last_name, $student_id, $department, $batch, $dob, $gender, $join_reason]);
        
        echo "<script>alert('Application submitted successfully! An administrator will review your profile metrics shortly.'); window.location.href='login.php';</script>";
    } catch (PDOException $e) {
        $error = "Registration failed. Username, Student ID, or Email is already registered.";
    }
}
?>

<section class="container" style="min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 10rem 2rem 4rem 2rem;">
    <div class="glass-card" style="padding: 3rem; width: 100%; max-width: 650px;">
        <h2 class="neon-text" style="margin-bottom: 0.5rem; text-align: center;">Request Entry</h2>
        <p style="color: var(--text-muted); text-align: center; margin-bottom: 2rem;">Complete your campus profile initialization</p>
        
        <?php if(isset($error)) echo "<p style='color:var(--neon-primary); margin-bottom:1rem; text-align:center;'>$error</p>"; ?>
        
        <form method="POST" style="display:flex; flex-direction:column; gap:1.2rem;">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <input type="text" name="username" placeholder="Username" required style="width:100%; padding:12px; background:rgba(0,0,0,0.6); color:white; border:1px solid var(--surface-border); border-radius:6px;">
                <input type="email" name="email" placeholder="Student Email Address" required style="width:100%; padding:12px; background:rgba(0,0,0,0.6); color:white; border:1px solid var(--surface-border); border-radius:6px;">
            </div>

            <input type="password" name="password" placeholder="Password" required style="width:100%; padding:12px; background:rgba(0,0,0,0.6); color:white; border:1px solid var(--surface-border); border-radius:6px;">
            
            <hr style="border: none; border-top: 1px solid var(--surface-border); margin: 0.5rem 0;">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <input type="text" name="first_name" placeholder="First Name" required style="width:100%; padding:12px; background:rgba(0,0,0,0.6); color:white; border:1px solid var(--surface-border); border-radius:6px;">
                <input type="text" name="last_name" placeholder="Last Name" required style="width:100%; padding:12px; background:rgba(0,0,0,0.6); color:white; border:1px solid var(--surface-border); border-radius:6px;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                <input type="text" name="student_id" placeholder="Student ID" required style="width:100%; padding:12px; background:rgba(0,0,0,0.6); color:white; border:1px solid var(--surface-border); border-radius:6px;">
                <input type="text" name="department" placeholder="Department (e.g. CSE)" required style="width:100%; padding:12px; background:rgba(0,0,0,0.6); color:white; border:1px solid var(--surface-border); border-radius:6px;">
                <input type="text" name="batch" placeholder="Batch" required style="width:100%; padding:12px; background:rgba(0,0,0,0.6); color:white; border:1px solid var(--surface-border); border-radius:6px;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; align-items: center;">
                <div>
                    <label style="font-size:0.8rem; color:var(--text-muted); display:block; margin-bottom:4px;">Date of Birth</label>
                    <input type="date" name="dob" required style="width:100%; padding:12px; background:rgba(0,0,0,0.6); color:white; border:1px solid var(--surface-border); border-radius:6px;">
                </div>
                <div>
                    <label style="font-size:0.8rem; color:var(--text-muted); display:block; margin-bottom:4px;">Gender Status</label>
                    <select name="gender" required style="width:100%; padding:12px; background:rgba(0,0,0,0.6); color:white; border:1px solid var(--surface-border); border-radius:6px; cursor:pointer;">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <div>
                <label style="font-size:0.8rem; color:var(--text-muted); display:block; margin-bottom:4px;">Why do you wish to join Otaku Nexus?</label>
                <textarea name="join_reason" rows="3" placeholder="Share your motivation (favorite series, interests, workshop goals)..." required style="width:100%; padding:12px; background:rgba(0,0,0,0.6); color:white; border:1px solid var(--surface-border); border-radius:6px; font-family:inherit; resize:none;"></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="border:none; width:100%; padding:14px; font-weight:bold; cursor:pointer; margin-top:1rem;">Submit Entry Request</button>
        </form>
    </div>
</section>

<?php include 'includes/footer.php'; ?>