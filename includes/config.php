<?php
// الإعدادات الأساسية
define('BASE_URL', '127.0.0.1');
define('API_KEY', 'your-secure-api-key-123');
ini_set('display_errors', 0);
error_reporting(0);
session_start();

// إعدادات التطبيق
define('SITE_NAME', 'النظام الشعري');
define('UPLOAD_DIR', __DIR__ . '/../uploads');
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024); // 2MB

// أنواع الملفات المسموح بها
$allowed_mime_types = [
    'image/jpeg',
    'image/png',
    'image/gif'
];

// إعدادات البريد الإلكتروني
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_USER', 'user@example.com');
define('SMTP_PASS', 'password');
define('ADMIN_EMAIL', 'admin@example.com');

// تفعيل وضع التصحيح (يجب تعطيله في الإنتاج)
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// إعدادات الأداء
ini_set('memory_limit', '128M');
ini_set('max_execution_time', 30);
date_default_timezone_set('Asia/Riyadh');

// إعدادات الجلسات
//ini_set('session.gc_maxlifetime', 14400);
//session_set_cookie_params(14400);

?>