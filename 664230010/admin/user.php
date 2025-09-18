<?php
require_once '../config.php';
require_once 'authadmin.php';

// ลบสมาชิก
if (isset($_GET['delete'])) {
    $user_id = (int) $_GET['delete'];
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
            background: #e9ecef;
            font-family: 'Sarabun', sans-serif;
        }

        .container {
            max-width: 1100px;
            margin: 50px auto;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            font-size: 20px;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }

        .btn-sm {
            padding: 0.4rem 0.7rem;
        }

        .btn-warning,
        .btn-danger,
        .btn-secondary {
            border-radius: 5px;
            transition: 0.2s;
        }

        .btn-warning:hover {
            transform: scale(1.05);
            background-color: #e0a800;
            box-shadow: 0 4px 8px rgba(255, 193, 7, 0.3);
        }

        .btn-danger:hover {
            transform: scale(1.05);
            background-color: #c82333;
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.3);
        }

        .table th,
        .table td {
            vertical-align: middle !important;
        }

        .alert {
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="index.php" class="btn btn-secondary mb-4">← กลับหน้าผู้ดูแล</a>

        <div class="card">
            <div class="card-header text-center">จัดการสมาชิก</div>
            <div class="card-body">

                <?php if (count($users) === 0): ?>
                    <div class="alert alert-warning text-center">ยังไม่มีสมาชิกในระบบ</div>
                <?php else: ?>
                    <p class="text-end text-muted">สมาชิกทั้งหมด: <strong><?= count($users) ?></strong> คน</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
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
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="u_id" value="<?= $user['user_id'] ?>">
                                                <button type="button" class="delete-button btn btn-sm btn-danger" data-user-id="<?= $user['user_id'] ?>">ลบ</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        function showDeleteConfirmation(userId) {
            Swal.fire({
                title: 'คุณแน่ใจหรือไม่?',
                text: 'คุณจะไม่สามารถเรียกคืนข้อมูลกลับได้!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ลบ',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
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

        document.querySelectorAll('.delete-button').forEach(button => {
            button.addEventListener('click', () => {
                const userId = button.getAttribute('data-user-id');
                showDeleteConfirmation(userId);
            });
        });
    </script>
</body>

</html>
