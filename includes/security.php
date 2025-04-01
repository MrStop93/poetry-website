<?php
// حماية ضد هجمات CSRF
function verify_csrf_token() {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("طلب غير مصرح به");
    }
}

// حماية ضد هجمات XSS
function xss_clean($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// حماية ضد هجمات SQL Injection
function sql_escape($db, $data) {
    return $db->real_escape_string($data);
}

// تحديد معدل الطلبات
function rate_limit($key, $limit = 5, $interval = 60) {
    $now = time();
    $window = floor($now / $interval);
    
    if (!isset($_SESSION['rate_limit'][$key][$window])) {
        $_SESSION['rate_limit'][$key][$window] = 0;
    }
    
    $_SESSION['rate_limit'][$key][$window]++;
    
    if ($_SESSION['rate_limit'][$key][$window] > $limit) {
        http_response_code(429);
        die("لقد تجاوزت عدد الطلبات المسموح بها");
    }
    
    // تنظيف النوافذ القديمة
    foreach ($_SESSION['rate_limit'][$key] as $w => $count) {
        if ($w < $window - 1) {
            unset($_SESSION['rate_limit'][$key][$w]);
        }
    }
}
?>