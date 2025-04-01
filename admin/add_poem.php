<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!Auth::check()) {
    header("Location: login.php");
    exit();
}


$poets = $db->query("SELECT id, name FROM poets ORDER BY name");
$artists = $db->query("SELECT id, name FROM artists ORDER BY name");
$composers = $db->query("SELECT id, name FROM composers ORDER BY name");
$tags = $db->query("SELECT id, name FROM tags ORDER BY name");

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->begin_transaction();

        // جمع البيانات الأساسية
        $data = [
            'title' => $db->sanitize($_POST['title'] ?? ''),
            'content' => $db->sanitize($_POST['content'] ?? ''),
            'poet_id' => (int)($_POST['poet_id'] ?? 0),
            'poetic_meter' => $db->sanitize($_POST['poetic_meter'] ?? ''),
            'publish_year' => $db->sanitize($_POST['publish_year'] ?? ''),
            'is_musical' => isset($_POST['is_musical']) ? 1 : 0
        ];

        // إدراج القصيدة الأساسية
        $stmt = $db->prepare("INSERT INTO poems (title, content, poet_id, poetic_meter, publish_year, is_musical) 
                             VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssissi", 
            $data['title'],
            $data['content'],
            $data['poet_id'],
            $data['poetic_meter'],
            $data['publish_year'],
            $data['is_musical']
        );
        $stmt->execute();
        //$poem_id = $db->insert_id;

        // معالجة الفنانين
        if (!empty($_POST['artists'])) {
            $stmt = $db->prepare("INSERT INTO poem_artist (poem_id, artist_id) VALUES (?, ?)");
            foreach ($_POST['artists'] as $artist_id) {
                $stmt->bind_param("ii", $poem_id, $artist_id);
                $stmt->execute();
            }
        }

        // معالجة الملحنين
        if (!empty($_POST['composers'])) {
            $stmt = $db->prepare("INSERT INTO poem_composer (poem_id, composer_id) VALUES (?, ?)");
            foreach ($_POST['composers'] as $composer_id) {
                $stmt->bind_param("ii", $poem_id, $composer_id);
                $stmt->execute();
            }
        }

        // معالجة الكلمات الدلالية
        if (!empty($_POST['tags'])) {
            $stmt = $db->prepare("INSERT INTO poem_tags (poem_id, tag_id) VALUES (?, ?)");
            foreach ($_POST['tags'] as $tag_id) {
                $stmt->bind_param("ii", $poem_id, $tag_id);
                $stmt->execute();
            }
        }

        $db->commit();
        $_SESSION['success'] = "تمت إضافة القصيدة بنجاح";
        header("Location: manage_poems.php");
        exit();

    } catch (Exception $e) {
        $db->rollback();
        $error = "حدث خطأ أثناء الإضافة: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة قصيدة جديدة - لوحة التحكم</title>
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
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        textarea {
            min-height: 200px;
        }
        .multi-select {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 4px;
            max-height: 150px;
            overflow-y: auto;
        }
        .btn {
            padding: 10px 15px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            border: none;
            cursor: pointer;
        }
        .btn-primary {
            background: #3498db;
            color: white;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>إضافة قصيدة جديدة</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>
        
        <div class="card">
            <form method="POST">
                <div class="form-group">
                    <label for="title">عنوان القصيدة:</label>
                    <input type="text" id="title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="content">نص القصيدة:</label>
                    <textarea id="content" name="content" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="poet_id">الشاعر:</label>
                    <select id="poet_id" name="poet_id" required>
                        <option value="">اختر شاعراً</option>
                        <?php while ($poet = $poets->fetch_assoc()): ?>
                            <option value="<?= $poet['id'] ?>"><?= safe_output($poet['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="poetic_meter">البحر الشعري:</label>
                    <input type="text" id="poetic_meter" name="poetic_meter">
                </div>
                
                <div class="form-group">
                    <label for="publish_year">سنة النشر:</label>
                    <input type="number" id="publish_year" name="publish_year" min="1000" max="<?= date('Y') ?>">
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_musical" id="is_musical"> 
                        قصيدة مغناة
                    </label>
                </div>
                
                <div id="musical-fields" style="display: none;">
                    <div class="form-group">
                        <label>الفنانين:</label>
                        <div class="multi-select">
                            <?php while ($artist = $artists->fetch_assoc()): ?>
                                <label style="display: block;">
                                    <input type="checkbox" name="artists[]" value="<?= $artist['id'] ?>"> 
                                    <?= safe_output($artist['name']) ?>
                                </label>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>الملحنين:</label>
                        <div class="multi-select">
                            <?php while ($composer = $composers->fetch_assoc()): ?>
                                <label style="display: block;">
                                    <input type="checkbox" name="composers[]" value="<?= $composer['id'] ?>"> 
                                    <?= safe_output($composer['name']) ?>
                                </label>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>الكلمات الدلالية:</label>
                    <div class="multi-select">
                        <?php while ($tag = $tags->fetch_assoc()): ?>
                            <label style="display: block;">
                                <input type="checkbox" name="tags[]" value="<?= $tag['id'] ?>"> 
                                <?= safe_output($tag['name']) ?>
                            </label>
                        <?php endwhile; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">حفظ القصيدة</button>
                    <a href="manage_poems.php" class="btn btn-secondary">إلغاء</a>
                </div>
            </form>
        </div>
    </div>

    <script>
    // إظهار/إخفاء حقول القصيدة المغناة
    document.getElementById('is_musical').addEventListener('change', function() {
        document.getElementById('musical-fields').style.display = 
            this.checked ? 'block' : 'none';
    });
    </script>
</body>
</html>