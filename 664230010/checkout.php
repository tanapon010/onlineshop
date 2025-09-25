<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$errors = [];

// -----------------------------
// ดึงรายการสินค้าในตะกร้า
// -----------------------------
$stmt = $conn->prepare("SELECT cart.cart_id, cart.quantity, cart.product_id, products.product_name, products.price
                        FROM cart
                        JOIN products ON cart.product_id = products.product_id
                        WHERE cart.user_id = ?");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// คำนวณราคารวม
$total = 0;
foreach ($items as $item) {
    $total += $item['quantity'] * $item['price'];
}

// -----------------------------
// เมื่อมีการ submit ฟอร์ม
// -----------------------------
// เมื่อลูกค้ากดยืนยันคำสั่งซื้อ (method POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['address']); // TODO: ช่องกรอกที่อยู่
    $city = trim($_POST['city']); // TODO: ช่องกรอกจังหวัด
    $postal_code = trim($_POST['postal_code']); // TODO: ช่องกรอกรหัสไปรษณีย์
    $phone = trim($_POST['phone']); // TODO: ช่องกรอกเบอร์โทรศัพท์
    // ตรวจสอบกำรกรอกข้อมูล
    if (empty($address) || empty($city) || empty($postal_code) || empty($phone)) {
        $errors[] = "กรุณากรอกข้อมูลให้ครบถ้วน"; // TODO: ข้อความแจ้งเตือนกรอกไม่ครบ
    }
    if (empty($errors)) {
        // เริ่ม transaction
        $conn->beginTransaction();
        try {
            // บันทึกข้อมูลการสั่งซื้อ
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')");
            $stmt->execute([$user_id, $total]);
            $order_id = $conn->lastInsertId();
            // บันทึกข้อมูลรายการสินค้าใน order_items
            $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($items as $item) {
                $stmtItem->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
                // TODO: product_id, quantity, price
            }
            // บันทึกข้อมูลการจัดส่ง
            $stmt = $conn->prepare("INSERT INTO shipping (order_id, address, city, postal_code, phone) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$order_id, $address, $city, $postal_code, $phone]);
            // ล้างตะกร้าสินค้า
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$user_id]);
            // ยืนยันการบันทึก
            $conn->commit();
            header("Location: orders.php?success=1"); // TODO: หน้าสำหรับแสดงผลคำสั่งซื้อ
            exit;
        } catch (Exception $e) {
            $conn->rollBack();
            $errors[] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สั่งซื้อสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .checkout-box {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        .checkout-header {
            color: #007bff;
            font-weight: 600;
        }
        .checkout-item {
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .checkout-item:last-child {
            border-bottom: none;
        }
        .btn-custom {
            width: 200px;
        }
        .error-message {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .btn-secondary {
            width: 150px;
        }
        .form-label {
            font-weight: 600;
        }
        .form-control {
            border-radius: 8px;
        }
    </style>
</head>
<body class="container py-4">

    <div class="checkout-box mx-auto" style="max-width: 800px;">
        <h2 class="mb-4 checkout-header">ยืนยันการสั่งซื้อ</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger error-message">
                <ul class="mb-0">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <h5 class="checkout-header">รายการสินค้าในตะกร้า</h5>
        <ul class="list-group mb-4">
            <?php foreach ($items as $item): ?>
                <li class="list-group-item checkout-item">
                    <strong><?= htmlspecialchars($item['product_name']) ?></strong> × <?= $item['quantity'] ?> = 
                    <?= number_format($item['quantity'] * $item['price'], 2) ?> บาท
                </li>
            <?php endforeach; ?>
            <li class="list-group-item text-end checkout-item">
                <strong>รวมทั้งหมด: <?= number_format($total, 2) ?> บาท</strong>
            </li>
        </ul>

        <form method="post" class="row g-3">
            <div class="col-md-6">
                <label for="address" class="form-label">ที่อยู่</label>
                <input type="text" name="address" id="address" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label for="city" class="form-label">จังหวัด</label>
                <input type="text" name="city" id="city" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label for="postal_code" class="form-label">รหัสไปรษณีย์</label>
                <input type="text" name="postal_code" id="postal_code" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="phone" class="form-label">เบอร์โทรศัพท์</label>
                <input type="text" name="phone" id="phone" class="form-control" required>
            </div>
            <div class="col-12 d-flex justify-content-between mt-4">
                <a href="cart.php" class="btn btn-secondary">← กลับตะกร้า</a>
                <button type="submit" class="btn btn-success btn-custom">ยืนยันการสั่งซื้อ</button>
            </div>
        </form>
    </div>

</body>
</html>
