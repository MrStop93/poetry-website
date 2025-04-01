<?php
require_once '../includes/auth.php';

if (!Auth::check()) {
    header("Location: login.php");
    exit();
}

$backup_file = '../backups/' . basename($_GET['file']);

if (file_exists($backup_file)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.basename($backup_file).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($backup_file));
    readfile($backup_file);
    exit;
} else {
    $_SESSION['error'] = "الملف المطلوب غير موجود";
    header("Location: backup.php");
    exit();
}
?>