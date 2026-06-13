<?php 
require 'config/db.php'; 
include 'includes/header.php'; 

// Executive Operation: Dynamic Event Erasure Hook
if (isset($_GET['delete_event']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'moderator') {
        $del_stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
        $del_stmt->execute([$_GET['delete_event']]);
        header("Location: index.php#events");
        exit;
    }
}

// --- NEW FEATURE: EVENT REGISTRATION HOOK ---
if (isset($_POST['register_event']) && isset($_SESSION['user_id'])) {
    $e_id = intval($_POST['event_id']);
    $u_id = $_SESSION['user_id'];
    
    // INSERT IGNORE prevents crashing if they double-click
    $reg_stmt = $pdo->prepare("INSERT IGNORE INTO event_registrations (event_id, user_id) VALUES (?, ?)");
    $reg_stmt->execute([$e_id, $u_id]);
    
    header("Location: index.php#events");
    exit;
}

// Fetch user's registered events to update button UI later
$user_registrations = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT event_id FROM event_registrations WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_registrations = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<section class="hidden" id="home">
    <div class="hidden-content">
        <div class="badge">Level Up Your Campus Life</div>
        <h1>Dive Into The <br><span class="gradient-text">Anime Multiverse</span></h1>
        <p>Your ultimate hub for watch parties, manga discussions, cosplay workshops, and epic convention road trips.</p>
        <div class="event-buttons">
            <a href="#events" class="btn btn-primary">Upcoming Events</a>
            <a href="#about" class="btn btn-secondary">Explore Club</a>
        </div>
    </div>
</section>


<section id="achievements" class="container">
    <div class="section-header hidden">
        <h2>Hall of <span class="neon-text">Glory</span></h2>
        <p>Our proudest moments and club milestones.</p>
    </div>
    <div class="achievement-grid hidden" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
        <?php
        // Fetch achievements from the database
        $ach_stmt = $pdo->query("SELECT * FROM achievements ORDER BY id DESC");
        $achievements = $ach_stmt->fetchAll();
        
        if (empty($achievements)): ?>
            <p style='color: var(--text-muted); text-align: center; grid-column: 1 / -1;'>No achievements recorded yet. Time to make history!</p>
        <?php else: 
            foreach ($achievements as $ach): ?>
                <div class="glass-card achievement-card" style="padding: 1.5rem; display: flex; flex-direction: column; align-items: center; text-align: center;">
                    
                    <?php if (!empty($ach['image_path']) && file_exists($ach['image_path'])): ?>
                        <img src="<?= htmlspecialchars($ach['image_path']) ?>" alt="<?= htmlspecialchars($ach['title']) ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px; margin-bottom: 1rem; border: 1px solid var(--surface-border);">
                    <?php else: ?>
                        <img src="https://images.unsplash.com/photo-1612404730960-5c71577fca11?auto=format&fit=crop&w=800&q=80" alt="Default Achievement" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px; margin-bottom: 1rem; border: 1px solid var(--surface-border); opacity: 0.7;">
                    <?php endif; ?>

                    <h3 class="neon-text" style="margin-bottom: 0.5rem; font-size: 1.4rem;"><?= htmlspecialchars($ach['title']) ?></h3>
                    <p style="font-size: 0.95rem; color: var(--text-muted);"><?= nl2br(htmlspecialchars($ach['description'])) ?></p>
                </div>
            <?php endforeach; 
        endif; ?>
    </div>
</section>

