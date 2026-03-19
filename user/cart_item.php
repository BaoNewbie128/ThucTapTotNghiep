<?php
    session_start();
    include __DIR__ . "/../config/db.php";
    if(!isset($_SESSION["user_id"])){
        header("Location: ../login.php");
        exit;
    }
    $user_id = $_SESSION["user_id"];
     $sql = "SELECT id FROM cart WHERE user_id = $user_id";
          $result=$conn->query($sql);
          $items =[];
   if($result->num_rows === 0){
        $cart_id = 0; 
        $result2 = null; 
    } else {
         $cart_id = $result->fetch_assoc()["id"];
         $sql2 = "SELECT ci.product_id,ci.quantity,p.brand,p.model,p.image,p.price,p.color
                  FROM cart_items ci 
                  JOIN products p ON ci.product_id = p.id 
                  WHERE ci.cart_id = $cart_id";
         $result2 = $conn->query($sql2); // Biến $result2 đã được định nghĩa ở đây
          if ($result2 && $result2->num_rows > 0) {
        while($row = $result2->fetch_assoc()) {
            $items[] = $row;
        }
    }
    }

    if(isset($_GET['action']) && isset($_GET['id']) && $cart_id > 0){
        $action = $_GET['action'];
        $product_id = (int)$_GET['id'];
        
        if($action == 'delete'){
            $sql_quantity = "SELECT quantity FROM cart_items WHERE cart_id = $cart_id AND product_id = $product_id";
            $result_quantity = $conn->query($sql_quantity);
            if($result_quantity->num_rows >0){
                $current_quantity = $result_quantity->fetch_assoc()["quantity"];
                if($current_quantity >1){
                    $sql_update_quantity = "UPDATE cart_items 
                                        SET quantity = quantity - 1 
                                        WHERE cart_id = $cart_id AND product_id = $product_id";
                    if($conn->query($sql_update_quantity)===true){
                        $_SESSION['message'] = "Đã loại 1 món ra khỏi giỏ hàng";
                    }else{
                        $_SESSION['message'] = "Lỗi khi cập nhật số lượng: " . $conn->error;
                    }
                }else if($current_quantity ==1){
                        $sql_delete = "DELETE FROM cart_items WHERE cart_id = $cart_id AND product_id = $product_id";
             if($conn->query($sql_delete)===true){
                $_SESSION['message'] = "Xóa sản phẩm khỏi giỏ hàng thành công.";
            }else{
                $_SESSION['message'] = "Lỗi khi xóa sản phẩm: " . $conn->error;
            }
            
                }
            }     
        } else{
            $_SESSION['message'] = "Sản phẩm không tồn tại.";
        }
        header("Location: cart_item.php");
            exit;
    } 
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>

<body class="bg-light">
    <div class="app-container">
        <h2 class="page-title mb-4">Giỏ hàng của bạn</h2>

        <!-- Desktop Table View -->
        <div class="d-none d-md-block">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Ảnh</th>
                        <th>Tên Sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Giá</th>
                        <th>Màu</th>
                        <th>Tổng</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
            
            $total = 0;
            if (!empty($items)):
            foreach ($items as $row):
                $subtotal = $row["price"] * $row["quantity"];
                $total += $subtotal;
            ?>
                    <tr>
                        <td width="120"><img src="../images/<?= $row['image'] ?>" width="100"></td>
                        <td><?= $row["brand"] . " " . $row["model"] ?></td>
                        <td><?= $row["quantity"] ?></td>
                        <td><?= number_format($row["price"]) ?>₫</td>
                        <td><?= $row["color"] ?></td>
                        <td><?= number_format($subtotal) ?>₫</td>
                        <td>
                            <a href="cart_item.php?action=delete&id=<?= $row['product_id'] ?>"
                                class="btn btn-danger btn-sm" onclick="return confirm('Xóa sản phẩm này khỏi giỏ?');">
                                Xóa
                            </a>
                        </td>
                    </tr>
                    <?php endforeach;
                    else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">Giỏ hàng của bạn đang trống.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="d-md-none">
            <?php
            $total = 0;
            if (!empty($items)) :
            foreach ($items as $row):
                $subtotal = $row["price"] * $row["quantity"];
                $total += $subtotal;
            ?>
            <div class="card mb-3 shadow-sm">
                <div class="row g-0">
                    <div class="col-4">
                        <img src="../images/<?= $row['image'] ?>" class="img-fluid w-100 h-100"
                            style="object-fit: cover;" alt="<?= $row['brand'] ?>">
                    </div>
                    <div class="col-8">
                        <div class="card-body p-3">
                            <h6 class="card-title mb-2"><?= $row["brand"] . " " . $row["model"] ?></h6>
                            <p class="mb-2 small">Số lượng: <strong><?= $row["quantity"] ?></strong></p>
                            <p class="mb-2 small">Màu: <strong><?= $row["color"] ?></strong></p>
                            <p class="mb-2 small">Giá: <strong><?= number_format($row["price"]) ?>₫</strong></p>
                            <p class="mb-3 text-danger fw-bold">Tổng: <?= number_format($subtotal) ?>₫</p>
                            <a href="cart_item.php?action=delete&id=<?= $row['product_id'] ?>"
                                class="btn btn-danger btn-sm w-100"
                                onclick="return confirm('Xóa sản phẩm này khỏi giỏ?');">
                                Xóa
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach;
            else: ?>
            <div class="alert alert-info text-center">Giỏ hàng của bạn đang trống.</div>
            <?php endif; ?>
        </div>

        <div class="mt-4">
            <div class="card p-3 bg-white shadow-sm">
                <div class="row align-items-center">
                    <div class="col-12 col-md-6">
                        <h4 class="mb-0 mb-md-0">Tổng cộng: <span
                                class="text-danger fw-bold"><?= number_format($total) ?>₫</span></h4>
                    </div>
                    <div class="col-12 col-md-6 mt-3 mt-md-0">
                        <div class="d-flex gap-2 flex-column flex-md-row">
                            <a href="dashboard.php" class="btn btn-secondary btn-sm flex-grow-1">Quay lại</a>
                            <a href="orders.php?action=checkout" class="btn btn-success btn-sm flex-grow-1">Đặt hàng</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

</body>

</html>