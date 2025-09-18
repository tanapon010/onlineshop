<?php
require '../config.php';
require 'authadmin.php';

if (!isset($_GET['id'])) {
    header("Location: user.php");
    exit;
}

$user_id = (int)$_GET['id'];

// ดึงข้อมูลสมาชิก
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? AND role = 'member'");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<h3 class='text-danger text-center mt-5'>ไม่พบสมาชิก</h3>";
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);

    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($username === '' || $email === '') {
        $error = "กรุณากรอกข้อมูลให้ครบถ้วน";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "รูปแบบอีเมลไม่ถูกต้อง";
    }

    if (!$error) {
        $chk = $conn->prepare("SELECT 1 FROM users WHERE (username = ? OR email = ?) AND user_id != ?");
        $chk->execute([$username, $email, $user_id]);
        if ($chk->fetch()) {
            $error = "ชื่อผู้ใช้หรืออีเมลนี้มีอยู่แล้วในระบบ";
        }
    }

    $updatePassword = false;
    $hashed = null;

    if (!$error && ($password !== '' || $confirm !== '')) {
        if (strlen($password) < 6) {
            $error = "รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร";
        } elseif ($password !== $confirm) {
            $error = "รหัสผ่านใหม่และยืนยันรหัสผ่านไม่ตรงกัน";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $updatePassword = true;
        }
    }

    if (!$error) {
        if ($updatePassword) {
            $sql = "UPDATE users SET username = ?, full_name = ?, email = ?, password = ? WHERE user_id = ?";
            $args = [$username, $full_name, $email, $hashed, $user_id];
        } else {
            $sql = "UPDATE users SET username = ?, full_name = ?, email = ? WHERE user_id = ?";
            $args = [$username, $full_name, $email, $user_id];
        }

        $upd = $conn->prepare($sql);
        $upd->execute($args);
        header("Location: user.php");
        exit;
    }

    // แสดงค่าที่กรอกไว้ หาก error
    $user['username'] = $username;
    $user['full_name'] = $full_name;
    $user['email'] = $email;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f7fa;
            font-family: 'Sarabun', sans-serif;
        }
        .container {
            max-width: 720px;
            margin-top: 50px;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        label {
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="container">
    <a href="user.php" class="btn btn-secondary mb-3">← กลับหน้ารายชื่อสมาชิก</a>

    <div class="card">
        <div class="card-header bg-primary text-white text-center fs-5">
            แก้ไขข้อมูลสมาชิก
        </div>
        <div class="card-body">

            <?php if (isset($error)): ?>
                <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post" class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">ชื่อผู้ใช้</label>
                    <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($user['username']) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">ชื่อ - นามสกุล</label>
                    <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>">
                </div>

                <div class="col-md-12">
                    <label class="form-label">อีเมล</label>
                    <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($user['email']) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">รหัสผ่านใหม่ <small class="text-muted">(ถ้าไม่เปลี่ยนให้เว้นว่าง)</small></label>
                    <input type="password" name="password" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
                    <input type="password" name="confirm_password" class="form-control">
                </div>

                <div class="col-12 text-center mt-3">
                    <button type="submit" class="btn btn-primary px-4">💾 บันทึกการแก้ไข</button>
                </div>

            </form>
        </div>
    </div>
</div>

</body>
</html>
