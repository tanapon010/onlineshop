<?php
require '../config.php';
require 'authadmin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_id = intval($_POST['category_id']);

    if ($name && $price > 0) {
        $imageName = null;
        if (!empty($_FILES['product_image']['name'])) {
            $file = $_FILES['product_image'];
            $allowed = ['image/jpeg', 'image/png'];

            if (in_array($file['type'], $allowed)) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $imageName = 'product_' . time() . '.' . $ext;
                $path = __DIR__ . '/../product_images/' . $imageName;
                move_uploaded_file($file['tmp_name'], $path);
            }
        }

        $stmt = $conn->prepare("INSERT INTO products (product_name, description, price, stock, category_id, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $stock, $category_id, $imageName]);
        header("Location: products.php");
        exit;
    }
}

// ลบสนิ คำ้ (ลบไฟลร์ปู ดว้ย)
if (isset($_GET['delete'])) {
$product_id = (int)$_GET['delete']; // แคสต์เป็น int
// 1) ดงึชอื่ ไฟลร์ปู จำก DB ก่อน
$stmt = $conn->prepare("SELECT image FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$imageName = $stmt->fetchColumn(); // null ถ ้ำไม่มีรูป
// 2) ลบใน DB ด ้วย Transaction
    try {
        $conn->beginTransaction();
        $del = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $del->execute([$product_id]);
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollBack();
        // ใส่ flash message หรือ log ได ้ตำมต ้องกำร
        header("Location: products.php");
        exit;
    }
// 3) ลบไฟล์รูปหลัง DB ลบส ำเร็จ
if ($imageName) {
    $baseDir = realpath(__DIR__ . '/../product_images'); // โฟลเดอร์เก็บรูป
    $filePath = realpath($baseDir . '/' . $imageName);
    // กัน path traversal: ต ้องอยู่ใต้ $baseDir จริง ๆ
    if ($filePath && strpos($filePath, $baseDir) === 0 && is_file($filePath)) {
            @unlink($filePath); // ใช ้@ กัน warning ถำ้ลบไมส่ ำเร็จ
    }
}
header("Location: products.php");
exit;
}

$stmt = $conn->query("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id ORDER BY p.created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = $conn->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f1f3f5;
            font-family: 'Roboto', sans-serif;
        }

        .container {
            max-width: 1000px;
            margin-top: 30px;
        }

        .card {
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            font-size: 18px;
        }

        .form-label {
            font-weight: 600;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }

        .btn {
            border-radius: 5px;
        }

        .btn-sm {
            padding: 4px 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="index.php" class="btn btn-secondary mb-3">← กลับหน้าผู้ดูแล</a>

        <!-- เพิ่มสินค้าใหม่ -->
        <div class="card mb-4">
            <div class="card-header">เพิ่มสินค้าใหม่</div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data" class="row g-3">
                    <div class="col-md-6">
                        <label for="product_name" class="form-label">ชื่อสินค้า</label>
                        <input type="text" name="product_name" id="product_name" class="form-control" placeholder="กรอกชื่อสินค้า" required>
                    </div>
                    <div class="col-md-3">
                        <label for="price" class="form-label">ราคา</label>
                        <input type="number" name="price" step="0.01" id="price" class="form-control" placeholder="เช่น 199.00" required>
                    </div>
                    <div class="col-md-3">
                        <label for="stock" class="form-label">จำนวน</label>
                        <input type="number" name="stock" id="stock" class="form-control" placeholder="เช่น 10" required>
                    </div>
                    <div class="col-md-6">
                        <label for="category_id" class="form-label">หมวดหมู่</label>
                        <select name="category_id" id="category_id" class="form-select" required>
                            <option value="">เลือกหมวดหมู่</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="description" class="form-label">รายละเอียดสินค้า</label>
                        <textarea name="description" id="description" class="form-control" rows="3" placeholder="ใส่รายละเอียดสินค้า"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="product_image" class="form-label">รูปสินค้า (JPG, PNG)</label>
                        <input type="file" name="product_image" id="product_image" class="form-control">
                    </div>
                    <div class="col-12">
                        <button type="submit" name="add_product" class="btn btn-success w-100">เพิ่มสินค้า</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- รายการสินค้า -->
        <div class="card">
            <div class="card-header">รายการสินค้า</div>
            <div class="card-body">
                <?php if (count($products) > 0): ?>
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>รูป</th>
                                <th>ชื่อสินค้า</th>
                                <th>หมวดหมู่</th>
                                <th>ราคา</th>
                                <th>คงเหลือ</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $p): ?>
                                <tr>
                                    <td>
                                        <?php if ($p['image']): ?>
                                            <img src="../product_images/<?= htmlspecialchars($p['image']) ?>" class="product-image">
                                        <?php else: ?>
                                            <span class="text-muted">ไม่มีรูป</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($p['product_name']) ?></td>
                                    <td><?= htmlspecialchars($p['category_name']) ?></td>
                                    <td><?= number_format($p['price'], 2) ?> บาท</td>
                                    <td><?= $p['stock'] ?></td>
                                    <td>
                                        <a href="edit_product.php?id=<?= $p['product_id'] ?>" class="btn btn-warning btn-sm">แก้ไข</a>
                                        <a href="products.php?delete=<?= $p['product_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('ยืนยันการลบสินค้านี้?')">ลบ</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted text-center">ยังไม่มีสินค้าในระบบ</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>
