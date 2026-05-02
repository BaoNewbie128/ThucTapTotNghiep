<?php
// Bật hiển thị lỗi để debug (tắt trên production)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/admin_auth_check.php';
require __DIR__ . "/../config/db.php";

// Kiểm tra phương thức request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $_SESSION['message'] = "Phương thức không hợp lệ.";
    $_SESSION['message_type'] = "danger";
    header("Location: admin_dashboard.php?view=products");
    exit;
}

// Xác thực CSRF token
try {
    verify_csrf();
} catch (Exception $e) {
    $_SESSION['message'] = "Lỗi bảo mật: CSRF token không hợp lệ.";
    $_SESSION['message_type'] = "danger";
    header("Location: admin_dashboard.php?view=products");
    exit;
}

// Kiểm tra ID sản phẩm
if (!isset($_POST['id']) || empty($_POST['id'])) {
    $_SESSION['message'] = "Không tìm thấy ID sản phẩm.";
    $_SESSION['message_type'] = "danger";
    header("Location: admin_dashboard.php?view=products");
    exit;
}

$id = intval($_POST['id']);

if ($id <= 0) {
    $_SESSION['message'] = "ID sản phẩm không hợp lệ.";
    $_SESSION['message_type'] = "danger";
    header("Location: admin_dashboard.php?view=products");
    exit;
}

try {
    // Kiểm tra sản phẩm có tồn tại trong đơn hàng không
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count
        FROM order_items
        WHERE product_id = ?
    ");
    
    if (!$stmt) {
        throw new Exception("Lỗi chuẩn bị câu lệnh: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row['count'] > 0) {
        $_SESSION['message'] = "Không thể xóa sản phẩm này vì đã có trong " . $row['count'] . " đơn hàng. Vui lòng xóa các đơn hàng liên quan trước.";
        $_SESSION['message_type'] = "warning";
        header("Location: admin_dashboard.php?view=products");
        exit;
    }

    // Kiểm tra sản phẩm có trong giỏ hàng không
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count
        FROM cart_items
        WHERE product_id = ?
    ");
    
    if (!$stmt) {
        throw new Exception("Lỗi chuẩn bị câu lệnh: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row['count'] > 0) {
        $_SESSION['message'] = "Không thể xóa sản phẩm này vì đang có trong " . $row['count'] . " giỏ hàng.";
        $_SESSION['message_type'] = "warning";
        header("Location: admin_dashboard.php?view=products");
        exit;
    }

    // Kiểm tra sản phẩm có trong bảng reviews không (nếu có)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count
        FROM reviews
        WHERE product_id = ?
    ");
    
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if ($row['count'] > 0) {
            // Xóa các review liên quan trước
            $stmt = $conn->prepare("DELETE FROM reviews WHERE product_id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    // Lấy thông tin ảnh sản phẩm trước khi xóa
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();
    }

    // Thực hiện xóa sản phẩm
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    
    if (!$stmt) {
        throw new Exception("Lỗi chuẩn bị câu lệnh xóa: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        // Xóa file ảnh nếu tồn tại
        if (!empty($product['image'])) {
            $imagePath = __DIR__ . '/../images/' . $product['image'];
            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }
        $_SESSION['message'] = "Xóa sản phẩm thành công.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Không tìm thấy sản phẩm để xóa hoặc sản phẩm đã bị xóa trước đó.";
        $_SESSION['message_type'] = "warning";
    }
    
    $stmt->close();

} catch (Exception $e) {
    // Ghi log lỗi
    error_log("Lỗi xóa sản phẩm ID $id: " . $e->getMessage());
    $_SESSION['message'] = "Lỗi khi xóa sản phẩm: " . $e->getMessage();
    $_SESSION['message_type'] = "danger";
}

header("Location: admin_dashboard.php?view=products");
exit;
?>