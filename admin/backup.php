<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!Auth::check()) {
    header("Location: login.php");
    exit();
}

// إنشاء نسخة احتياطية
if (isset($_POST['create_backup'])) {
    $backup_file = '../backups/backup_' . date("Y-m-d_H-i-s") . '.sql';
    
    $command = "mysqldump --user=" . DB_USER . " --password=" . DB_PASS . " --host=" . DB_HOST . " " . DB_NAME . " > " . $backup_file;
    system($command, $output);
    
    if ($output === 0) {
        $_SESSION['message'] = "تم إنشاء النسخة الاحتياطية بنجاح: " . basename($backup_file);
    } else {
        $_SESSION['error'] = "فشل في إنشاء النسخة الاحتياطية";
    }
}

// جلب قائمة النسخ الاحتياطية
$backups = glob('../backups/*.sql');
rsort($backups);
?>

<!DOCTYPE html>
<html dir="rtl">
<head>
    <title>النسخ الاحتياطي</title>
</head>
<body>
    <h1>إدارة النسخ الاحتياطية</h1>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert success"><?= $_SESSION['message'] ?></div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert error"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <form method="POST">
        <button type="submit" name="create_backup">إنشاء نسخة احتياطية جديدة</button>
    </form>
    
    <h2>النسخ الاحتياطية المتاحة:</h2>
    <ul>
        <?php foreach ($backups as $backup): ?>
            <li>
                <?= basename($backup) ?> 
                (<?= format_size(filesize($backup)) ?>)
                <a href="download_backup.php?file=<?= basename($backup) ?>">تحميل</a>
                <a href="restore_backup.php?file=<?= basename($backup) ?>" onclick="return confirm('هل أنت متأكد من استعادة هذه النسخة؟')">استعادة</a>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>