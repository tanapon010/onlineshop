<?php
require '../config.php'; // TODO-1: เชอื่ มตอ่ ฐำนขอ้ มลู ดว้ย PDO
require 'authadmin.php'; // TODO-2: กำรด์ สทิ ธิ์(Admin Guard)
// TODO-3: ตรวจว่ำมีพำรำมิเตอร์ id มำจริงไหม (ผ่ำน GET)
// แนวทำง: ถ ้ำไม่มี -> redirect ไป user.php
if (!isset($_GET['id'])) {
header("Location: user.php");
exit;
}
// TODO-4: ดึงค่ำ id และ "แคสต์เป็น int" เพื่อควำมปลอดภัย
$user_id = (int)$_GET['id'];
// ดงึขอ้ มลู สมำชกิทจี่ ะถกู แกไ้ข
/*
TODO-5: เตรียม/รัน SELECT (เฉพำะ role = 'member')
SQL แนะน ำ:
SELECT * FROM users WHERE user_id = ? AND role = 'member'
- ใช ้prepare + execute([$user_id])
- fetch(PDO::FETCH_ASSOC) แล้วเก็บใน $user
*/
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? AND role = 'member'");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
// TODO-6: ถ ้ำไม่พบข ้อมูล -> แสดงข ้อควำมและ exit;
if (!$user) {
    echo "<h3>ไม่พบสมาชิก</h3>";
    exit;
}
// ========== เมื่อผู้ใช้กด Submit ฟอร์ม ==========
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // TODO-7: รับค่ำ POST + trim
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);

    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // TODO-8: ตรวจควำมครบถ ้วน และตรวจรูปแบบ email

    if ($username === '' || $email === '') {
        $error = "กรุณากรอกข้อมูลให้ครบถ้วน";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "รูปแบบอีเมลไม่ถูกต้อง";
    }
    // TODO-9: ถ ้ำ validate ผ่ำน ใหต้ รวจสอบซ ้ำ (username/email ชนกับคนอนื่ ทไี่ มใ่ ชต่ ัวเองหรือไม่)
    // SQL แนะน ำ:
    // SELECT 1 FROM users WHERE (username = ? OR email = ?) AND user_id != ?
    if (!$error) {
        $chk = $conn->prepare("SELECT 1 FROM users WHERE (username = ? OR email = ?) AND user_id != ?");
        $chk->execute([$username, $email, $user_id]);
        if ($chk->fetch()) {
        $error = "ชื่อผู้ใช้หรืออีเมล์นี้มีอยู่แล้วในระบบ";
        }
    }
    // ตรวจรหัสผ่ำน (กรณีต้องกำรเปลี่ยน)
    // เงื่อนไข: อนุญำตให้ปล่อยว่ำงได ้ (คือไม่เปลี่ยนรหัสผ่ำน)
    $updatePassword = false;
    $hashed = null;

    if (!$error && ($password !== '' || $confirm !== '')) {
        // TODO: นศ.เตมิกตกิ ำ เชน่ ยำว >= 6 และรหัสผ่ำนตรงกัน
        if (strlen($password) < 6) {
            $error = "รหัสผ่านต้องยาวอย่างน้อย 6 อักขระ";
        } elseif ($password !== $confirm) {
            $error = "รหัสผ่านใหม่กับยืนยันรหัสผ่ำนไม่ตรงกัน";
        } else {
            // แฮชรหัสผ่ำน
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $updatePassword = true;
        }
    }
    // สร ้ำง SQL UPDATE แบบยืดหยุ่น (ถ ้ำไม่เปลี่ยนรหัสผ่ำนจะไม่แตะ field password)
    if (!$error) {
        if ($updatePassword) {
            // อัปเดตรวมรหัสผ่าน
            $sql = "UPDATE users SET username = ?, full_name = ?, email = ?, password = ? WHERE user_id = ?";
            $args = [$username, $full_name, $email, $hashed, $user_id];
        } else {
            // อัปเดตเฉพาะข้อมูลทั่วไป
            $sql = "UPDATE users SET username = ?, full_name = ?, email = ? WHERE user_id = ?";
            $args = [$username, $full_name, $email, $user_id];
        }
        $upd = $conn->prepare($sql);
        $upd->execute($args);
        header("Location: user.php");
        exit;
    }
    // เขียน update แบบปกต:ิ ถำ้ไมซ่ ้ำ -> ท ำ UPDATE
    // if (!$error) {
    // $upd = $pdo->prepare("UPDATE users SET username = ?, full_name = ?, email = ? WHERE user_id = ?");
    // $upd->execute([$username, $full_name, $email, $user_id]);
    // // TODO-11: redirect กลับหน้ำ users.php หลังอัปเดตส ำเร็จ
    // header("Location: users.php");
    // exit;
    // }

    // OPTIONAL: อัปเดตค่ำ $user เพอื่ สะทอ้ นคำ่ ทชี่ อ่ งฟอรม์ (หำกมีerror)
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
</head>
<body class="container mt-4">
    <h2>แก้ไขข้อมูลสมาชิก</h2>
    <a href="user.php" class="btn btn-secondary mb-3">← กลับหน้ารายชื่อสมาชิก</a>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">ชื้อสมาชิก</label> ้
                <input type="text" name="username" class="form-control" required value="<?=
                htmlspecialchars($user['username']) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">ชื้อ-นามสกุล</label>
                <input type="text" name="full_name" class="form-control" value="<?=
                htmlspecialchars($user['full_name']) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">อีเมล</label>
                <input type="email" name="email" class="form-control" required value="<?=
                htmlspecialchars($user['email']) ?>">
            </div>
            
            <div class="col-md-6">
                <label class="form-label">รหัสผ่านใหม่ <small class="text-muted">(ถ้าไม่ต้องการเปลี่ยนให้เว้นว่าง)</small></label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
                <input type="password" name="confirm_password" class="form-control">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
            </div>
    </form>
</body>
</html>
