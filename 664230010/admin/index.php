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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Sarabun', sans-serif;
        }

        .container-main {
            max-width: 960px;
            margin: 50px auto;
            padding: 40px;
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header h2 {
            font-weight: 700;
            font-size: 2.5rem;
            color: #343a40;
        }

        .header p {
            font-size: 1.1rem;
            color: #6c757d;
        }

        .card-link {
            text-decoration: none;
            color: inherit;
        }

        .card-custom {
            border: none;
            border-radius: 12px;
            padding: 30px 20px;
            text-align: center;
            transition: all 0.3s ease-in-out;
            height: 100%;
        }

        .card-custom:hover {
            transform: translateY(-6px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .card-custom i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            display: block;
        }

        .card-custom h5 {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .btn-logout {
            width: 100%;
            margin-top: 30px;
            padding: 12px;
            font-size: 1rem;
            border-radius: 8px;
        }

        @media (max-width: 767px) {
            .card-custom {
                padding: 20px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container container-main">
        <header class="header">
            <h2>แผงควบคุมผู้ดูแลระบบ</h2>
            <p>ยินดีต้อนรับ, ผู้ดูแลหมายเลข <?= htmlspecialchars($_SESSION['user_id']) ?></p>
        </header>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <a href="user.php" class="card-link">
                    <div class="card-custom bg-warning text-dark">
                        <i class="bi bi-people-fill"></i>
                        <h5>จัดการสมาชิก</h5>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-3">
                <a href="category.php" class="card-link">
                    <div class="card-custom bg-secondary text-white">
                        <i class="bi bi-tags-fill"></i>
                        <h5>จัดการหมวดหมู่</h5>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-3">
                <a href="products.php" class="card-link">
                    <div class="card-custom bg-primary text-white">
                        <i class="bi bi-box-seam-fill"></i>
                        <h5>จัดการสินค้า</h5>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-3">
                <a href="orders.php" class="card-link">
                    <div class="card-custom bg-success text-white">
                        <i class="bi bi-receipt-cutoff"></i>
                        <h5>จัดการคำสั่งซื้อ</h5>
                    </div>
                </a>
            </div>
        </div>

        <a href="../logout.php" class="btn btn-danger btn-logout mt-4">
            <i class="bi bi-box-arrow-right me-2"></i> ออกจากระบบ
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
