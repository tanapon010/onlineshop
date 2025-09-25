<?php
session_start();
require_once 'config.php';

// ดึงข้อมูลสินค้า
$stmt = $conn->query("SELECT p.*, c.category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.category_id
            ORDER BY p.created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$isLoggedIn = isset($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    // ตรวจสอบว่ามีตะกร้าหรือยัง
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // เพิ่มสินค้าลงในตะกร้า
    $productId = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];

    // ตรวจสอบว่าสินค้ามีอยู่ในตะกร้าแล้วหรือไม่
    $found = false;
    foreach ($_SESSION['cart'] as &$cartItem) {
        if ($cartItem['product_id'] == $productId) {
            $cartItem['quantity'] += $quantity;
            $found = true;
            break;
        }
    }

    // ถ้าไม่มีสินค้าในตะกร้า, เพิ่มเข้าไปใหม่
    if (!$found) {
        $_SESSION['cart'][] = [
            'product_id' => $productId,
            'quantity' => $quantity
        ];
    }

    // เปลี่ยนเส้นทางไปยังหน้า cart.php
    header('Location: cart.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าแรก | Attack Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Sarabun', sans-serif;
        }
        .product-card {
            border: 1px solid #dee2e6;
            background: #fff;
            border-radius: 8px;
            transition: all 0.3s ease-in-out;
        }
        .product-card:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }
        .product-thumb {
            height: 180px;
            object-fit: cover;
            border-radius: .5rem;
        }
        .product-meta {
            font-size: .75rem;
            color: #6c757d;
            text-transform: uppercase;
        }
        .product-title {
            font-size: 1rem;
            font-weight: 600;
            color: #212529;
            margin-top: .5rem;
        }
        .price {
            font-weight: 700;
            color: #28a745;
        }
        .rating i {
            color: #ffc107;
        }
        .wishlist {
            color: #adb5bd;
        }
        .wishlist:hover {
            color: #dc3545;
        }
        .badge-top-left {
            position: absolute;
            top: .5rem;
            left: .5rem;
            z-index: 2;
            border-radius: .375rem;
        }
    </style>
</head>

<body class="container mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold text-primary">⚔️ Attack Shop</h1>
        <div>
            <?php if ($isLoggedIn): ?>
                <span class="me-3">ยินดีต้อนรับ, <?= htmlspecialchars($_SESSION['full_name']) ?> (<?= $_SESSION['role'] ?>)</span>
                <a href="profile.php" class="btn btn-outline-info">ข้อมูลส่วนตัว</a>
                <a href="orders.php" class="btn btn-outline-warning">ดูประวัติการสั่งซื้อ</a>
                <a href="cart.php" class="btn btn-outline-warning">ดูตะกร้า</a>
                <a href="logout.php" class="btn btn-outline-secondary">ออกจากระบบ</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-success">เข้าสู่ระบบ</a>
                <a href="register.php" class="btn btn-primary">สมัครสมาชิก</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Product List -->
    <div class="row g-4">
        <?php foreach ($products as $p): ?>
            <?php
                $img = !empty($p['image']) ? 'product_images/' . rawurlencode($p['image']) : 'product_images/no-image.jpg';
                $isNew = isset($p['created_at']) && (time() - strtotime($p['created_at']) <= 7 * 24 * 3600);
                $isHot = (int)$p['stock'] > 0 && (int)$p['stock'] < 5;
                $rating = isset($p['rating']) ? (float)$p['rating'] : 4.5;
                $full = floor($rating);
                $half = ($rating - $full) >= 0.5 ? 1 : 0;
            ?>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card product-card h-100 position-relative">
                    <?php if ($isNew): ?>
                        <span class="badge bg-success badge-top-left">NEW</span>
                    <?php elseif ($isHot): ?>
                        <span class="badge bg-danger badge-top-left">HOT</span>
                    <?php endif; ?>
                    
                    <a href="product_detail.php?id=<?= (int)$p['product_id'] ?>" class="p-3 d-block">
                        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p['product_name']) ?>" class="img-fluid w-100 product-thumb">
                    </a>

                    <div class="px-3 pb-3 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="product-meta"><?= htmlspecialchars($p['category_name'] ?? 'ไม่มีหมวดหมู่') ?></div>
                            <button class="btn btn-link p-0 wishlist" title="เพิ่มในรายการโปรด" type="button">
                                <i class="bi bi-heart"></i>
                            </button>
                        </div>

                        <a href="product_detail.php?id=<?= (int)$p['product_id'] ?>" class="text-decoration-none">
                            <div class="product-title"><?= htmlspecialchars($p['product_name']) ?></div>
                        </a>

                        <div class="rating mb-2">
                            <?php for ($i = 0; $i < $full; $i++): ?><i class="bi bi-star-fill"></i><?php endfor; ?>
                            <?php if ($half): ?><i class="bi bi-star-half"></i><?php endif; ?>
                            <?php for ($i = 0; $i < 5 - $full - $half; $i++): ?><i class="bi bi-star"></i><?php endfor; ?>
                        </div>

                        <div class="price mb-3"><?= number_format((float)$p['price'], 2) ?> บาท</div>

                        <div class="mt-auto d-flex gap-2">
                            <?php if ($isLoggedIn): ?>
                                <form action="cart.php" method="post" class="d-inline-flex gap-2">
                                    <input type="hidden" name="product_id" value="<?= (int)$p['product_id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-sm btn-success">เพิ่มในตะกร้า</button>
                                </form>
                            <?php else: ?>
                                <small class="text-muted">เข้าสู่ระบบเพื่อสั่งซื้อ</small>
                            <?php endif; ?>
                            <a href="product_detail.php?id=<?= (int)$p['product_id'] ?>" class="btn btn-sm btn-outline-primary ms-auto">ดูรายละเอียด</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
