<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!Auth::check()) {
    header("Location: login.php");
    exit();
}

// معالجة حذف القصيدة
if (isset($_GET['delete'])) {
    $poem_id = (int)$_GET['delete'];
    try {
        $db->begin_transaction();
        
        // حذف الروابط أولاً
        $db->query("DELETE FROM poem_tags WHERE poem_id = $poem_id");
        $db->query("DELETE FROM poem_artist WHERE poem_id = $poem_id");
        $db->query("DELETE FROM poem_composer WHERE poem_id = $poem_id");
        
        // ثم حذف القصيدة
        $db->query("DELETE FROM poems WHERE id = $poem_id");
        
        $db->commit();
        $_SESSION['success'] = "تم حذف القصيدة بنجاح";
    } catch (Exception $e) {
        $db->rollback();
        $_SESSION['error'] = "فشل في حذف القصيدة: " . $e->getMessage();
    }
    header("Location: manage_poems.php");
    exit();
}

// جلب جميع القصائد مع معلومات الشعراء
$poems = $db->query("
    SELECT p.*, pt.name AS poet_name 
    FROM poems p
    LEFT JOIN poets pt ON p.poet_id = pt.id
    ORDER BY p.created_at DESC
");

// جلب الشعراء للقائمة المنسدلة
$poets = $db->query("SELECT id, name FROM poets ORDER BY name");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة القصائد - لوحة التحكم</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 15px;
            text-align: right;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-edit {
            background: #3498db;
            color: white;
        }
        .btn-delete {
            background: #e74c3c;
            color: white;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>إدارة القصائد</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>قائمة القصائد</h2>
                <a href="add_poem.php" class="btn btn-edit">إضافة قصيدة جديدة</a>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>العنوان</th>
                        <th>الشاعر</th>
                        <th>البحر الشعري</th>
                        <th>التاريخ</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($poem = $poems->fetch_assoc()): ?>
                    <tr>
                        <td><?= $poem['id'] ?></td>
                        <td><?= safe_output($poem['title']) ?></td>
                        <td><?= $poem['poet_name'] ?? 'غير معروف' ?></td>
                        <td><?= safe_output($poem['poetic_meter'] ?? '') ?></td>
                        <td><?= date('Y-m-d', strtotime($poem['created_at'])) ?></td>
                        <td>
                            <a href="edit_poem.php?id=<?= $poem['id'] ?>" class="btn btn-edit">تعديل</a>
                            <a href="manage_poems.php?delete=<?= $poem['id'] ?>" 
                               class="btn btn-delete" 
                               onclick="return confirm('هل أنت متأكد من حذف هذه القصيدة؟')">حذف</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>