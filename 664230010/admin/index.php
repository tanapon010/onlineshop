<?php
require_once '../Config.php';
require_once 'authadmin.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แผงควบคุมผู้ดูแลระบบ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #B0B0B0;
            font-family: 'Arial', sans-serif;
        }
        .container-main {
            max-width: 900px;
            margin: 50px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            color: #343a40;
        }
        .header h2 {
            font-weight: 700;
            font-size: 2.5rem;
        }
        .header p {
            font-size: 1.2rem;
            color: #6c757d;
        }
        .card-link {
            text-decoration: none;
            color: inherit;
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            display: block;
        }
        .card-link:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        .card-custom {
            border: none;
            border-radius: 10px;
            background-color: #e9ecef;
            padding: 20px;
            text-align: center;
        }
        .card-custom h5 {
            font-size: 1.5rem;
            margin-bottom: 0;
            font-weight: 600;
        }
        .btn-logout {
            width: 100%;
            margin-top: 30px;
            border-radius: 5px;
            padding: 10px;
        }
    </style>
</head>
<body>
    <div class="container container-main">
        <header class="header">
            <h2>แผงควบคุมผู้ดูแลระบบ</h2>
            <p>ยินดีต้อนรับ, <?= htmlspecialchars($_SESSION['user_id']) ?></p>
        </header>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <a href="user.php" class="card-link">
                    <div class="card-custom bg-warning">
                        <h5><i class="bi bi-people-fill me-2"></i>จัดการสมาชิก</h5>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-3">
                <a href="category.php" class="card-link">
                    <div class="card-custom bg-dark text-white">
                        <h5><i class="bi bi-tags-fill me-2"></i>จัดการหมวดหมู่</h5>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-3">
                <a href="products.php" class="card-link">
                    <div class="card-custom bg-primary text-white">
                        <h5><i class="bi bi-box-seam-fill me-2"></i>จัดการสินค้า</h5>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-3">
                <a href="orders.php" class="card-link">
                    <div class="card-custom bg-success text-white">
                        <h5><i class="bi bi-receipt-cutoff me-2"></i>จัดการคำสั่งซื้อ</h5>
                    </div>
                </a>
            </div>
        </div>

        <a href="../logout.php" class="btn btn-secondary btn-lg btn-logout">ออกจากระบบ</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</body>
</html>