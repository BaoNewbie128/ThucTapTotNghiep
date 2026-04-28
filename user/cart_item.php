<?php
    session_start();
    include __DIR__ . "/../config/db.php";
    require_once __DIR__ . "/../includes/security.php";
    if(!isset($_SESSION["user_id"])){
        header("Location: ../login.php");
        exit;
    }
    $user_id = intval($_SESSION["user_id"]);
    $total = 0;
    $cart_total = 0;
    $items = [];

    $stmt = $conn->prepare("SELECT c.id, ci.product_id, ci.quantity, p.brand, p.model, p.image, p.price, p.color, p.stock
                            FROM cart c
                            JOIN cart_items ci ON c.id = ci.cart_id
                            JOIN products p ON ci.product_id = p.id
                            WHERE c.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result_items = $stmt->get_result();
    $cart_id = 0;
    while($row = $result_items->fetch_assoc()) {
        $cart_id = intval($row['id']);
        $items[] = $row;
        $cart_total += $row['price'] * $row['quantity'];
    }
    $total = $cart_total;
    $discount_amount = 0;
if(isset($_POST['apply_coupon'])){
    verify_csrf();
    $code = trim($_POST['coupon_code'] ?? '');

    $stmt = $conn->prepare("SELECT * FROM coupons WHERE code = ? AND (expiry_date IS NULL OR expiry_date >= CURDATE())");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result_coupon = $stmt->get_result();

    if($result_coupon->num_rows > 0){
        $coupon = $result_coupon->fetch_assoc();

        if($coupon['type'] == 'fixed'){
            $discount_amount = $coupon['discount'];
        } else {
            $discount_amount = min($total, ($total * $coupon['discount']) / 100);
        }

        $_SESSION['coupon'] = [
            'code' => $coupon['code'],
            'discount' => $discount_amount
        ];

        $_SESSION['message'] = "Áp dụng mã thành công!";
    } else {
        $_SESSION['coupon'] = null;
        $_SESSION['message'] = "Mã không hợp lệ!";
    }
}
    if(isset($_POST['update'])){
    verify_csrf();
    $product_id = (int)$_POST['product_id'];
    $quantity = max(1, (int)$_POST['quantity']);

    $stmt = $conn->prepare("UPDATE cart_items ci JOIN cart c ON ci.cart_id = c.id JOIN products p ON ci.product_id = p.id
                           SET ci.quantity = LEAST(?, p.stock)
                           WHERE c.user_id = ? AND ci.product_id = ?");
    $stmt->bind_param("iii", $quantity, $user_id, $product_id);
        if($stmt->execute()){
            $_SESSION['message'] = "Cập nhật thành công!";
        } else {
            $_SESSION['message'] = "Lỗi: " . $conn->error;
        }

    header("Location: cart_item.php");
    exit;
}

    if(isset($_POST['delete']) && isset($_POST['product_id']) && $cart_id > 0){
        verify_csrf();
        $product_id = (int)$_POST['product_id'];
        
            $stmt = $conn->prepare("DELETE ci FROM cart_items ci JOIN cart c ON ci.cart_id = c.id WHERE c.user_id = ? AND ci.product_id = ?");
            $stmt->bind_param("ii", $user_id, $product_id);
             if($stmt->execute()){
                $_SESSION['message'] = "Xóa sản phẩm khỏi giỏ hàng thành công.";
            }else{
                $_SESSION['message'] = "Lỗi khi xóa sản phẩm: " . $conn->error;        
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
            
            if (!empty($items)):
            foreach ($items as $row):
                $subtotal = $row["price"] * $row["quantity"];
            ?>
                    <tr>
                        <td width="120"><img src="../images/<?= $row['image'] ?>" width="100"></td>
                        <td><?= $row["brand"] . " " . $row["model"] ?></td>
                        <td>
                            <form method="post" action="cart_item.php" class="d-flex">
                                <?= csrf_field() ?>
                                <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
                                <input type="number" name="quantity" value="<?= $row['quantity'] ?>" min="1"
                                    class="form-control form-control-sm me-2" style="width: 80px;">
                                <button type="submit" name="update" class="btn btn-primary btn-sm">Cập nhật</button>
                            </form>
                        </td>
                        <td><?= number_format($row["price"]) ?>₫</td>
                        <td><?= $row["color"] ?></td>
                        <td><?= number_format($subtotal) ?>₫</td>
                        <td>
                            <form method="post" action="cart_item.php"
                                onsubmit="return confirm('Xóa sản phẩm này khỏi giỏ?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
                                <button type="submit" name="delete" class="btn btn-danger btn-sm">Xóa</button>
                            </form>
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
            if (!empty($items)) :
            foreach ($items as $row):
                $subtotal = $row["price"] * $row["quantity"];
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
                            <form method="POST" action="cart_item.php">
                                <?= csrf_field() ?>
                                <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
                                <input type="number" name="quantity" value="<?= $row["quantity"] ?>" min="1"
                                    class="form-control mb-2">
                                <button type="submit" name="update" class="btn btn-primary btn-sm w-100">
                                    Cập nhật
                                </button>
                            </form>
                            <p class="mb-2 small">Màu: <strong><?= $row["color"] ?></strong></p>
                            <p class="mb-2 small">Giá: <strong><?= number_format($row["price"]) ?>₫</strong></p>
                            <p class="mb-3 text-danger fw-bold">Tổng: <?= number_format($subtotal) ?>₫</p>
                            <form method="post" action="cart_item.php"
                                onsubmit="return confirm('Xóa sản phẩm này khỏi giỏ?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
                                <button type="submit" name="delete" class="btn btn-danger btn-sm w-100">Xóa</button>
                            </form>
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
            <?php $total = $cart_total; ?>
            <div class="card p-3 bg-white shadow-sm">
                <div class="row align-items-center">
                    <div class="col-12 col-md-6">
                        <h4 class="mb-0 mb-md-0">Tổng sản phẩm: <span
                                class="text-danger fw-bold"><?= number_format($total) ?>₫</span></h4>
                        <form method="POST" class="mt-2">
                            <?= csrf_field() ?>
                            <input type="text" name="coupon_code" placeholder="Nhập mã giảm giá"
                                class="form-control mb-2">
                            <button type="submit" name="apply_coupon" class="btn btn-success btn-sm">Áp dụng</button>
                        </form>
                        <?php
                        if($total <= 0){
                            $shipping_fee = 0;
                            $discount_amount = 0;
                            $grand_total = 0;
                        }else{
                            $shipping_fee = 30000; // phí ship cố định
                            $discount_amount = $_SESSION['coupon']['discount'] ?? 0;
                            $grand_total = $total + $shipping_fee - $discount_amount;
                        if($grand_total < 0) $grand_total = 0; // đảm bảo tổng không âm 
                        }    
                        ?>
                        <?php if($total > 0): ?>
                        <p class="mb-0">Phí ship: <span
                                class="text-warning fw-bold"><?= number_format($shipping_fee) ?>₫</span></p>
                        <p>Giảm giá:
                            <span class="text-success fw-bold">
                                -<?= number_format($discount_amount) ?>₫
                            </span>
                        </p>
                        <?php endif; ?>
                        <h5 class="mb-0">Tổng cộng: <span
                                class="text-danger fw-bold"><?= number_format($grand_total) ?>₫</span></h5>
                    </div>
                    <div class="col-12 col-md-6 mt-3 mt-md-0">
                        <div class="d-flex gap-2 flex-column flex-md-row align-items-md-start">
                            <a href="/index.php"
                                class="btn btn-secondary btn-sm px-3 py-2 flex-shrink-0 align-self-start">Quay lại</a>
                            <form method="post" action="orders.php" class="flex-grow-1">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="checkout">
                                <div class="border rounded p-2 mb-2 bg-light">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method"
                                            id="payment_bank" value="bank_transfer" checked>
                                        <label class="form-check-label" for="payment_bank">
                                            Chuyển khoản trước
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method"
                                            id="payment_cod" value="cod">
                                        <label class="form-check-label" for="payment_cod">
                                            Trả sau khi nhận hàng
                                        </label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success btn-sm w-100">Đặt hàng</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

</body>

</html>