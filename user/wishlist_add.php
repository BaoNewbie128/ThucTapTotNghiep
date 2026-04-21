<?php
    session_start();
    require_once __DIR__ ."/../config/db.php";
    if(!isset($_SESSION['user_id'])){
            $_SESSION['wishlist_pending'] = intval($_POST['product_id']);
    $_SESSION['redirect_after_login'] = $_POST['redirect'] ?? '../index.php';
        header("Location: ../login.php");
        exit();
    }
    if (isset($_SESSION['wishlist_pending'])) {
    $pid = $_SESSION['wishlist_pending'];
    unset($_SESSION['wishlist_pending']);

    header("Location: /user/wishlist_add.php?product_id=" . $pid);
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
    $redirect = $_POST['redirect'] ?? '../index.php';
if($stmt->execute()){
    header("Location: " . $redirect);
    exit();
} else {
        die("Lỗi khi thêm vào wishlist: " . $conn->error);
    }
?>