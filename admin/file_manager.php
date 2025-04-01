<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!Auth::check()) {
    header("Location: login.php");
    exit();
}

// حذف الملفات
if (isset($_GET['delete']) {
    $file = $_GET['delete'];
    $file_path = "../uploads/" . basename($file);
    
    if (file_exists($file_path) {
        unlink($file_path);
        $_SESSION['message'] = "تم حذف الملف بنجاح";
    }
}

// جلب قائمة الملفات
$upload_dir = "../uploads/";
$files = glob($upload_dir . "*");
?>

<!DOCTYPE html>
<html dir="rtl">
<head>
    <title>إدارة الملفات</title>
    <style>
        .file-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }
        .file-item {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        .file-item img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <h1>إدارة الملفات</h1>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert"><?= $_SESSION['message'] ?></div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    
    <div class="file-list">
        <?php foreach ($files as $file): ?>
            <?php if (is_file($file)): ?>
                <div class="file-item">
                    <?php if (strpos(mime_content_type($file), 'image/') === 0): ?>
                        <img src="<?= str_replace('../', '/', $file) ?>" alt="File">
                    <?php else: ?>
                        <div class="file-icon">📄</div>
                    <?php endif; ?>
                    
                    <div class="file-name"><?= basename($file) ?></div>
                    <div class="file-size"><?= format_size(filesize($file)) ?></div>
                    <div class="file-actions">
                        <a href="?delete=<?= basename($file) ?>" onclick="return confirm('هل أنت متأكد؟')">حذف</a>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</body>
</html>