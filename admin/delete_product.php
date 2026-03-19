<?php
    require __DIR__ . "/../config/db.php";
   if(!isset($_GET['id'])){
    die("Không tìm thấy sản phẩm .");
   }
    $id = intval($_GET['id']);
    $delete_sql = "DELETE FROM products WHERE id = $id";
    if($conn->query($delete_sql) === TRUE){
        echo "<div class='alert alert-success'>Xóa sản phẩm thành công!</div>";
    } else {
        echo "<div class='alert alert-danger'>Lỗi khi xóa sản phẩm: " . $conn->error . "</div>";
    }
        header("Location: admin_dashboard.php?view=products");
    exit; 
?>