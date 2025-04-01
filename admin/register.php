<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// إذا كان المستخدم مسجل دخول بالفعل
if (Auth::check()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);

    // التحقق من البيانات
    if (empty($username) || empty($password) || empty($email)) {
        $error = 'جميع الحقول مطلوبة';
    } elseif ($password !== $confirm_password) {
        $error = 'كلمة المرور غير متطابقة';
    } elseif (strlen($password) < 8) {
        $error = 'كلمة المرور يجب أن تكون 8 أحرف على الأقل';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'البريد الإلكتروني غير صالح';
    } else {
        // التحقق من عدم وجود مستخدم بنفس الاسم
        $stmt = $db->prepare("SELECT id FROM admin_users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'اسم المستخدم أو البريد الإلكتروني موجود مسبقاً';
        } else {
            // تسجيل العضو الجديد
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO admin_users (username, password, email, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("sss", $username, $hashed_password, $email);
            
            if ($stmt->execute()) {
                $success = 'تم إنشاء الحساب بنجاح. يمكنك الآن تسجيل الدخول';
                // إرسال بريد إلكتروني (اختياري)
                // send_welcome_email($email, $username);
            } else {
                $error = 'حدث خطأ أثناء التسجيل. يرجى المحاولة لاحقاً';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل عضوية إدارية جديدة</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .register-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 400px;
            max-width: 90%;
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
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background: #2c3e50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        .error {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }
        .success {
            color: green;
            margin-bottom: 15px;
            text-align: center;
        }
        .login-link {
            text-align: center;
            margin-top: 15px;
        }
        .password-strength {
            height: 5px;
            background: #eee;
            margin-top: 5px;
            border-radius: 3px;
            overflow: hidden;
        }
        .strength-meter {
            height: 100%;
            width: 0%;
            transition: width 0.3s;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2 style="text-align: center;">تسجيل عضوية إدارية جديدة</h2>
        
        <?php if ($error): ?>
            <div class="error"><?= safe_output($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?= safe_output($success) ?></div>
        <?php endif; ?>
        
        <form method="POST" id="register-form">
            <div class="form-group">
                <label for="username">اسم المستخدم:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="email">البريد الإلكتروني:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">كلمة المرور:</label>
                <input type="password" id="password" name="password" required minlength="8">
                <div class="password-strength">
                    <div class="strength-meter" id="strength-meter"></div>
                </div>
                <small>يجب أن تحتوي على 8 أحرف على الأقل</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">تأكيد كلمة المرور:</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
            </div>
            
            <div class="form-group">
                <button type="submit">تسجيل الحساب</button>
            </div>
        </form>
        
        <div class="login-link">
            <p>لديك حساب بالفعل؟ <a href="login.php">تسجيل الدخول</a></p>
        </div>
    </div>

    <script>
    // عرض قوة كلمة المرور
    document.getElementById('password').addEventListener('input', function() {
        const password = this.value;
        const meter = document.getElementById('strength-meter');
        let strength = 0;
        
        if (password.length >= 8) strength += 1;
        if (password.match(/[a-z]/)) strength += 1;
        if (password.match(/[A-Z]/)) strength += 1;
        if (password.match(/[0-9]/)) strength += 1;
        if (password.match(/[^a-zA-Z0-9]/)) strength += 1;
        
        const colors = ['#ff0000', '#ff5a00', '#ffc100', '#a4ff00', '#00ff00'];
        meter.style.width = (strength * 25) + '%';
        meter.style.backgroundColor = colors[strength - 1] || '#eee';
    });

    // التحقق من تطابق كلمات المرور
    document.getElementById('register-form').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('كلمة المرور غير متطابقة!');
        }
    });
    </script>
</body>
</html>