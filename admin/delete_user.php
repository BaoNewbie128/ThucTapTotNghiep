<?php
require_once __DIR__ . '/../includes/admin_auth_check.php';
require __DIR__ . "/../config/db.php";

if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    $_SESSION['message'] = "Không tìm thấy ID người dùng.";
    $_SESSION['message_type'] = "danger";
    header("Location: admin_dashboard.php?view=users");
    exit;
}

$user_id = intval($_GET['user_id']);

if ($user_id <= 0) {
    $_SESSION['message'] = "ID người dùng không hợp lệ.";
    $_SESSION['message_type'] = "danger";
    header("Location: admin_dashboard.php?view=users");
    exit;
}

// Không cho phép xóa chính mình
if ($user_id === intval($_SESSION['user_id'])) {
    $_SESSION['message'] = "Bạn không thể xóa chính mình!";
    $_SESSION['message_type'] = "danger";
    header("Location: admin_dashboard.php?view=users");
    exit;
}

// Lấy thông tin user
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['message'] = "Người dùng không tồn tại.";
    $_SESSION['message_type'] = "danger";
    header("Location: admin_dashboard.php?view=users");
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

function checkRelation($conn, $table, $column, $user_id) {
    $q = $conn->prepare("SELECT COUNT(*) AS total FROM $table WHERE $column = ?");
    $q->bind_param("i", $user_id);
    $q->execute();
    $res = $q->get_result()->fetch_assoc();
    $q->close();
    return $res['total'];
}

$error_message = "";
$success_message = "";

// Nếu bấm xác nhận
if (isset($_POST['confirm_delete'])) {
    try {
        verify_csrf();
    } catch (Exception $e) {
        $error_message = "Lỗi bảo mật: CSRF token không hợp lệ.";
    }
    
    if (empty($error_message)) {
        // Kiểm tra các ràng buộc
        $orders_count = checkRelation($conn, "orders", "user_id", $user_id);
        if ($orders_count > 0) {
            $error_message = "Không thể xóa vì người dùng đã có $orders_count đơn hàng.";
        }
        
        $cart_count = checkRelation($conn, "cart", "user_id", $user_id);
        if ($cart_count > 0 && empty($error_message)) {
            $error_message = "Không thể xóa vì người dùng đang có giỏ hàng.";
        }
        
        $reviews_count = checkRelation($conn, "reviews", "user_id", $user_id);
        
        if (empty($error_message)) {
            try {
                $conn->begin_transaction();
                
                // Xóa reviews nếu có
                if ($reviews_count > 0) {
                    $stmt = $conn->prepare("DELETE FROM reviews WHERE user_id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $stmt->close();
                }
                
                // Xóa user
                $del = $conn->prepare("DELETE FROM users WHERE id = ?");
                $del->bind_param("i", $user_id);
                $del->execute();
                
                if ($del->affected_rows > 0) {
                    $conn->commit();
                    $_SESSION['message'] = "Xóa người dùng thành công.";
                    $_SESSION['message_type'] = "success";
                    header("Location: admin_dashboard.php?view=users");
                    exit;
                } else {
                    $conn->rollback();
                    $error_message = "Không thể xóa người dùng.";
                }
                $del->close();
            } catch (Throwable $th) {
                $conn->rollback();
                $error_message = "Lỗi khi xóa người dùng: " . $th->getMessage();
            }
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
<?php $conn->close(); ?>