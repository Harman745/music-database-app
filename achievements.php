<?php
/**
 * Achievements Page
 * Developer: Harman Singh
 */
require_once 'db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user's achievements
$userAchievementsQuery = "
    SELECT 
        a.achievement_id,
        a.achievement_name,
        a.description,
        a.badge_icon,
        a.points,
        ua.earned_date
    FROM User_Achievement ua
    INNER JOIN Achievement a ON ua.achievement_id = a.achievement_id
    WHERE ua.user_id = ?
    ORDER BY ua.earned_date DESC
";
$earnedAchievements = fetchAll($userAchievementsQuery, 'i', [$user_id]);

// Fetch all available achievements
$allAchievementsQuery = "
    SELECT 
        achievement_id,
        achievement_name,
        description,
        badge_icon,
        points
    FROM Achievement
    ORDER BY points DESC
";
$allAchievements = fetchAll($allAchievementsQuery);

// Calculate total points
$totalPoints = 0;
foreach ($earnedAchievements as $achievement) {
    $totalPoints += $achievement['points'];
}

// Get earned achievement IDs
$earnedIds = array_column($earnedAchievements, 'achievement_id');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achievements - Music Database</title>
    <link rel="stylesheet" href="static/css/style.css">
    <style>
        .achievement-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .stat-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-box h3 {
            font-size: 2.5em;
            margin: 0;
            color: #1db954;
        }
        .stat-box p {
            margin: 10px 0 0 0;
            color: #666;
        }
        .achievements-section {
            margin: 40px 0;
        }
        .achievements-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .achievement-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.2s;
        }
        .achievement-card:hover {
            transform: translateY(-5px);
        }
        .achievement-card.earned {
            border: 2px solid #1db954;
            background: #f0fdf4;
        }
        .achievement-card.locked {
            opacity: 0.6;
            background: #f5f5f5;
        }
        .achievement-icon {
            font-size: 3em;
            margin-bottom: 10px;
        }
        .achievement-card h3 {
            margin: 10px 0;
            font-size: 1.2em;
        }
        .achievement-card p {
            color: #666;
            font-size: 0.9em;
            margin: 10px 0;
        }
        .achievement-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }
        .points {
            background: #1db954;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
        }
        .earned-date {
            font-size: 0.8em;
            color: #666;
        }
        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
        }
        .earned-badge {
            background: #1db954;
            color: white;
        }
        .locked-badge {
            background: #999;
            color: white;
        }
        .no-achievements {
            text-align: center;
            padding: 40px;
            color: #666;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="page-header">
        <div class="container">
            <h1>🏆 Achievements</h1>
            <p>Track your progress and unlock badges</p>
            <div class="achievement-stats">
                <div class="stat-box">
                    <h3><?php echo count($earnedAchievements); ?></h3>
                    <p>Achievements Earned</p>
                </div>
                <div class="stat-box">
                    <h3><?php echo $totalPoints; ?></h3>
                    <p>Total Points</p>
                </div>
                <div class="stat-box">
                    <h3><?php echo count($allAchievements); ?></h3>
                    <p>Total Available</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Earned Achievements -->
        <?php if (!empty($earnedAchievements)): ?>
        <section class="achievements-section">
            <h2>Earned Achievements</h2>
            <div class="achievements-grid">
                <?php foreach ($earnedAchievements as $achievement): ?>
                <div class="achievement-card earned">
                    <div class="achievement-icon"><?php echo $achievement['badge_icon'] ?: '🏆'; ?></div>
                    <h3><?php echo htmlspecialchars($achievement['achievement_name']); ?></h3>
                    <p><?php echo htmlspecialchars($achievement['description']); ?></p>
                    <div class="achievement-meta">
                        <span class="points">+<?php echo $achievement['points']; ?> pts</span>
                        <span class="earned-date">Earned: <?php echo date('M d, Y', strtotime($achievement['earned_date'])); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- All Achievements -->
        <section class="achievements-section">
            <h2>All Achievements</h2>
            <div class="achievements-grid">
                <?php foreach ($allAchievements as $achievement): ?>
                <div class="achievement-card <?php echo in_array($achievement['achievement_id'], $earnedIds) ? 'earned' : 'locked'; ?>">
                    <div class="achievement-icon"><?php echo $achievement['badge_icon'] ?: '🏆'; ?></div>
                    <h3><?php echo htmlspecialchars($achievement['achievement_name']); ?></h3>
                    <p><?php echo htmlspecialchars($achievement['description']); ?></p>
                    <div class="achievement-meta">
                        <span class="points">+<?php echo $achievement['points']; ?> pts</span>
                        <?php if (in_array($achievement['achievement_id'], $earnedIds)): ?>
                            <span class="status earned-badge">✓ Earned</span>
                        <?php else: ?>
                            <span class="status locked-badge">🔒 Locked</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <?php if (empty($allAchievements)): ?>
            <p class="no-achievements">No achievements available yet. Check back later!</p>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
