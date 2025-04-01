<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$artist_id = (int)$_GET['id'];

// جلب بيانات الفنان
$artist = $db->query("SELECT * FROM artists WHERE id = $artist_id")->fetch_assoc();

if (!$artist) {
    header("HTTP/1.0 404 Not Found");
    die("الفنان غير موجود");
}

// جلب القصائد التي غناها
$poems = $db->query("
    SELECT p.id, p.title, p.poetic_meter, p.rhyme, pt.name AS poet_name
    FROM poems p
    JOIN poem_artist pa ON p.id = pa.poem_id
    JOIN poets pt ON p.poet_id = pt.id
    WHERE pa.artist_id = $artist_id
    ORDER BY p.title
");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title><?= safe_output($artist['name']) ?> - النظام الشعري</title>
</head>
<body>
    <h1><?= safe_output($artist['name']) ?></h1>
    
    <div class="artist-info">
        <?php if ($artist['image']): ?>
            <img src="/uploads/artists/<?= safe_output($artist['image']) ?>" alt="<?= safe_output($artist['name']) ?>" width="150">
        <?php endif; ?>
        
        <?php if ($artist['country']): ?>
            <p>الجنسية: <?= safe_output($artist['country']) ?></p>
        <?php endif; ?>
    </div>
    
    <h2>القصائد المغناة:</h2>
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