<!-- RESTORED: BENTO GRID ACTIVITIES SECTION -->
<section id="about" class="container">
    <div class="section-header hidden">
        <h2>Club <span class="neon-text">Activities</span></h2>
        <p>What we actually do when we meet up.</p>
    </div>
    <div class="bento-grid hidden">
        
        <div class="bento-item span-2 glass-card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
            <img src="images/watch parties.jpg" alt="Watch Party" style="width: 100%; height: 220px; object-fit: cover; border-bottom: 1px solid var(--surface-border);">
            <div class="bento-content" style="padding: 2rem;">
                <h3 style="margin-bottom: 0.5rem;">🍿 Weekly Watch Parties</h3>
                <p style="color: var(--text-muted);">Every Friday night, we project the latest seasonal drops on the big screen with surround sound.</p>
            </div>
        </div>

        <div class="bento-item glass-card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
            <img src="images/mangla library.jpg" alt="Manga Library" style="width: 100%; height: 180px; object-fit: cover; border-bottom: 1px solid var(--surface-border);">
            <div class="bento-content" style="padding: 1.5rem;">
                <h3 style="margin-bottom: 0.5rem; font-size: 1.2rem;">📖 Manga Library</h3>
                <p style="color: var(--text-muted); font-size: 0.9rem;">Access our massive physical collection of over 500+ volumes.</p>
            </div>
        </div>

        <div class="bento-item glass-card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
            <img src="images/cosplay lab.jpg" alt="Cosplay Lab" style="width: 100%; height: 180px; object-fit: cover; border-bottom: 1px solid var(--surface-border);">
            <div class="bento-content" style="padding: 1.5rem;">
                <h3 style="margin-bottom: 0.5rem; font-size: 1.2rem;">🧵 Cosplay Lab</h3>
                <p style="color: var(--text-muted); font-size: 0.9rem;">Monthly workshops on sewing, EVA foam armor, and SFX makeup.</p>
            </div>
        </div>

        <div class="bento-item span-2 glass-card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
            <img src="images/convention trip.jpg" alt="Convention Trips" style="width: 100%; height: 220px; object-fit: cover; border-bottom: 1px solid var(--surface-border); object-position: center 30%;">
            <div class="bento-content" style="padding: 2rem;">
                <h3 style="margin-bottom: 0.5rem;">🎫 Convention Trips</h3>
                <p style="color: var(--text-muted);">We organize group tickets, carpools, and hotel blocks for major regional anime conventions. Travel as a guild!</p>
            </div>
        </div>

    </div>
</section>

