<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// -----------------------------
// ลบสินค้า (POST ผ่าน SweetAlert)
// -----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_cart_id'])) {
    $cart_id = $_POST['delete_cart_id'];
    $stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    header("Location: cart.php?deleted=1");
    exit;
}

// -----------------------------
// เพิ่มสินค้าเข้าตะกร้า
// -----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $quantity = max(1, intval($_POST['quantity'] ?? 1));
    
    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($item) {
        // ถ้ามีแล้ว ให้เพิ่มจำนวน
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE cart_id = ?");
        $stmt->execute([$quantity, $item['cart_id']]);
    } else {
        // ถ้ายังไม่มี ให้เพิ่มใหม่
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $quantity]);
    }
    header("Location: cart.php?added=1");
    exit;
}

// -----------------------------
// อัปเดตจำนวนสินค้า (เพิ่ม/ลด)
// -----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart_id'])) {
    $cart_id = $_POST['update_cart_id'];
    $action = $_POST['action']; // increase / decrease

    // ดึงจำนวนเดิม
    $stmt = $conn->prepare("SELECT quantity FROM cart WHERE cart_id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        $quantity = $item['quantity'];
        if ($action === 'increase')
            $quantity++;
        if ($action === 'decrease' && $quantity > 1)
            $quantity--;

        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
        $stmt->execute([$quantity, $cart_id, $user_id]);
    }
    header("Location: cart.php");
    exit;
}

// -----------------------------
// ดึงรายการสินค้าในตะกร้า
// -----------------------------
$stmt = $conn->prepare("SELECT cart.cart_id, cart.quantity, products.product_name, products.price
FROM cart
JOIN products ON cart.product_id = products.product_id
WHERE cart.user_id = ?");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// -----------------------------
// คำนวณราคารวม
// -----------------------------
$total = 0;
foreach ($items as $item) {
    $total += $item['quantity'] * $item['price'];
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ตะกร้าสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .cart-table {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .table thead {
            background-color: #007bff;
            color: #fff;
        }
        .table td, .table th {
            vertical-align: middle;
        }
        .btn-custom {
            width: 150px;
        }
        .btn-danger {
            width: 120px;
        }
        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body class="container mt-4">

    <h2 class="text-center mb-4">ตะกร้าสินค้า</h2>
    <a href="index.php" class="btn btn-secondary mb-3">← กลับไปเลือกสินค้า</a>

    <?php if (count($items) === 0): ?>
        <div class="alert alert-warning">ยังไม่มีสินค้าในตะกร้า</div>
    <?php else: ?>
        <div class="cart-table">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ชื่อสินค้า</th>
                        <th>จำนวน</th>
                        <th>ราคาต่อหน่วย</th>
                        <th>ราคารวม</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['product_name']) ?></td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="update_cart_id" value="<?= $item['cart_id'] ?>">
                                    <input type="hidden" name="action" value="decrease">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">-</button>
                                </form>

                                <span class="mx-2"><?= $item['quantity'] ?></span>

                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="update_cart_id" value="<?= $item['cart_id'] ?>">
                                    <input type="hidden" name="action" value="increase">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">+</button>
                                </form>
                            </td>
                            <td><?= number_format($item['price'], 2) ?></td>
                            <td><?= number_format($item['quantity'] * $item['price'], 2) ?></td>
                            <td>
                                <button class="btn btn-sm btn-danger delete-btn" data-cart-id="<?= $item['cart_id'] ?>">ลบ</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" class="text-end"><strong>รวมทั้งหมด:</strong></td>
                        <td colspan="2"><strong><?= number_format($total, 2) ?> บาท</strong></td>
                    </tr>
                </tbody>
            </table>
            <a href="checkout.php" class="btn btn-success btn-custom">สั่งซื้อสินค้า</a>
        </div>
    <?php endif; ?>

    <!-- ฟอร์มลบสินค้า (ซ่อน) -->
    <form id="delete-form" method="POST" style="display: none;">
        <input type="hidden" name="delete_cart_id" id="delete-cart-id">
    </form>

    <script>
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', () => {
                const cartId = button.getAttribute('data-cart-id');
                Swal.fire({
                    title: 'คุณแน่ใจหรือไม่?',
                    text: "คุณต้องการลบสินค้านี้ออกจากตะกร้าหรือไม่?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'ลบ',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-cart-id').value = cartId;
                        document.getElementById('delete-form').submit();
                    }
                });
            });
        });

        <?php if (isset($_GET['deleted'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'ลบสินค้าแล้ว',
                timer: 1500,
                showConfirmButton: false
            });
        <?php endif; ?>

        <?php if (isset($_GET['added'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'เพิ่มสินค้าในตะกร้าสำเร็จ',
                timer: 1500,
                showConfirmButton: false
            });
        <?php endif; ?>
    </script>

</body>
</html>
