<?php
require_once __DIR__ . '/../includes/admin_auth_check.php';
require __DIR__ . "/../config/db.php";

if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    die("Không tìm thấy ID người dùng.");
}

$user_id = intval($_GET['user_id']);

// Lấy thông tin user
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Người dùng không tồn tại.");
}

$user = $result->fetch_assoc();
$stmt->close();
function checkRelation($conn, $table, $column, $user_id) {
    $q = $conn->prepare("SELECT COUNT(*) AS total FROM $table WHERE $column = ?");
    $q->bind_param("i", $user_id);
    $q->execute();
    $res = $q->get_result()->fetch_assoc();
    return $res['total'] > 0;
}
$error_message = "";
// Nếu bấm xác nhận
if (isset($_POST['confirm_delete'])) {
    verify_csrf();
    if (checkRelation($conn, "orders", "user_id", $user_id)) {
        $error_message = "Không thể xóa vì người dùng đã đặt hàng (orders).";
    } elseif (checkRelation($conn, "cart", "user_id", $user_id)) {
        $error_message = "Không thể xóa vì người dùng đang có giỏ hàng (cart).";
    }else{
        try {
           $del = $conn->prepare("DELETE FROM users WHERE id = ?");
           $del->bind_param("i", $user_id);
           $del->execute();
           $del->close();
        header("Location: admin_dashboard.php?view=users&deleted=1");
        exit;
        } catch (\Throwable $th) {
            $error_message = "Không thể xóa người dùng do lỗi ràng buộc dữ liệu.";
        }
    }
}
?>

<div class="container mt-4">
    <h3 class="mb-3 text-danger">Xóa người dùng</h3>
    <?php if (!empty($error_message)) : ?>
    <div class="alert alert-danger">
        <strong>Lỗi:</strong> <?= htmlspecialchars($error_message) ?>
    </div>
    <?php endif; ?>
    <div class="alert alert-warning">
        <strong>Bạn có chắc muốn xóa?</strong><br>
        Username: <b><?= htmlspecialchars($user['username']) ?></b><br>
        Email: <b><?= htmlspecialchars($user['email']) ?></b>
    </div>

    <form method="POST">
        <?= csrf_field() ?>
        <button type="submit" name="confirm_delete" class="btn btn-danger">Xóa ngay</button>
        <a href="admin_dashboard.php?view=users" class="btn btn-secondary">Hủy</a>
    </form>
</div>