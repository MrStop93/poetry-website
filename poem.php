<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$poem_id = (int)$_GET['id'];

// جلب بيانات القصيدة
$stmt = $db->prepare("SELECT p.*, pt.name AS poet_name, pt.image AS poet_image 
                      FROM poems p 
                      JOIN poets pt ON p.poet_id = pt.id 
                      WHERE p.id = ?");
$stmt->bind_param("i", $poem_id);
$stmt->execute();
$poem = $stmt->get_result()->fetch_assoc();

if (!$poem) {
    header("HTTP/1.0 404 Not Found");
    die("القصيدة غير موجودة");
}

// جلب التفاصيل الموسيقية إن وجدت
$music_details = [];
if ($poem['is_musical']) {
    $music_details = $db->query("
        SELECT 
            a.name AS artist_name, 
            c.name AS composer_name,
            pc.name AS company_name,
            pc.country AS company_country,
            pp.production_year,
            pp.recording_type,
            pp.rhythm,
			artist_id,
			composer_id,
            pp.maqam
        FROM poems p
        LEFT JOIN poem_artist pa ON p.id = pa.poem_id
        LEFT JOIN artists a ON pa.artist_id = a.id
        LEFT JOIN poem_composer pcmp ON p.id = pcmp.poem_id
        LEFT JOIN composers c ON pcmp.composer_id = c.id
        LEFT JOIN poem_production pp ON p.id = pp.poem_id
        LEFT JOIN production_companies pc ON pp.company_id = pc.id
        WHERE p.id = $poem_id
    ")->fetch_assoc();
}

// جلب الحقول المخصصة
$custom_fields = $db->query("SELECT field_name, field_value FROM poem_custom_fields WHERE poem_id = $poem_id");

// جلب الكلمات الدلالية
$tags = $db->query("SELECT t.name FROM tags t JOIN poem_tags pt ON t.id = pt.tag_id WHERE pt.poem_id = $poem_id");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="assets/css/style.css">
    <title><?= safe_output($poem['title']) ?> - النظام الشعري</title>
    <style>
        body {
            font-family: 'Traditional Arabic', Arial, sans-serif;
            line-height: 1.8;
        }
        .poem-content {
            white-space: pre-line;
            text-align: center;
            font-size: 1.2em;
        }
        .poem-meta {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <h1><?= safe_output($poem['title']) ?></h1>
    
    <div class="poet-info">
        <img src="/uploads/poets/<?= safe_output($poem['poet_image']) ?>" alt="<?= safe_output($poem['poet_name']) ?>" width="80">
        <h2>بقلم: <?= safe_output($poem['poet_name']) ?></h2>
    </div>
    
    <div class="poem-meta">
        <p>البحر الشعري: <?= safe_output($poem['poetic_meter']) ?></p>
        <p>القافية: <?= safe_output($poem['rhyme']) ?></p>
        <p>عدد الأبيات: <?= safe_output($poem['verses_count']) ?></p>
        <?php if ($poem['publish_year']): ?>
            <p>سنة النشر: <?= safe_output($poem['publish_year']) ?></p>
        <?php endif; ?>
    </div>
    
    <div class="poem-content">
        <?= safe_output($poem['content']) ?>
    </div>
    
    <?php if ($poem['is_musical'] && $music_details): ?>
    <div class="music-details">
        <h3>التفاصيل الموسيقية:</h3>
        <ul>
            <?php if ($music_details['artist_name']): ?>
                <li>الفنان: <a href="artist.php?id=<?= $music_details['artist_id'] ?>"><?= safe_output($music_details['artist_name']) ?></a></li>
            <?php endif; ?>
            
            <?php if ($music_details['composer_name']): ?>
                <li>الملحن: <a href="composer.php?id=<?= $music_details['composer_id'] ?>"><?= safe_output($music_details['composer_name']) ?></a></li>
            <?php endif; ?>
            
            <?php if ($music_details['company_name']): ?>
                <li>شركة الإنتاج: <?= safe_output($music_details['company_name']) ?> (<?= safe_output($music_details['company_country']) ?>)</li>
            <?php endif; ?>
            
            <?php if ($music_details['production_year']): ?>
                <li>سنة الإنتاج: <?= safe_output($music_details['production_year']) ?></li>
            <?php endif; ?>
            
            <?php if ($music_details['recording_type']): ?>
                <li>نوع التسجيل: <?= safe_output($music_details['recording_type']) ?></li>
            <?php endif; ?>
            
            <?php if ($music_details['rhythm']): ?>
                <li>الإيقاع: <?= safe_output($music_details['rhythm']) ?></li>
            <?php endif; ?>
            
            <?php if ($music_details['maqam']): ?>
                <li>المقام: <?= safe_output($music_details['maqam']) ?></li>
            <?php endif; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <?php if ($custom_fields->num_rows > 0): ?>
    <div class="custom-fields">
        <h3>معلومات إضافية:</h3>
        <ul>
            <?php while ($field = $custom_fields->fetch_assoc()): ?>
                <li><strong><?= safe_output($field['field_name']) ?>:</strong> <?= safe_output($field['field_value']) ?></li>
            <?php endwhile; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <?php if ($tags->num_rows > 0): ?>
    <div class="poem-tags">
        <h3>الكلمات الدلالية:</h3>
        <?php while ($tag = $tags->fetch_assoc()): ?>
            <a href="search.php?tag=<?= urlencode($tag['name']) ?>"><?= safe_output($tag['name']) ?></a>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>
</body>
</html>