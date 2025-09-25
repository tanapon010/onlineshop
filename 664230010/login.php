<?php
    session_start();
    require_once'config.php';

    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOremail = trim($_POST['username_or_email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE (username = ? OR email = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$usernameOremail, $usernameOremail,]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];


            if($user['role'] === 'admin'){
                header("Location: admin/index.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
        }
    }
?>








<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เข้าสู่ระบบ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f0f2f5;
            background: linear-gradient(135deg, #a8c0ff, #3f51b5);
            font-family: 'Arial', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .container {
            background-color: white;
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: 0 auto;
            margin-top: 100px;
        }

        h2 {
            font-weight: 600;
            text-align: center;
            margin-bottom: 30px;
        }

        .form-label {
            font-weight: 500;
        }

        .btn-primary {
            width: 100%;
            background-color: #00bcd4;
            border: none;
            padding: 10px;
            font-size: 16px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #0097a7;
        }

        .btn-link {
            text-decoration: none;
            font-size: 14px;
            color: #6c757d;
            display: block;
            text-align: center;
            margin-top: 20px;
        }

        .btn-link:hover {
            color: #333;
        }

        .alert {
            margin-top: 20px;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .input-group-text {
            background-color: #f1f1f1;
            border-radius: 8px 0 0 8px;
        }

        input.form-control {
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="mt-5">
    <div class="container">
    <h2>เข้าสู่ระบบ</h2>

    <?php if (isset($_GET['register']) && $_GET['register'] === 'success'): ?>
    <div class="alert alert-success">สมัครสมาชิกสำเร็จ กรุณาเข้าสู่ระบบ</div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>


    <form method="post" class="row g-3">
        <div class="col-md-6">
            <label for="username_or_email" class="form-label">ชื่อผู้ใช้หรืออีเมล</label>
            <input type="text" name="username_or_email" id="username_or_email" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label for="password" class="form-label">รหัสผ่าน</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-success">เข้าสู่ระบบ</button>
            <a href="register.php" class="btn btn-link">สมัครสมาชิก</a>
        </div>
    </form>
    </div>
</body>
</html>