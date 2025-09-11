<?php
require '../config.php'; // TODO: เชอื่ มตอ่ ฐำนขอ้ มลู ดว้ย PDO
require 'authadmin.php';// TODO: กำรด์ สทิ ธิ์(Admin Guard)
// แนวทำง: ถ ้ำ !isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' -> redirect ไป ../login.php แล้ว exit;
// เพมิ่ สนิคำ้ใหม่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
$name = trim($_POST['product_name']);
$description = trim($_POST['description']);
$price = floatval($_POST['price']); // floatval() ใชแปลงเป็น ้ float
$stock = intval($_POST['stock']); // intval() ใชแ้ปลงเป็น integer
$category_id = intval($_POST['category_id']);

// ค่ำที่ได ้จำกฟอร์มเป็น string เสมอ

if ($name && $price > 0) { // ตรวจสอบชอื่ และรำคำสนิ คำ้
$stmt = $conn->prepare("INSERT INTO products (product_name, description, price, stock, category_id) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$name, $description, $price, $stock, $category_id]);
header("Location: products.php");
exit;
}
// ถ ้ำเขียนให ้อ่ำนง่ำยขึ้น สำมำรถเขียนแบบด ้ำนล่ำง
// if (!empty($name) && $price > 0) {
// // ผำ่ นเงอื่ นไข: มชี อื่ สนิคำ้ และ รำคำมำกกวำ่ 0
// }
}
// ลบสนิ คำ้
if (isset($_GET['delete'])) {
$product_id = $_GET['delete'];
$stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
header("Location: products.php");
exit;
}
// ดงึรำยกำรสนิคำ้
$stmt = $conn->query("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id ORDER BY p.created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
// ดึงหมวดหมู่
$categories = $conn->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet">
</head>
    <body class="container mt-4">
        <h2>จัดการสินค้า</h2>
        <a href="index.php" class="btn btn-secondary mb-3">← กลับหน้าผู้ดูแล</a>
        <!-- ฟอรม์ เพมิ่ สนิคำ้ใหม่ -->
        <form method="post" class="row g-3 mb-4">
        <h5>เพิ่มสินค้าใหม่</h5>
        <div class="col-md-4">
            <input type="text" name="product_name" class="form-control" placeholder="ชื้อสินค้า" required>
        </div>
        <div class="col-md-2">
            <input type="number" step="0.01" name="price" class="form-control" placeholder="ราคา" required>
        </div>
        <div class="col-md-2">
            <input type="number" name="stock" class="form-control" placeholder="จำนวน" required>
        </div>
        <div class="col-md-2">
            <select name="category_id" class="form-select" required>
                <option value="">เลือกหมวดหมู่</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name'])?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <textarea name="description" class="form-control" placeholder="รายละเอียดสินค้า" rows="2"></textarea>
        </div>
        <div class="col-12">
            <button type="submit" name="add_product" class="btn btn-primary">เพิ่มสินค้า</button>
        </div>
        </form>
        <!-- แสดงรำยกำรสนิคำ้ , แก ้ไข , ลบ -->
        <h5>รายการสินค้า</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
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
                    <td><?= htmlspecialchars($p['product_name']) ?></td>
                    <td><?= htmlspecialchars($p['description']) ?></td>
                    <td><?= number_format($p['price'], 2) ?> บาท</td>
                    <td><?= $p['stock'] ?></td>
                    <td>
                        <a href="products.php?delete=<?= $p['product_id'] ?>" class="btn btn-sm btn-danger"
                        onclick="return confirm('ยืนยันการลบสินค้านี้?')">ลบ</a>
                        <a href="edit_product.php?id=<?= $p['product_id'] ?>" class="btn btn-sm btn-warning">แก้ไข</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </body>
</html>
