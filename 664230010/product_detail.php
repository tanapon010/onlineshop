<?php
session_start();
require_once 'Config.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$product_id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT p.*, c.category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    WHERE p.product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// ถ้าไม่พบสินค้า
if (!$product) {
    echo "<h3 class='text-danger'>ไม่พบสินค้าที่คุณต้องการ</h3>";
    exit;
}

$isLoggedIn = isset($_SESSION['user_id']);
$img = !empty($product['image']) ? 'product_images/' . rawurlencode($product['image']) : 'product_images/no-image.jpg';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียดสินค้า - <?= htmlspecialchars($product['product_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .product-img {
            width: 100%;
            height: auto;
            max-height: 400px;
            object-fit: contain;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        .back-btn {
            margin-bottom: 20px;
        }
    </style>
</head>

<body class="container mt-4">

    <!-- ปุ่มกลับ -->
    <a href="index.php" class="btn btn-outline-secondary back-btn">
        <i class="bi bi-arrow-left-circle"></i> กลับหน้ารายการสินค้า
    </a>

    <div class="row">
        <!-- รูปภาพสินค้า -->
        <div class="col-md-5">
            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>" class="product-img img-fluid shadow-sm">
        </div>

        <!-- รายละเอียดสินค้า -->
        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-primary"><?= htmlspecialchars($product['product_name']) ?></h2>
                    <p class="text-muted mb-1"><i class="bi bi-tag-fill"></i> หมวดหมู่: <?= htmlspecialchars($product['category_name']) ?></p>

                    <hr>

                    <p class="card-text"><?= nl2br(htmlspecialchars($product['description'])) ?></p>

                    <p class="h5 mt-4"><strong>ราคา:</strong> <?= number_format($product['price'], 2) ?> บาท</p>
                    <p><strong>คงเหลือ:</strong> <?= (int)$product['stock'] ?> ชิ้น</p>

                    <?php if ($isLoggedIn): ?>
                        <form action="cart.php" method="post" class="mt-3">
                            <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                            <label for="quantity" class="form-label">จำนวนที่ต้องการสั่งซื้อ:</label>
                            <div class="input-group mb-3" style="max-width: 200px;">
                                <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?= $product['stock'] ?>" required class="form-control">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-cart-plus-fill"></i> เพิ่มในตะกร้า
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info mt-4">
                            <i class="bi bi-info-circle-fill"></i> กรุณา <a href="login.php" class="alert-link">เข้าสู่ระบบ</a> เพื่อสั่งซื้อสินค้า
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
