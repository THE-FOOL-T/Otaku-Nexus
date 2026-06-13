<?php
include 'includes/header.php';
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    die("<section class='container' style='padding-top:12rem;'><h2 class='neon-text'>System Protected: Please log in to view discussions.</h2><br><a href='login.php' class='btn btn-primary'>Go to Login</a></section>");
}

// CREATE THREAD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_thread'])) {
    $title = htmlspecialchars($_POST['anime_title']);
    $status = $_POST['anime_status'];
    $rating = intval($_POST['initial_rating']);
    $review = htmlspecialchars($_POST['review_text']);
    
    $thumb_path = "";
    if (!empty($_FILES["thumbnail"]["name"])) {
        $dest = "uploads/" . time() . '_' . basename($_FILES["thumbnail"]["name"]);
        if (move_uploaded_file($_FILES["thumbnail"]["tmp_name"], $dest)) {
            $thumb_path = $dest;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO discussions (user_id, anime_title, anime_status, initial_rating, thumbnail_path, review_text) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $title, $status, $rating, $thumb_path, $review]);
    header("Location: discussions.php");
    exit;
}

// POST COMMENT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_comment'])) {
    $thread_id = intval($_POST['discussion_id']);
    $user_id = $_SESSION['user_id'];
    $comment = htmlspecialchars($_POST['comment_text']);
    $score = !empty($_POST['user_rating']) ? intval($_POST['user_rating']) : null;

    $stmt = $pdo->prepare("INSERT INTO discussion_comments (discussion_id, user_id, user_rating, comment_text) VALUES (?, ?, ?, ?)");
    $stmt->execute([$thread_id, $user_id, $score, $comment]);
    header("Location: discussions.php");
    exit;
}
?>

