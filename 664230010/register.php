<?php
require 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];


    if(empty($username)||empty($fullname)||empty($email)||empty($password)||empty($confirm_password)){
        $error[] = "กรุณากรอกข้อมูลให้ครับทุกช่อง";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error[] = "อีเมลไม่ถูกต้อง";
    } elseif ($password !== $confirm_password) {
        $error[] = "รหัสผ่ำนไม่ตรงกัน";
    }  else {
        $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username, $email,]);
        if($stmt->rowCount() > 0){
            $error[] = "ชื่อผู้ใช้หรืออีเมลนี้ถูกใช้ไปแล้ว";
        }
    }
    
    if(empty($error)){
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users(username,full_name,email,password, role) VALUES (?,?,?,?, 'member')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username, $fullname, $email, $hashedPassword]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header("Location: login.php?register=success");
        exit();
    }


}

//นำข้อมูลบันทึกลงในฐานข้อมูล

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>register</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" />
    <link href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css" rel="stylesheet" />
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
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    max-width: 450px;
    width: 100%;
    }
    h2 {
    color: #333;
    font-weight: 600;
    text-align: center;
    margin-bottom: 30px;
    }
    .form-label {
    font-weight: 500;
    color: #555;
    margin-bottom: 5px;
    }
    .form-control {
    border-radius: 8px;
    border: 1px solid #ddd;
    padding: 10px 15px;
    margin-bottom: 15px;
    box-shadow: none;
    transition: border-color 0.3s;
    }
    .form-control:focus {
    border-color: #3f51b5;
    box-shadow: 0 0 0 0.2rem rgba(63, 81, 181, 0.25);
    }
    .btn-primary {
    width: 100%;
    background-color: #3f51b5;
    border: none;
    padding: 12px;
    font-size: 16px;
    font-weight: bold;
    border-radius: 8px;
    transition: background-color 0.3s ease, transform 0.2s ease;
    }
    .btn-primary:hover {
    background-color: #303f9f;
    transform: translateY(-2px);
    }
    .btn-link {
    text-decoration: none;
    font-size: 14px;
    color: #6c757d;
    display: block;
    text-align: center;
    margin-top: 20px;
    transition: color 0.3s ease;
    }
    .btn-link:hover {
    color: #333;
    }
    .alert {
    margin-bottom: 20px;
    padding: 15px;
    border-radius: 8px;
    text-align: center;
    }
    .alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border-color: #f5c6cb;
    }
    .alert-success {
    background-color: #d4edda;
    color: #155724;
    border-color: #c3e6cb;
    }
</style>
</head>

<body>
    
    <div class="container mt-5">
        <h2>สมัครสมาชิก</h2>

        <?php if (!empty($error)): // ถ ้ำมีข ้อผิดพลำด ให้แสดงข ้อควำม ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($error as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>

                        <!-- ใช ้ htmlspecialchars เพื่อป้องกัน XSS -->
                        <!-- < ? = คือ short echo tag ?> -->
                        <!-- ถ ้ำเขียนเต็ม จะได ้แบบด ้ำนล่ำง -->
                        <?php //echo "<li>" . htmlspecialchars($e) . "</li>"; ?>

                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="" method="post">
            <div>
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" id="username" class="form-control" placeholder="ชื่อผู้ใช้" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" required>
            </div>
            <div>
                <label for="fullname" class="form-label">Full Name</label>
                <input type="text" name="fullname" id="fullname" class="form-control" placeholder="ชื่อเต็ม" value="<?= isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : '' ?>" required>
            </div>
            <div>
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
            </div>
            <div>
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="รหัสผ่าน" required>
            </div>
            <div>
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="ยืนยันรหัสผ่าน" required>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">สมัครสมาชิก</button>
                <a href="login.php" class="btn btn-link">เข้าสู่ระบบ</a>
            </div>
        </form>
    </div>
</body>

</html>

