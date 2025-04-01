<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!Auth::check()) {
    header("Location: login.php");
    exit();
}

// حفظ إعدادات المظهر
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        die("Invalid CSRF token");
    }

    $theme_data = [
        'primary_color' => $db->sanitize($_POST['primary_color']),
        'secondary_color' => $db->sanitize($_POST['secondary_color']),
        'font_family' => $db->sanitize($_POST['font_family']),
        'logo' => $_FILES['logo']
    ];

    // معالجة تحميل الشعار
    if ($theme_data['logo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/images/';
        $file_name = 'logo_' . time() . '.' . pathinfo($theme_data['logo']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($theme_data['logo']['tmp_name'], $upload_dir . $file_name);
        $theme_data['logo_path'] = $file_name;
    }

    // حفظ الإعدادات في ملف أو قاعدة البيانات
    file_put_contents('../theme_settings.json', json_encode($theme_data));
}
?>

<!-- واجهة إعدادات المظهر -->
<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= Auth::generateCSRFToken() ?>">
    
    <div class="form-group">
        <label>اللون الأساسي:</label>
        <input type="color" name="primary_color" value="#3498db">
    </div>
    
    <div class="form-group">
        <label>اللون الثانوي:</label>
        <input type="color" name="secondary_color" value="#2ecc71">
    </div>
    
    <div class="form-group">
        <label>نوع الخط:</label>
        <select name="font_family">
            <option value="Arial">Arial</option>
            <option value="Tahoma">Tahoma</option>
            <option value="'Traditional Arabic'">الخط العربي</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>شعار الموقع:</label>
        <input type="file" name="logo" accept="image/*">
    </div>
    
    <button type="submit">حفظ التغييرات</button>
</form>