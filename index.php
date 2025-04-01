<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// جلب أحدث القصائد
//$latest_poems = $db->query("
//    SELECT p.id, p.title, p.rhyme, p.verses_count, pt.name AS poet_name, pt.image AS poet_image
//    FROM poems p
//    JOIN poets pt ON p.poet_id = pt.id
//    ORDER BY p.created_at DESC
//    LIMIT 6
//");

$latest_poems = $db->query("
    SELECT p.id, p.title, p.poet_id, p.rhyme, p.verses_count, 
           pt.name AS poet_name, pt.image AS poet_image
    FROM poems p
    JOIN poets pt ON p.poet_id = pt.id
    ORDER BY p.created_at DESC
    LIMIT 6
");

// جلب الشعراء الأكثر نشاطاً
$active_poets = $db->query("
    SELECT pt.id, pt.name, pt.image, COUNT(p.id) AS poems_count
    FROM poets pt
    JOIN poems p ON pt.id = p.poet_id
    GROUP BY pt.id
    ORDER BY poems_count DESC
    LIMIT 4
");

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="assets/css/style.css">
	<script src="js/main.js"></script>
	<script src="assets/js/search.js"></script>
    <title>النظام الشعري - الرئيسية</title>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
        }
        body {
            font-family: 'Traditional Arabic', Arial, sans-serif;
            margin: 0;
            padding: 0;
            line-height: 1.6;
            color: #333;
        }
        header {
            background: var(--primary-color);
            color: white;
            padding: 20px 0;
            text-align: center;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        .section-title {
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
            margin-top: 40px;
        }
        .poems-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .poem-card {
            background: white;
            border: 1px solid #eee;
            border-radius: 5px;
            padding: 15px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .poem-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .poet-card {
            text-align: center;
            margin-bottom: 20px;
        }
        .poet-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }
        .search-box {
            max-width: 600px;
            margin: 30px auto;
        }
        .search-input {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--secondary-color);
            border-radius: 30px;
            font-size: 16px;
        }
        footer {
            background: var(--primary-color);
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: 40px;
        }
        @media (max-width: 768px) {
            .poems-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>النظام الشعري</h1>
            <p>منصة متكاملة للشعر العربي</p>
        </div>
    </header>
    
    <div class="container">
        <div class="search-box">
            <form action="search.php" method="GET">
                <input type="text" name="q" class="search-input" placeholder="ابحث عن قصيدة، شاعر أو فنان...">
            </form>
        </div>
        
        <h2 class="section-title">أحدث القصائد</h2>
        <div class="poems-grid">
            <?php while ($poem = $latest_poems->fetch_assoc()): ?>
                <div class="poem-card">
                    <h3><a href="poem.php?id=<?= $poem['id'] ?>"><?= safe_output($poem['title']) ?></a></h3>
                    <div class="poet-info">
                        <a href="poet.php?id=<?= $poem['poet_id'] ?>">
                            <?php if ($poem['poet_image']): ?>
                                <img src="/uploads/poets/<?= safe_output($poem['poet_image']) ?>" width="40" style="border-radius:50%;vertical-align:middle">
                            <?php endif; ?>
                            <?= safe_output($poem['poet_name']) ?>
                        </a>
                    </div>
                    <div class="poem-meta">
                        <?php if ($poem['rhyme']): ?>
                            <span>القافية: <?= safe_output($poem['rhyme']) ?></span>
                        <?php endif; ?>
                        <?php if ($poem['verses_count']): ?>
                            <span> - الأبيات: <?= safe_output($poem['verses_count']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
        <h2 class="section-title">أبرز الشعراء</h2>
        <div style="display: flex; flex-wrap: wrap; justify-content: center;">
            <?php while ($poet = $active_poets->fetch_assoc()): ?>
                <div class="poet-card" style="margin: 10px; padding: 15px; width: 200px;">
                    <a href="poet.php?id=<?= $poet['id'] ?>">
                        <?php if ($poet['image']): ?>
                            <img src="/uploads/poets/<?= safe_output($poet['image']) ?>" alt="<?= safe_output($poet['name']) ?>" class="poet-image">
                        <?php else: ?>
                            <div style="width:100px;height:100px;background:#eee;border-radius:50%;margin:0 auto 10px;"></div>
                        <?php endif; ?>
                        <h3><?= safe_output($poet['name']) ?></h3>
                    </a>
                    <p><?= $poet['poems_count'] ?> قصيدة</p>
                </div>
            <?php endwhile; ?>
        </div>
		
		<h2 class="section-title">كلمات الدلالية</h2>
        <div style="display: flex; flex-wrap: wrap; justify-content: center;">
            <?php while ($poet = $active_poets->fetch_assoc()): ?>
                <div class="poet-card" style="margin: 10px; padding: 15px; width: 200px;">
                    <a href="poet.php?id=<?= $poet['id'] ?>">
                        <?php if ($poet['image']): ?>
                            <img src="/uploads/poets/<?= safe_output($poet['image']) ?>" alt="<?= safe_output($poet['name']) ?>" class="poet-image">
                        <?php else: ?>
                            <div style="width:100px;height:100px;background:#eee;border-radius:50%;margin:0 auto 10px;"></div>
                        <?php endif; ?>
                        <h3><?= safe_output($poet['name']) ?></h3>
                    </a>
                    <p><?= $poet['poems_count'] ?> قصيدة</p>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    
    <footer>
        <div class="container">
            <p>جميع الحقوق محفوظة &copy; <?= date('Y') ?> - النظام الشعري</p>
        </div>
    </footer>
</body>
</html>