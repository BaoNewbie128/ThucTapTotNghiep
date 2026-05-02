<?php
require_once __DIR__ . '/../includes/admin_auth_check.php';
require __DIR__ . "/../config/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $_SESSION['message'] = "Phương thức không hợp lệ.";
    $_SESSION['message_type'] = "danger";
    header("Location: admin_dashboard.php?view=posts");
    exit;
}

try {
    verify_csrf();
} catch (Exception $e) {
    $_SESSION['message'] = "Lỗi bảo mật: CSRF token không hợp lệ.";
    $_SESSION['message_type'] = "danger";
    header("Location: admin_dashboard.php?view=posts");
    exit;
}

$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['message'] = "ID bài viết không hợp lệ.";
    $_SESSION['message_type'] = "danger";
    header("Location: admin_dashboard.php?view=posts");
    exit;
}

try {
    // Kiểm tra bài viết có tồn tại không
    $stmt = $conn->prepare("SELECT id FROM posts WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Lỗi chuẩn bị câu lệnh: " . $conn->error);
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['message'] = "Bài viết không tồn tại.";
        $_SESSION['message_type'] = "warning";
        header("Location: admin_dashboard.php?view=posts");
        exit;
    }
    $stmt->close();

    // Xóa bài viết
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Lỗi chuẩn bị câu lệnh xóa: " . $conn->error);
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        $_SESSION['message'] = "Xóa bài viết thành công.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Không thể xóa bài viết hoặc bài viết đã bị xóa trước đó.";
        $_SESSION['message_type'] = "warning";
    }
    $stmt->close();

} catch (Exception $e) {
    error_log("Lỗi xóa bài viết ID $id: " . $e->getMessage());
    $_SESSION['message'] = "Lỗi khi xóa bài viết: " . $e->getMessage();
    $_SESSION['message_type'] = "danger";
}

header("Location: admin_dashboard.php?view=posts");
exit;
?>