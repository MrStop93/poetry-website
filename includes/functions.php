<?php
// حساب القافية التلقائية
function calculateRhyme($poem_text) {
    $lines = explode("\n", $poem_text);
    $last_chars = [];
    
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (!empty($trimmed)) {
            $last_char = mb_substr($trimmed, -1, 1, 'UTF-8');
            if (!in_array($last_char, ['،', '؛', '!', '؟', '.'])) {
                $last_chars[] = $last_char;
            }
        }
    }
    
    if (count(array_unique($last_chars)) === 1) {
        return $last_chars[0];
    }
    
    return "متعددة";
}

// حساب عدد الأبيات
function countVerses($poem_text) {
    return count(array_filter(explode("\n", $poem_text), function($line) {
        return !empty(trim($line));
    }));
}

// إنشاء رابط آمن
function generateSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    
    return $text ?: 'untitled';
}

// تنسيق حجم الملف
function format_size($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        return $bytes . ' bytes';
    } elseif ($bytes == 1) {
        return '1 byte';
    } else {
        return '0 bytes';
    }
}

// تسمية الإحصائيات
function stat_label($key) {
    $labels = [
        'poets' => 'الشعراء',
        'poems' => 'القصائد',
        'artists' => 'الفنانين',
        'composers' => 'الملحنين',
        'musical_poems' => 'قصائد مغناة',
        'tags' => 'كلمات دلالية'
    ];
    return $labels[$key] ?? $key;
}

// إنشاء صورة مصغرة
function create_thumbnail($source_path, $dest_path, $max_width = 200, $max_height = 200) {
    list($orig_width, $orig_height, $type) = getimagesize($source_path);
    
    $ratio = $orig_width / $orig_height;
    
    if ($max_width / $max_height > $ratio) {
        $new_width = $max_height * $ratio;
        $new_height = $max_height;
    } else {
        $new_width = $max_width;
        $new_height = $max_width / $ratio;
    }
    
    $image_p = imagecreatetruecolor($new_width, $new_height);
    
    switch ($type) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($source_path);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($source_path);
            break;
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($source_path);
            break;
        default:
            return false;
    }
    
    imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $orig_width, $orig_height);
    
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($image_p, $dest_path, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($image_p, $dest_path, 9);
            break;
        case IMAGETYPE_GIF:
            imagegif($image_p, $dest_path);
            break;
    }
    
    imagedestroy($image_p);
    imagedestroy($image);
    
    return true;
}

// دالة لتحويل النص إلى slug
function generate_slug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    
    if (empty($text)) {
        return 'n-a';
    }
    
    return $text;
}

// دالة لتحميل الصور بشكل آمن
function upload_image($file, $target_dir, $prefix = '') {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception('نوع الملف غير مسموح به');
    }
    
    if ($file['size'] > $max_size) {
        throw new Exception('حجم الملف كبير جداً');
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $prefix . uniqid() . '.' . $extension;
    $target_path = $target_dir . '/' . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $target_path)) {
        throw new Exception('فشل في تحميل الملف');
    }
    
    // إنشاء صورة مصغرة إذا كان الملف صورة
    if (strpos($file['type'], 'image/') === 0) {
        create_thumbnail($target_path, $target_dir . '/thumbs/' . $filename, 200, 200);
    }
    
    return $filename;
}

// دالة لإنشاء token آمن
function generate_token($length = 32) {
    return bin2hex(random_bytes($length));
}

// دالة لتنظيف المدخلات
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// دالة لتنظيف المخرجات وحماية من XSS
//function safe_output($data) {
//    return htmlspecialchars($data ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
//}

// دالة أخرى لتحويل الأسطر الجديدة إلى <br> (اختياري)
function nl2br_safe($text) {
    return nl2br(safe_output($text));
}

if (!function_exists('safe_output')) {
    function safe_output($data) {
        if (is_array($data)) {
            return array_map('safe_output', $data);
        }
        return htmlspecialchars($data ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
    }
}

// دوال مساعدة إضافية
function format_date($date) {
    return date('Y-m-d', strtotime($date));
}


// دالة لحماية المخرجات من XSS
function safe_output($data) {
    if (is_array($data)) {
        return array_map('safe_output', $data);
    }
    return htmlspecialchars($data ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// دالة لتحميل الصور بشكل آمن
function get_safe_image($path, $default = '/assets/images/default.png') {
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
        return $path;
    }
    return $default;
}

// دالة للبحث الآمن في قاعدة البيانات
function search_query($db, $table, $columns, $query, $limit = 5) {
    $terms = explode(' ', $query);
    $sql = "SELECT * FROM $table WHERE ";
    $params = [];
    $types = '';
    
    foreach ($terms as $term) {
        foreach ($columns as $col) {
            $sql .= "$col LIKE ? OR ";
            $params[] = "%$term%";
            $types .= 's';
        }
    }
    $sql = rtrim($sql, 'OR ') . " LIMIT $limit";
    
    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

?>