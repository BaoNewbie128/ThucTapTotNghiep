<?php
require_once __DIR__ . '/../includes/admin_auth_check.php';
    require __DIR__ . "/../config/db.php";
   if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    http_response_code(405);
    die("Phương thức không hợp lệ.");
   }
   verify_csrf();
   if(!isset($_POST['id'])){
    die("Không tìm thấy sản phẩm .");
   }
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    if($stmt->execute()){
        echo "<div class='alert alert-success'>Xóa sản phẩm thành công!</div>";
    } else {
        echo "<div class='alert alert-danger'>Lỗi khi xóa sản phẩm: " . $conn->error . "</div>";
    }
        header("Location: admin_dashboard.php?view=products");
    exit; 
?>