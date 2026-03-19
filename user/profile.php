<?php
    $user_id = $_SESSION["user_id"];

$sql = "SELECT username, email, phone, address,created_at FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<div class="mb-3">
    <a class="btn btn-secondary btn-sm" href="dashboard.php">Quay lại</a>
</div>

<div class="card shadow-sm" style="max-width: 600px;">
    <div class="card-header bg-primary">
        <h3 class="page-title mb-0">Hồ sơ cá nhân</h3>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label fw-bold">Tên người dùng</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Email</label>
            <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Số điện thoại</label>
            <input type="tel" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Địa chỉ</label>
            <textarea class="form-control" rows="3" readonly><?= htmlspecialchars($user['address']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Ngày tạo tài khoản</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($user['created_at']) ?>" readonly>
        </div>

        <div class="d-grid gap-2 d-md-flex">
            <a class="btn btn-warning flex-grow-1" href="dashboard.php?view=edit-profile&user_id=<?= $user_id ?>">
                Chỉnh sửa thông tin
            </a>
            <a class="btn btn-secondary flex-grow-1" href="dashboard.php">
                Quay lại
            </a>
        </div>
    </div>
</div>