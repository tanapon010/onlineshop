<?php
require '../config.php';
require 'authadmin.php';

// เพิ่มหมวดหมู่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name']);
    if ($category_name) {
        $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
        $stmt->execute([$category_name]);
        $_SESSION['success'] = "เพิ่มหมวดหมู่เรียบร้อยแล้ว";
        header("Location: category.php");
        exit;
    }
}

// ลบหมวดหมู่ (มีการตรวจสอบสินค้าที่ใช้หมวดหมู่นี้อยู่ก่อนลบ)
if (isset($_GET['delete'])) {
    $category_id = (int)$_GET['delete'];

    $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $productCount = $stmt->fetchColumn();

    if ($productCount > 0) {
        $_SESSION['error'] = "ไม่สามารถลบหมวดหมู่นี้ได้ เนื่องจากยังมีสินค้าในหมวดหมู่นี้อยู่";
    } else {
        $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
        $stmt->execute([$category_id]);
        $_SESSION['success'] = "ลบหมวดหมู่เรียบร้อยแล้ว";
    }

    header("Location: category.php");
    exit;
}

// แก้ไขหมวดหมู่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $category_id = $_POST['category_id'];
    $category_name = trim($_POST['new_name']);
    if ($category_name) {
        $stmt = $conn->prepare("UPDATE categories SET category_name = ? WHERE category_id = ?");
        $stmt->execute([$category_name, $category_id]);
        $_SESSION['success'] = "แก้ไขชื่อหมวดหมู่เรียบร้อยแล้ว";
        header("Location: category.php");
        exit;
    }
}

// ดึงหมวดหมู่ทั้งหมด
$categories = $conn->query("SELECT * FROM categories ORDER BY category_id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการหมวดหมู่สินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Sarabun', sans-serif;
        }
        .container {
            max-width: 900px;
            margin-top: 40px;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }
        .btn-primary {
            background-color: #28a745;
            border: none;
        }
        .btn-primary:hover {
            background-color: #218838;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .btn-warning:hover {
            background-color: #e0a800;
        }
        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
        .alert {
            border-radius: 8px;
        }
    </style>
</head>
<body>

<div class="container">
    <a href="index.php" class="btn btn-secondary mb-3">← กลับหน้าผู้ดูแล</a>

    <div class="card">
        <div class="card-header bg-success text-white text-center fs-5">
            จัดการหมวดหมู่สินค้า
        </div>
        <div class="card-body">

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger text-center"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success text-center"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <!-- ฟอร์มเพิ่มหมวดหมู่ -->
            <form method="post" class="row g-3 mb-4">
                <div class="col-md-8">
                    <input type="text" name="category_name" class="form-control" placeholder="ชื่อหมวดหมู่ใหม่" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" name="add_category" class="btn btn-primary w-100">เพิ่มหมวดหมู่</button>
                </div>
            </form>

            <!-- รายการหมวดหมู่ -->
            <h5 class="mb-3">รายการหมวดหมู่</h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ชื่อหมวดหมู่</th>
                            <th>แก้ไขชื่อ</th>
                            <th class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td><?= htmlspecialchars($cat['category_name']) ?></td>
                                <td>
                                    <form method="post" class="d-flex">
                                        <input type="hidden" name="category_id" value="<?= $cat['category_id'] ?>">
                                        <input type="text" name="new_name" class="form-control me-2" placeholder="ชื่อใหม่" required>
                                        <button type="submit" name="update_category" class="btn btn-sm btn-warning">แก้ไข</button>
                                    </form>
                                </td>
                                <td class="text-center">
                                    <a href="category.php?delete=<?= $cat['category_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('คุณต้องการลบหมวดหมู่นี้หรือไม่?')">ลบ</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted">ยังไม่มีหมวดหมู่ในระบบ</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

</body>
</html>
