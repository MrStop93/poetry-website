<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$poet_id = (int)$_GET['id'];

// جلب بيانات الشاعر
$poet = $db->query("SELECT * FROM poets WHERE id = $poet_id")->fetch_assoc();

if (!$poet) {
    header("HTTP/1.0 404 Not Found");
    die("الشاعر غير موجود");
}

// جلب قصائد الشاعر
$poems = $db->query("
    SELECT id, title, publish_year, poetic_meter, rhyme, verses_count
    FROM poems
    WHERE poet_id = $poet_id
    ORDER BY title
");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="assets/css/style.css">
    <title><?= safe_output($poet['name']) ?> - النظام الشعري</title>
    <style>
        .poet-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }
        .poet-image {
            margin-left: 20px;
            border-radius: 50%;
            width: 150px;
            height: 150px;
            object-fit: cover;
        }
        .poet-bio {
            line-height: 1.8;
            color: #555;
        }
        .poems-list {
            list-style: none;
            padding: 0;
        }
        .poem-item {
            margin-bottom: 15px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .poem-meta {
            font-size: 0.9em;
            color: #666;
            margin-top: 5px;
        }
        @media (max-width: 768px) {
            .poet-header {
                flex-direction: column;
                text-align: center;
            }
            .poet-image {
                margin-left: 0;
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="poet-header">
            <?php if ($poet['image']): ?>
                <img src="/uploads/poets/<?= safe_output($poet['image']) ?>" alt="<?= safe_output($poet['name']) ?>" class="poet-image">
            <?php endif; ?>
            
            <div>
                <h1><?= safe_output($poet['name']) ?></h1>
                
                <?php if ($poet['birth_date'] || $poet['country']): ?>
                    <div class="poet-details">
                        <?php if ($poet['birth_date']): ?>
                            <span>تاريخ الميلاد: <?= date('Y-m-d', strtotime($poet['birth_date'])) ?></span>
                        <?php endif; ?>
                        
                        <?php if ($poet['death_date']): ?>
                            <span> - تاريخ الوفاة: <?= date('Y-m-d', strtotime($poet['death_date'])) ?></span>
                        <?php endif; ?>
                        
                        <?php if ($poet['country']): ?>
                            <span> - الجنسية: <?= safe_output($poet['country']) ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($poet['bio']): ?>
            <div class="poet-bio">
                <h2>سيرة الشاعر</h2>
                <?= nl2br(safe_output($poet['bio'])) ?>
            </div>
        <?php endif; ?>
        
        <h2>قصائد الشاعر</h2>
        
        <?php if ($poems->num_rows > 0): ?>
            <ul class="poems-list">
                <?php while ($poem = $poems->fetch_assoc()): ?>
                    <li class="poem-item">
                        <a href="poem.php?id=<?= $poem['id'] ?>"><?= safe_output($poem['title']) ?></a>
                        <div class="poem-meta">
                            <?php if ($poem['publish_year']): ?>
                                <span>سنة النشر: <?= safe_output($poem['publish_year']) ?></span>
                            <?php endif; ?>
                            <?php if ($poem['poetic_meter']): ?>
                                <span> - البحر: <?= safe_output($poem['poetic_meter']) ?></span>
                            <?php endif; ?>
                            <?php if ($poem['rhyme']): ?>
                                <span> - القافية: <?= safe_output($poem['rhyme']) ?></span>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>لا توجد قصائد مسجلة لهذا الشاعر بعد.</p>
        <?php endif; ?>
    </div>
</body>
</html>