<!-- RESTORED: COMPLEX EVENT TIMELINE LOGIC -->
<section id="events" class="container">
    <div class="section-header hidden">
        <h2>Event <span class="neon-text">Timeline</span></h2>
        <p>Don't miss out on our upcoming club activities.</p>
    </div>
    
    <div class="timeline hidden" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem;">
        <?php
        // Fetch events ordered by upcoming dates
        $evt_stmt = $pdo->query("SELECT * FROM events ORDER BY event_date ASC");
        $events = $evt_stmt->fetchAll();
        
        if (empty($events)): ?>
            <p style='color: var(--text-muted); text-align: center; grid-column: 1 / -1;'>No upcoming events scheduled at the moment.</p>
        <?php else: 
            $current_time = time(); // Get current timestamp
            
            foreach ($events as $evt): 
                // Time Math Logic
                $start_time = strtotime($evt['event_date']);
                $duration = isset($evt['duration_minutes']) ? $evt['duration_minutes'] : 120; // Fallback if old data
                $end_time = $start_time + ($duration * 60); 
                $deadline_time = strtotime($evt['registration_deadline']);

                // Status Engine
                if ($current_time > $end_time) {
                    $status_text = "Expired";
                    $badge_bg = "rgba(255, 255, 255, 0.1)";
                    $badge_color = "var(--text-muted)";
                    $card_opacity = "0.6"; // Dim expired events
                } elseif ($current_time >= $start_time && $current_time <= $end_time) {
                    $status_text = "Ongoing 🔥";
                    $badge_bg = "var(--neon-primary)";
                    $badge_color = "#fff";
                    $card_opacity = "1";
                } else {
                    $status_text = "Upcoming";
                    $badge_bg = "var(--neon-secondary)";
                    $badge_color = "#000";
                    $card_opacity = "1";
                }
                
                // Format duration for display cleanly (e.g., "1d 2h")
                $days_display = floor($duration / (24 * 60));
                $remaining_minutes = $duration % (24 * 60);
                $hours_display = floor($remaining_minutes / 60);

                $duration_parts = [];
                if ($days_display > 0) $duration_parts[] = "{$days_display}d";
                if ($hours_display > 0) $duration_parts[] = "{$hours_display}h";
                $duration_str = implode(' ', $duration_parts);
                if (empty($duration_str)) $duration_str = "0h";
        ?>
                <div class="glass-card event-card" style="padding: 1.5rem; display: flex; flex-direction: column; opacity: <?= $card_opacity ?>; position: relative;">
                    
                    <div style="position: absolute; top: 1.5rem; right: 1.5rem; background: <?= $badge_bg ?>; color: <?= $badge_color ?>; padding: 4px 10px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; z-index: 10;">
                        <?= $status_text ?>
                    </div>

                    <?php if (!empty($evt['image_path']) && file_exists($evt['image_path'])): ?>
                        <img src="<?= htmlspecialchars($evt['image_path']) ?>" alt="<?= htmlspecialchars($evt['title']) ?>" style="width: 100%; height: 180px; object-fit: cover; border-radius: 8px; margin-bottom: 1rem; border: 1px solid var(--surface-border);">
                    <?php else: ?>
                        <img src="https://images.unsplash.com/photo-1540569014015-19a7be504e3a?auto=format&fit=crop&w=800&q=80" alt="Event Placeholder" style="width: 100%; height: 180px; object-fit: cover; border-radius: 8px; margin-bottom: 1rem; border: 1px solid var(--surface-border); opacity: 0.7;">
                    <?php endif; ?>

                    <p style="color: var(--neon-secondary); font-weight: bold; font-size: 0.95rem; margin-bottom: 0.3rem;">
                        <?= date('M j, Y • g:i A', $start_time) ?> (<?= $duration_str ?>)
                    </p>
                    <h3 style="font-size: 1.4rem; margin-bottom: 0.5rem; padding-right: 5rem;"><?= htmlspecialchars($evt['title']) ?></h3>
                    
                    <div style="background: rgba(0,0,0,0.3); padding: 10px; border-radius: 6px; margin-bottom: 1rem; font-size: 0.85rem; border-left: 3px solid <?= $current_time > $deadline_time ? 'var(--neon-primary)' : 'var(--neon-secondary)' ?>;">
                        <strong>📍 Venue:</strong> <?= htmlspecialchars($evt['location']) ?><br>
                        <strong>⏳ Registration Deadline:</strong> 
                        <span style="color: <?= $current_time > $deadline_time ? 'var(--neon-primary)' : '#ddd' ?>;">
                            <?= date('M j, Y • g:i A', $deadline_time) ?>
                            <?= ($current_time > $deadline_time && $status_text === 'Upcoming') ? "(Closed)" : "" ?>
                        </span>
                    </div>

                    <p style="flex-grow: 1; font-size: 0.95rem; color: #ddd; margin-bottom: 1.5rem;">
                        <?= nl2br(htmlspecialchars($evt['description'])) ?>
                    </p>

                    <div style="margin-top: auto;">
                        
                        <!-- NEW FEATURE: Event Registration Button -->
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <?php if(in_array($evt['id'], $user_registrations)): ?>
                                <button disabled class="btn" style="background: var(--neon-secondary); color: #000; border: none; padding: 8px; width: 100%; border-radius: 4px; font-weight: bold; cursor: not-allowed; margin-bottom: 10px;">
                                    Registered ✓
                                </button>
                            <?php elseif($current_time > $deadline_time || $status_text === "Expired"): ?>
                                <button disabled class="btn" style="background: rgba(255,255,255,0.1); color: var(--text-muted); border: none; padding: 8px; width: 100%; border-radius: 4px; font-weight: bold; cursor: not-allowed; margin-bottom: 10px;">
                                    Registration Closed
                                </button>
                            <?php else: ?>
                                <form method="POST" style="margin-bottom: 10px;">
                                    <input type="hidden" name="event_id" value="<?= $evt['id'] ?>">
                                    <button type="submit" name="register_event" class="btn btn-primary" style="width: 100%; padding: 8px; font-size: 0.9rem; border: none; cursor: pointer;">
                                        Join Event
                                    </button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- RESTORED: Admin Manual Delete Button -->
                        <?php 
                        $user_role = isset($_SESSION['role']) ? strtolower(str_replace('_', ' ', $_SESSION['role'])) : '';
                        if (in_array($user_role, ['admin', 'moderator'])): 
                        ?>
                            <a href="index.php?delete_event=<?= $evt['id'] ?>" class="btn" style="padding: 8px; font-size: 0.85rem; background: rgba(255, 42, 109, 0.1); border: 1px solid var(--neon-primary); color: var(--neon-primary); display: block; text-align: center; border-radius: 4px; text-decoration: none; transition: 0.3s;" onclick="return confirm('Purge this event timeline record?');">
                                Manual Delete
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; 
        endif; ?>
    </div>
</section>

<section id="team" class="container">
    <div class="section-header hidden">
        <h2>Guild <span class="neon-text">Leaders</span></h2>
        <p>Reach out to the exec board for collaborations or questions.</p>
    </div>
    <div class="team-grid hidden">
        <div class="team-card glass-card">
            <div class="avatar">T</div>
            <h3>Torikul Islam</h3>
            <p class="role">President</p>
            <a href="mailto:torikul@otakunexus.edu" class="contact-link">torikul@otakunexus.edu</a>
        </div>
        <div class="team-card glass-card">
            <div class="avatar">R</div>
            <h3>Rahad Islam</h3>
            <p class="role">Event Coordinator</p>
            <a href="mailto:rahad@otakunexus.edu" class="contact-link">rahad@otakunexus.edu</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>