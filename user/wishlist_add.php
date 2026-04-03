<?php
    session_start();
    require_once __DIR__ ."/../config/db.php";
    if(!isset($_SESSION['user_id'])){
        header("Location: ../login.php");
        exit();
    }
    $user_id = $_SESSION['user_id'];
    $product_id = intval($_GET['product_id'] ?? 0);
    if($product_id <= 0){
        die("Sản phẩm không hợp lệ");
    }
    $sql = "INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    if($stmt->execute()){
        header("Location:/user/dashboard.php?msg=added_wishlist");
    } else {
        die("Lỗi khi thêm vào wishlist: " . $conn->error);
    }
?>