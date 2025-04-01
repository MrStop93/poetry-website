<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!Auth::check()) {
    header("Location: login.php");
    exit();
}

// إضافة شاعر جديد
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_poet'])) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        die("Invalid CSRF token");
    }

    $data = [
        'name' => $db->sanitize($_POST['name']),
        'birth_date' => $db->sanitize($_POST['birth_date']),
        'death_date' => $db->sanitize($_POST['death_date']),
        'country' => $db->sanitize($_POST['country']),
        'bio' => $db->sanitize($_POST['bio'])
    ];

    $stmt = $db->prepare("INSERT INTO poets (name, birth_date, death_date, country, bio) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $data['name'], $data['birth_date'], $data['death_date'], $data['country'], $data['bio']);
    $stmt->execute();
}
?>

<!-- واجهة إضافة شاعر -->
<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= Auth::generateCSRFToken() ?>">
    
    <div class="form-group">
        <label>اسم الشاعر:</label>
        <input type="text" name="name" required>
    </div>
    
    <div class="form-group">
        <label>تاريخ الميلاد:</label>
        <input type="date" name="birth_date">
    </div>
    
    <div class="form-group">
        <label>تاريخ الوفاة (إن وجد):</label>
        <input type="date" name="death_date">
    </div>
    
    <div class="form-group">
        <label>الدولة:</label>
        <input type="text" name="country">
    </div>
    
    <div class="form-group">
        <label>السيرة الذاتية:</label>
        <textarea name="bio"></textarea>
    </div>
    
    <div class="form-group">
        <label>صورة الشاعر:</label>
        <input type="file" name="image" accept="image/*">
    </div>
    
    <button type="submit" name="add_poet">حفظ</button>
</form>

<!-- قائمة الشعراء -->
<table>
    <thead>
        <tr>
            <th>الصورة</th>
            <th>الاسم</th>
            <th>الدولة</th>
            <th>الإجراءات</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $poets = $db->query("SELECT * FROM poets ORDER BY name");
        while ($poet = $poets->fetch_assoc()) {
            echo "<tr>
                <td><img src='../uploads/poets/{$poet['image']}' width='50'></td>
                <td>{$poet['name']}</td>
                <td>{$poet['country']}</td>
                <td>
                    <a href='edit_poet.php?id={$poet['id']}'>تعديل</a>
                    <a href='delete_poet.php?id={$poet['id']}' onclick='return confirm(\"هل أنت متأكد؟\")'>حذف</a>
                </td>
            </tr>";
        }
        ?>
    </tbody>
</table>