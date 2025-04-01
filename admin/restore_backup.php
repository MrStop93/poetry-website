<?php
require_once '../includes/auth.php';

if (!Auth::check()) {
    header("Location: login.php");
    exit();
}

$backup_file = '../backups/' . basename($_GET['file']);

if (!file_exists($backup_file)) {
    $_SESSION['error'] = "الملف المطلوب غير موجود";
    header("Location: backup.php");
    exit();
}

$command = "mysql --user=" . DB_USER . " --password=" . DB_PASS . " --host=" . DB_HOST . " " . DB_NAME . " < " . $backup_file;
system($command, $output);

if ($output === 0) {
    $_SESSION['message'] = "تم استعادة النسخة الاحتياطية بنجاح";
} else {
    $_SESSION['error'] = "فشل في استعادة النسخة الاحتياطية";
}

header("Location: backup.php");
exit();
?>