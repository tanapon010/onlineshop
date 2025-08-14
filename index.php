<?php
    session_start();
    
?>







<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>หน้าหลัก</title>
    <style>
        a{
            width: 100%;
            background-color: #00bcd4;
            border: none;
            padding: 10px;
            font-size: 16px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }
    </style>
</head>
<body>
    <h1>ยินดีต้อนรับสู้หน้าหลัก</h1>
    <p>ผู้ใช้: <?= htmlspecialchars($_SESSION['username'])?>(<?= $_SESSION['role']?>) </p>

    <div>
        <a href="logout.php">ออกจากระบบ</a>
    </div>

</body>
</html>