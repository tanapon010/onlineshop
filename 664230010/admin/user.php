<?php

require_once '../config.php';
require_once 'authadmin.php'; // ตรวจสอบสิทธิ์แอดมิน

// ลบสมาชิก
if (isset($_GET['delete'])) {
    $user_id = (int) $_GET['delete'];

    // ป้องกันไม่ให้ลบตัวเอง
    if ($user_id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND role = 'member'");
        $stmt->execute([$user_id]);
    }

    header("Location: users.php");
    exit;
}

// ดึงข้อมูลสมาชิก
$stmt = $conn->prepare("SELECT * FROM users WHERE role = 'member' ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
        background: #B0B0B0;
        font-family: 'Sarabun', sans-serif;
        color: #343a40;
        }

        .container {
            background-color: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
            margin-bottom: 50px;
        }

        h2 {
            font-weight: 600;
            margin-bottom: 25px;
            text-align: center;
        }

        .table-container {
            background-color: #f8f9fa;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .table th, .table td {
            vertical-align: middle;
        }

        .table thead th {
            background-color: #e9ecef;
            font-weight: 500;
        }

        .table-bordered {
            border-radius: 10px;
            overflow: hidden;
            border: none;
        }

        .table-bordered td, .table-bordered th {
            border-color: #dee2e6;
        }

        .btn-sm {
            padding: 0.3rem 0.6rem;
        }

        .btn-warning, .btn-danger, .btn-secondary {
            border-radius: 5px;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .btn-warning:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(255, 193, 7, 0.4);
        }

        .btn-danger:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(220, 53, 69, 0.4);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.2);
        }

    </style>

</head>

<body>
<div class="container">
<h2>จัดการสมาชิก</h2>
<a href="index.php" class="btn btn-secondary mb-3">← กลับหน้าผู้ดูแล</a>

<?php if (count($users) === 0): ?>
    <div class="alert alert-warning text-center">ยังไม่มีสมาชิกในระบบ</div>
<?php else: ?>
    <div class="table-container">
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>ชื่อผู้ใช้</th>
                    <th>ชื่อ - นามสกุล</th>
                    <th>อีเมล</th>
                    <th>วันที่สมัคร</th>
                    <th class="text-center">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['full_name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['created_at']) ?></td>
                        <td class="text-center">
                            <a href="edit_user.php?id=<?= $user['user_id'] ?>" class="btn btn-sm btn-warning">แก้ไข</a>
                            <form action="delUser_Sweet.php" method="POST" style="display:inline;">
                                <input type="hidden" name="u_id" value="<?php echo $user['user_id']; ?>">
                                <button type="button" class="delete-button btn btn-danger btn-sm" data-user-id="<?php echo $user['user_id']; ?>">ลบ</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
        // ฟังก์ชันสำหรับแสดงกล่องยืนยัน SweetAlert2
        function showDeleteConfirmation(userId) {
        Swal.fire({
        title: 'คุณแน่ใจหรือไม่?',
        text: 'คุณจะไม่สามารถเรียกคืนข้อมูลกลับได้!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก',
        }).then((result) => {
        if (result.isConfirmed) {
        // หากผู้ใช้ยืนยัน ให้ส่งค่าฟอร์มไปยัง delUser_Sweet.php เพื่อลบข้อมูล
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'delUser_Sweet.php';
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'u_id';
        input.value = userId;
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
        }
        });
        }
        // แนบตัวตรวจจับเหตุการณ์คลิกกับปุ่มลบทั้งหมดที่มีคลาส delete-button
        const deleteButtons = document.querySelectorAll('.delete-button');
        deleteButtons.forEach((button) => {
        button.addEventListener('click', () => {
        const userId = button.getAttribute('data-user-id');
        showDeleteConfirmation(userId);
        });
        });
    </script>

</body>

</html>