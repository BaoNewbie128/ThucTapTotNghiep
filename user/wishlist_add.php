<?php
    session_start();
    require_once __DIR__ ."/../config/db.php";
    require_once __DIR__ ."/../includes/security.php";
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: ../index.php");
        exit();
    }

    verify_csrf();

    if(!isset($_SESSION['user_id'])){
        $_SESSION['wishlist_pending'] = intval($_POST['product_id'] ?? 0);
        $_SESSION['redirect_after_login'] = is_safe_local_url($_POST['redirect'] ?? '../index.php');
        header("Location: ../login.php");
        exit();
    }
    $user_id = $_SESSION['user_id'];
    $product_id = intval($_POST['product_id'] ?? 0);
    if($product_id <= 0){
        die("Sản phẩm không hợp lệ". $product_id);
    }
    $sql = "INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    $redirect = is_safe_local_url($_POST['redirect'] ?? '../index.php');
if($stmt->execute()){
    header("Location: " . $redirect);
    exit();
} else {
        die("Lỗi khi thêm vào wishlist: " . $conn->error);
    }
?>