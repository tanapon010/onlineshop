<?php
require '../Config.php';
require 'authadmin.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit;
}

$product_id = intval($_GET['id']);

// ดึงข้อมูลสินค้าที่จะแก้ไข
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    $_SESSION['error'] = "ไม่พบข้อมูลสินค้า";
    header("Location: products.php");
    exit;
}

// ดึงหมวดหมู่
$categories = $conn->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// ถ้ามีการ submit แบบ POST เพื่อแก้ไข
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $category_id = (int)$_POST['category_id'];
    // ค่ำรูปเดิมจำกฟอร์ม
    $oldImage = $_POST['old_image'] ?? null;
    $removeImage = isset($_POST['remove_image']); // true/false
    if ($name && $price > 0) {
        // เตรียมตัวแปรรูปที่จะบันทึก
        $newImageName = $oldImage; // default: คงรูปเดิมไว้
        // 1) ถ ้ำมีติ๊ก "ลบรูปเดิม" → ตั้งให้เป็น null
        if ($removeImage) {
            $newImageName = null;
        }
        // 2) ถ ้ำมีอัปโหลดไฟล์ใหม่ → ตรวจแลว้เซฟไฟลแ์ ละตัง้ชอื่ ใหมท่ ับคำ่
        if (!empty($_FILES['product_image']['name'])) {
            $file = $_FILES['product_image'];
            // ตรวจชนิดไฟล์แบบง่ำย (แนะน ำ: ตรวจ MIME จริงด ้วย finfo)
            $allowed = ['image/jpeg', 'image/png'];
            if (in_array($file['type'], $allowed, true) && $file['error'] === UPLOAD_ERR_OK) {
                // สรำ้งชอื่ ไฟลใ์หม่
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $newImageName = 'product_' . time() . '.' . $ext;
                $uploadDir = realpath(__DIR__ . '/../product_images');
                $destPath = $uploadDir . DIRECTORY_SEPARATOR . $newImageName;
                // ย้ำยไฟล์อัปโหลด
                if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                    // ถ ้ำย้ำยไม่ได ้ อำจตั้ง flash message แลว้คงใชรู้ปเดมิ ไว ้
                    $newImageName = $oldImage;
                }
            }
        }
    // อัปเดต DB
    $sql = "UPDATE products
    SET product_name = ?, description = ?, price = ?, stock = ?, category_id = ?, image = ?
    WHERE product_id = ?";
    $args = [$name, $description, $price, $stock, $category_id, $newImageName, $product_id];
    $stmt = $conn->prepare($sql);
    $stmt->execute($args);
    // ลบไฟล์เก่ำในดิสก์ ถ ้ำ:
    // - มีรูปเดิม ($oldImage) และ
    // - เกดิ กำรเปลยี่ นรปู (อัปโหลดใหมห่ รอื สั่งลบรปู เดมิ)
    if (!empty($oldImage) && $oldImage !== $newImageName) {
        $baseDir = realpath(__DIR__ . '/../product_images');
        $filePath = realpath($baseDir . DIRECTORY_SEPARATOR . $oldImage);
        if ($filePath && strpos($filePath, $baseDir) === 0 && is_file($filePath)) {
            @unlink($filePath);
        }
    }
    header("Location: products.php");
    exit;
}

    } else {
        $error = "กรุณากรอกข้อมูลให้ถูกต้อง";
    }
    ?>

<!-- บล็อก PHP ด้านบนไม่เปลี่ยนจากที่คุณให้มา -->
<!-- เราจะแต่งเฉพาะ HTML/Bootstrap/JS และโค้ดส่วนแสดงผล -->

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        body {
            background: #f2f5f9;
            font-family: 'Sarabun', sans-serif;
        }

        .container {
            max-width: 850px;
            background: white;
            margin-top: 40px;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }

        h2 {
            font-weight: bold;
            color: #343a40;
        }

        label {
            font-weight: 600;
        }

        .form-control, .form-select {
            padding: 10px 12px;
            border-radius: 6px;
        }

        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13,110,253,.25);
        }

        .img-preview {
            max-width: 140px;
            height: auto;
            border: 1px solid #ccc;
            padding: 4px;
            border-radius: 8px;
            background: #f8f9fa;
        }

        .btn {
            padding: 10px 20px;
            font-size: 16px;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
        }

        .btn-secondary {
            background-color: #6c757d;
            border: none;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .form-check-label {
            font-weight: normal;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="mb-4">แก้ไขข้อมูลสินค้า</h2>

    <a href="products.php" class="btn btn-secondary mb-3">← กลับหน้าจัดการสินค้า</a>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="row g-3">

        <div class="col-md-6">
            <label for="product_name" class="form-label">ชื่อสินค้า</label>
            <input type="text" name="product_name" id="product_name" class="form-control" required
                    value="<?= htmlspecialchars($product['product_name']) ?>">
        </div>

        <div class="col-md-6">
            <label for="category_id" class="form-label">หมวดหมู่</label>
            <select name="category_id" id="category_id" class="form-select" required>
                <option value="">เลือกหมวดหมู่</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['category_id'] ?>"
                        <?= $cat['category_id'] == $product['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-6">
            <label for="price" class="form-label">ราคา (บาท)</label>
            <input type="number" step="0.01" name="price" id="price" class="form-control" required
                    value="<?= htmlspecialchars($product['price']) ?>">
        </div>

        <div class="col-md-6">
            <label for="stock" class="form-label">จำนวนในคลัง</label>
            <input type="number" name="stock" id="stock" class="form-control" required
                    value="<?= htmlspecialchars($product['stock']) ?>">
        </div>

        <div class="col-12">
            <label for="description" class="form-label">รายละเอียดสินค้า</label>
            <textarea name="description" id="description" class="form-control" rows="4"><?= htmlspecialchars($product['description']) ?></textarea>
        </div>

        <div class="col-md-6">
            <label class="form-label">รูปปัจจุบัน</label><br>
            <?php if (!empty($product['image'])): ?>
                <img src="../product_images/<?= htmlspecialchars($product['image']) ?>" class="img-preview mb-2">
            <?php else: ?>
                <span class="text-muted">ไม่มีรูป</span>
            <?php endif; ?>
            <input type="hidden" name="old_image" value="<?= htmlspecialchars($product['image']) ?>">
        </div>

        <div class="col-md-6">
            <label class="form-label">อัปโหลดรูปใหม่ (JPG/PNG)</label>
            <input type="file" name="product_image" class="form-control">
            <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image" value="1">
                <label class="form-check-label" for="remove_image">ลบรูปเดิม</label>
            </div>
        </div>

        <div class="col-12 text-center mt-4">
            <button type="submit" name="update_product" class="btn btn-primary me-2">💾 บันทึกการแก้ไข</button>
            <a href="products.php" class="btn btn-secondary">ยกเลิก</a>
        </div>

    </form>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($_SESSION['error'])): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด',
            text: '<?= addslashes($_SESSION['error']) ?>',
        });
    </script>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'สำเร็จ',
            text: '<?= addslashes($_SESSION['success']) ?>',
            timer: 2500,
            timerProgressBar: true,
            showConfirmButton: false,
        });
    </script>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

</body>
</html>

