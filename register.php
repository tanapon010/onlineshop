<?php
    require 'config.php';
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $username = trim($_POST['username']);
        $fullname = trim($_POST['fullname']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users(username,full_name,email,password, role) VALUES (?,?,?,?, 'admin')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username, $fullname, $email, $hashedPassword]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC); 
    }

    //นำข้อมูลบันทึกลงในฐานข้อมูล

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>register</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"/>
    <link href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css" rel="stylesheet"/>
</head>
<body>
    <div class="container mt-5">
        <h2>สมัครสมาชิก</h2>
        <form action="" method="post">
            <div>
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" id="username" class="form-control" placeholder="ชื่อผู้ใช้" required>
            </div>  
            <div>
                <label for="fullname" class="form-label">Full Name</label>
                <input type="text" name="fullname" id="fullname" class="form-control" placeholder="ชื่อเต็ม" required>
            </div>
            <div>
                <label for="email" class="form-label">Email</label>
                <input type="text" name="email" id="email" class="form-control" placeholder="email" required>
            </div>
            <div>
                <label for="password" class="form-label">Password</label>
                <input type="text" name="password" id="password" class="form-control" placeholder="รหัสผ่าน" required>
            </div>
            <div>
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="text" name="confirm_password" id="confirm_password" class="form-control" placeholder="ยืนยันรหัสผ่าน" required>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">สมัครสมาชิก</button>
                <a href="login.php" class="btn btn-link">เข้าสู่ระบบ</a>
            </div>
        </form>
    </div>
</body>
</html>