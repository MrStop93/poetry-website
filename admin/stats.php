<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!Auth::check()) {
    header("Location: login.php");
    exit();
}

// جلب الإحصائيات
$stats = [
    'poets' => $db->query("SELECT COUNT(*) FROM poets")->fetch_row()[0],
    'poems' => $db->query("SELECT COUNT(*) FROM poems")->fetch_row()[0],
    'artists' => $db->query("SELECT COUNT(*) FROM artists")->fetch_row()[0],
    'composers' => $db->query("SELECT COUNT(*) FROM composers")->fetch_row()[0],
    'musical_poems' => $db->query("SELECT COUNT(*) FROM poems WHERE is_musical = TRUE")->fetch_row()[0],
    'tags' => $db->query("SELECT COUNT(*) FROM tags")->fetch_row()[0]
];

// جلب أحدث القصائد
$latest_poems = $db->query("
    SELECT p.id, p.title, p.created_at, pt.name AS poet_name 
    FROM poems p
    JOIN poets pt ON p.poet_id = pt.id
    ORDER BY p.created_at DESC
    LIMIT 5
");

// جلب أكثر الكلمات الدلالية استخداماً
$popular_tags = $db->query("
    SELECT t.name, COUNT(pt.poem_id) AS count
    FROM tags t
    JOIN poem_tags pt ON t.id = pt.tag_id
    GROUP BY t.id
    ORDER BY count DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html dir="rtl">
<head>
    <title>الإحصائيات</title>
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
        }
        .stat-card h3 {
            margin-top: 0;
            color: #333;
        }
        .stat-card .count {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <h1>إحصائيات النظام</h1>
    
    <div class="stats-grid">
        <?php foreach ($stats as $key => $value): ?>
            <div class="stat-card">
                <h3><?= stat_label($key) ?></h3>
                <div class="count"><?= $value ?></div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="recent-items">
        <h2>أحدث القصائد المضافة</h2>
        <ul>
            <?php while ($poem = $latest_poems->fetch_assoc()): ?>
                <li>
                    <a href="../poem.php?id=<?= $poem['id'] ?>"><?= safe_output($poem['title']) ?></a>
                    - <?= safe_output($poem['poet_name']) ?>
                    <small>(<?= date('Y-m-d', strtotime($poem['created_at'])) ?>)</small>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
    
    <div class="popular-tags">
        <h2>أكثر الكلمات الدلالية استخداماً</h2>
        <ul>
            <?php while ($tag = $popular_tags->fetch_assoc()): ?>
                <li>
                    <?= safe_output($tag['name']) ?>
                    <span>(<?= $tag['count'] ?> قصائد)</span>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
</body>
</html>