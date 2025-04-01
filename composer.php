<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$composer_id = (int)$_GET['id'];

// جلب بيانات الملحن
$composer = $db->query("SELECT * FROM composers WHERE id = $composer_id")->fetch_assoc();

if (!$composer) {
    header("HTTP/1.0 404 Not Found");
    die("الملحن غير موجود");
}

// جلب القصائد التي لحنها
$poems = $db->query("
    SELECT p.id, p.title, p.poetic_meter, p.rhyme, pt.name AS poet_name
    FROM poems p
    JOIN poem_composer pc ON p.id = pc.poem_id
    JOIN poets pt ON p.poet_id = pt.id
    WHERE pc.composer_id = $composer_id
    ORDER BY p.title
");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title><?= safe_output($composer['name']) ?> - النظام الشعري</title>
</head>
<body>
    <h1><?= safe_output($composer['name']) ?></h1>
    
    <div class="composer-info">
        <?php if ($composer['image']): ?>
            <img src="/uploads/composers/<?= safe_output($composer['image']) ?>" alt="<?= safe_output($composer['name']) ?>" width="150">
        <?php endif; ?>
        
        <?php if ($composer['country']): ?>
            <p>الجنسية: <?= safe_output($composer['country']) ?></p>
        <?php endif; ?>
    </div>
    
    <h2>الأعمال الموسيقية:</h2>
    <ul class="poems-list">
        <?php while ($poem = $poems->fetch_assoc()): ?>
            <li>
                <a href="poem.php?id=<?= $poem['id'] ?>"><?= safe_output($poem['title']) ?></a>
                <span> (<?= safe_output($poem['poet_name']) ?> - <?= safe_output($poem['poetic_meter']) ?>)</span>
            </li>
        <?php endwhile; ?>
    </ul>
</body>
</html>