<section class="container" style="padding-top: 10rem; min-height: 100vh;">
    <div class="section-header">
        <h2>Anime <span class="neon-text">Review Matrix</span></h2>
        <p>Share and give  rating of anime</p>
    </div>

    <!-- CREATE NEW THREAD -->
    <div class="glass-card" style="padding: 2.5rem; margin-bottom: 4rem;">
        <h3 class="neon-text" style="margin-bottom: 1.5rem;">🎯 Initialize Review Thread</h3>
        <form method="POST" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:1.2rem;">
            <input type="hidden" name="start_thread" value="1">
            <div style="display:grid; grid-template-columns: 2fr 1fr 1fr; gap:1rem;">
                <input type="text" name="anime_title" placeholder="Anime Title" required style="padding:12px; background:rgba(0,0,0,0.5); color:white; border:1px solid var(--surface-border); border-radius:5px;">
                <select name="anime_status" required style="padding:12px; background:rgba(0,0,0,0.5); color:white; border:1px solid var(--surface-border); border-radius:5px;">
                    <option value="Currently Airing">Currently Airing</option>
                    <option value="Completed / Watched">Completed / Watched</option>
                </select>
                <input type="number" name="initial_rating" placeholder="Your Score (1-10)" min="1" max="10" required style="padding:12px; background:rgba(0,0,0,0.5); color:white; border:1px solid var(--surface-border); border-radius:5px;">
            </div>
            <div>
                <label style="display:block; margin-bottom:0.5rem; color:var(--text-muted); font-size:0.9rem;">Anime Thumbnail Graphic:</label>
                <input type="file" name="thumbnail" accept="image/*" required style="color:white;">
            </div>
            <textarea name="review_text" placeholder="Write your full review details here..." rows="3" required style="padding:12px; background:rgba(0,0,0,0.5); color:white; border:1px solid var(--surface-border); border-radius:5px; font-family:inherit;"></textarea>
            <button type="submit" class="btn btn-primary" style="align-self: flex-start; border:none; cursor:pointer;">Broadcast Thread</button>
        </form>
    </div>

    <!-- PORTAL THREADS FEED -->
    <div style="display:flex; flex-direction:column; gap:2.5rem; margin-bottom: 5rem;">
        <?php
        $threads = $pdo->query("SELECT d.*, u.username, u.role, u.rank_title FROM discussions d JOIN users u ON d.user_id = u.id ORDER BY d.created_at DESC")->fetchAll();
        
        foreach($threads as $th):
            // ALGORITHM: Weighted Average (Admin 3x, Mod 2x, Member 1x)
            $scores = [];
            $base_weight = ($th['role'] === 'admin') ? 3 : (($th['role'] === 'moderator') ? 2 : 1);
            $scores[] = ['rating' => $th['initial_rating'], 'weight' => $base_weight];
            
            $c_data = $pdo->prepare("SELECT dc.user_rating, u.role FROM discussion_comments dc JOIN users u ON dc.user_id = u.id WHERE dc.discussion_id = ? AND dc.user_rating IS NOT NULL");
            $c_data->execute([$th['id']]);
            while($c_row = $c_data->fetch()) {
                $w = ($c_row['role'] === 'admin') ? 3 : (($c_row['role'] === 'moderator') ? 2 : 1);
                $scores[] = ['rating' => $c_row['user_rating'], 'weight' => $w];
            }
            
            $sum_products = 0;
            $sum_weights = 0;
            foreach($scores as $s) {
                $sum_products += ($s['rating'] * $s['weight']);
                $sum_weights += $s['weight'];
            }
            $weighted_average = round($sum_products / $sum_weights, 1);
        ?>
            <div class="glass-card" style="padding: 2.5rem; display:grid; grid-template-columns: 200px 1fr; gap:2rem;">
                <div>
                    <?php if(!empty($th['thumbnail_path'])): ?>
                        <img src="<?= $th['thumbnail_path'] ?>" style="width:100%; height:260px; object-fit:cover; border-radius:6px; border:1px solid var(--surface-border);" alt="Cover Graphic">
                    <?php else: ?>
                        <div style="width:100%; height:260px; background:rgba(0,0,0,0.5); border-radius:6px;"></div>
                    <?php endif; ?>
                </div>

                <div style="display:flex; flex-direction:column; justify-content:space-between;">
                    <div>
                        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                            <div>
                                <h2 class="neon-text" style="margin-bottom:0.3rem;"><?= htmlspecialchars($th['anime_title']) ?></h2>
                                <span style="font-size:0.8rem; background:rgba(255,255,255,0.1); padding:3px 8px; border-radius:4px; color:var(--text-muted);"><?= $th['anime_status'] ?></span>
                            </div>
                            <div style="text-align:right;">
                                <div style="font-size:1.6rem; font-weight:900; color:#ff2a6d;">📊 <?= $weighted_average ?> <span style="font-size:0.9rem; color:white; font-weight:normal;">/10</span></div>
                                <small style="color:var(--neon-secondary); font-size:1.15rem;">Rating</small>
                            </div>
                        </div>
                        <p style="margin-top:1.5rem; color:#e0e0e0; line-height:1.6;"><?= htmlspecialchars($th['review_text']) ?></p>
                        <div style="font-size:0.8rem; color:var(--text-muted); margin-top:0.5rem;">
                            Thread started by: <strong><?= htmlspecialchars($th['username']) ?></strong> (<?= $th['rank_title'] ?>)
                        </div>
                    </div>

                    <div style="margin-top:2rem; background:rgba(0,0,0,0.3); padding:1.5rem; border-radius:6px;">
                        <h4 style="border-bottom:1px solid var(--surface-border); padding-bottom:0.5rem; margin-bottom:1rem;">Community Responses</h4>
                        
                        <?php
                        $comments_stmt = $pdo->prepare("SELECT dc.*, u.username, u.role FROM discussion_comments dc JOIN users u ON dc.user_id = u.id WHERE dc.discussion_id = ? ORDER BY dc.created_at ASC");
                        $comments_stmt->execute([$th['id']]);
                        $comments = $comments_stmt->fetchAll();
                        
                        foreach($comments as $cm): ?>
                            <div class="comment-box">
                                <div style="display:flex; justify-content:space-between; font-size:0.8rem; margin-bottom:4px;">
                                    <strong>
                                        <?= htmlspecialchars($cm['username']) ?> 
                                        <?php if($cm['role'] === 'admin') echo '<span class="badge-admin">ADMIN (3x)</span>'; ?>
                                        <?php if($cm['role'] === 'moderator') echo '<span class="badge-mod">MOD (2x)</span>'; ?>
                                    </strong>
                                    <?php if(!is_null($cm['user_rating'])): ?>
                                        <span style="color:yellow; font-weight:bold;">⭐ Rating: <?= $cm['user_rating'] ?>/10</span>
                                    <?php endif; ?>
                                </div>
                                <p style="font-size:0.9rem; color:#ccc;"><?= htmlspecialchars($cm['comment_text']) ?></p>
                            </div>
                        <?php endforeach; ?>

                        <!-- REPLY FORM -->
                        <form method="POST" style="margin-top:1.5rem; display:grid; grid-template-columns: 1fr 120px auto; gap:1rem;">
                            <input type="hidden" name="discussion_id" value="<?= $th['id'] ?>">
                            <input type="hidden" name="post_comment" value="1">
                            <input type="text" name="comment_text" placeholder="Write a response comment..." required style="padding:8px; background:rgba(0,0,0,0.5); color:white; border:1px solid var(--surface-border); border-radius:4px;">
                            <input type="number" name="user_rating" placeholder="Score (Optional)" min="1" max="10" style="padding:8px; background:rgba(0,0,0,0.5); color:white; border:1px solid var(--surface-border); border-radius:4px;">
                            <button type="submit" class="btn btn-secondary" style="padding:8px 16px; font-size:0.85rem; border:none; cursor:pointer;">Reply</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>