<?php
session_start();
require 'config.php';
require 'function.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// เก็บ user_id
$user_id = $_SESSION['user_id']; // ตัวแปรเก็บ user_id

// -----------------------------
// ดึงค่าสั่งซื้อของผู้ใช้
// -----------------------------
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
    <head>
        <meta charset="UTF-8">
        <title>ประวัติการสั่งซื้อ</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background-color: #f8f9fa;
            }
            .container {
                max-width: 1200px;
            }
            .card-header {
                font-size: 1.1rem;
            }
            .card-body {
                font-size: 1rem;
            }
            .list-group-item {
                font-size: 0.95rem;
            }
            .btn-custom {
                width: 150px;
            }
            .alert-success, .alert-warning {
                font-size: 1rem;
            }
        </style>
    </head>
    <body class="container mt-5">
        <h2 class="text-center mb-4">ประวัติการสั่งซื้อ</h2>
        <a href="index.php" class="btn btn-secondary mb-3">← กลับสู่หน้าหลัก</a>

        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">ทำรายการสั่งซื้อเรียบร้อยแล้ว</div>
        <?php endif; ?>

        <?php if (count($orders) === 0): ?>
        <div class="alert alert-warning">คุณยังไม่เคยสั่งซื้อสินค้า</div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <strong>รหัสคำสั่งซื้อ:</strong> #<?= $order['order_id'] ?> |
                    <strong>วันที่:</strong> <?= $order['order_date'] ?> |
                    <strong>สถานะ:</strong> <?= ucfirst($order['status']) ?>
                </div>
                <div class="card-body">
                    <h5>รายละเอียดสินค้า</h5>
                    <ul class="list-group mb-3">
                        <?php foreach (getOrderItems($conn, $order['order_id']) as $item): ?>
                        <li class="list-group-item">
                            <?= htmlspecialchars($item['product_name']) ?> * <?= $item['quantity'] ?> = <?= number_format($item['price'] * $item['quantity'], 2) ?> บาท
                        </li>
                        <?php endforeach; ?>
                    </ul>

                    <p><strong>รวมทั้งหมด:</strong> <?= number_format($order['total_amount'], 2) ?> บาท</p>

                    <?php $shipping = getShippingInfo($conn, $order['order_id']); ?>
                    <?php if ($shipping): ?>
                        <h5>ข้อมูลการจัดส่ง</h5>
                        <p><strong>ที่อยู่:</strong> <?= htmlspecialchars($shipping['address']) ?>, <?= htmlspecialchars($shipping['city']) ?> <?= $shipping['postal_code'] ?></p>
                        <p><strong>สถานะการจัดส่ง:</strong> <?= ucfirst($shipping['shipping_status']) ?></p>
                        <p><strong>เบอร์โทร:</strong> <?= htmlspecialchars($shipping['phone']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </body>
</html>
