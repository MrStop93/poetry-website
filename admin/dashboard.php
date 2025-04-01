<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!Auth::check()) {
    header("Location: login.php");
    exit();
}

// التحقق من مدة الجلسة (30 دقيقة)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 1800)) {
    Auth::logout();
    header("Location: login.php");
    exit();
}

// تجديد وقت الجلسة مع كل طلب
$_SESSION['login_time'] = time();

// جلب الإحصائيات الأساسية
$stats = [
    'poets' => $db->query("SELECT COUNT(*) FROM poets")->fetch_row()[0],
    'poems' => $db->query("SELECT COUNT(*) FROM poems")->fetch_row()[0],
    'artists' => $db->query("SELECT COUNT(*) FROM artists")->fetch_row()[0]
];
?>

<!DOCTYPE html>
<html dir="rtl">
<head>
    <title>لوحة التحكم</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f5f5f5;
        }
        .sidebar {
            background: var(--primary-color);
            color: white;
            width: 250px;
            height: 100vh;
            position: fixed;
            padding: 20px 0;
        }
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .sidebar-menu li a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px 20px;
            transition: background 0.3s;
        }
        .sidebar-menu li a:hover {
            background: rgba(255,255,255,0.1);
        }
        .main-content {
            margin-right: 250px;
            padding: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 5px rgba(0,0,0,0.05);
            text-align: center;
        }
        .stat-card h3 {
            margin-top: 0;
            color: #666;
        }
        .stat-card .count {
            font-size: 28px;
            font-weight: bold;
            color: var(--primary-color);
        }
        .header {
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .user-info {
            display: flex;
            align-items: center;
        }
        .logout-btn {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>النظام الشعري</h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">الرئيسية</a></li>
            <li><a href="manage_poets.php">إدارة الشعراء</a></li>
            <li><a href="manage_poems.php">إدارة القصائد</a></li>
            <li><a href="manage_artists.php">إدارة الفنانين</a></li>
            <li><a href="manage_composers.php">إدارة الملحنين</a></li>
            <li><a href="theme_settings.php">إعدادات المظهر</a></li>
            <li><a href="stats.php">الإحصائيات</a></li>
            <li><a href="backup.php">النسخ الاحتياطي</a></li>
            <li><a href="file_manager.php">مدير الملفات</a></li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="header">
            <h1>لوحة التحكم</h1>
            <div class="user-info">
                <span>مرحباً، <?= $_SESSION['admin_username'] ?? 'مدير' ?></span>
                <a href="logout.php" class="logout-btn">تسجيل الخروج</a>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>عدد الشعراء</h3>
                <div class="count"><?= $stats['poets'] ?></div>
            </div>
            <div class="stat-card">
                <h3>عدد القصائد</h3>
                <div class="count"><?= $stats['poems'] ?></div>
            </div>
            <div class="stat-card">
                <h3>عدد الفنانين</h3>
                <div class="count"><?= $stats['artists'] ?></div>
            </div>
        </div>
        
        <div class="recent-activity">
            <h2>آخر النشاطات</h2>
            <!-- يمكنك إضافة سجل النشاطات هنا -->
        </div>
    </div>
</body>
</html>