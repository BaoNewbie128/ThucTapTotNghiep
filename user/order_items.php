<?php
    session_start();
    include __DIR__ . "/../config/db.php";
    if(!isset($_SESSION["user_id"])){
        header("Location: ../login.php");
        exit;
    }
    $user_id = $_SESSION["user_id"];
     $sql = "SELECT id,total,status,created_at FROM orders WHERE user_id = $user_id AND status NOT IN ('cancelled') ORDER BY id DESC";
    $orders=$conn->query($sql);
    $orderlist = [];
    if($orders && $orders->num_rows >0){
        while($order = $orders->fetch_assoc()){
            $order_item_id = $order['id'];
            $sql2 = "SELECT oi.product_id,oi.quantity,p.brand,p.model,p.image,p.price,p.color
                     FROM order_items oi 
                     JOIN products p ON oi.product_id = p.id 
                     WHERE oi.order_id = $order_item_id";
            $item2 = $conn->query($sql2);
            $orderlist[] = ['order' => $order, 'items' => $item2];
        }
    }
    if(isset($_GET['action'])  && isset($_GET['id'])){
        $action = $_GET['action'];
        $order_id = intval($_GET['id']);
        $isItemReturn_query = $conn->query("SELECT product_id,quantity FROM order_items WHERE order_id = $order_id");
        if($isItemReturn_query->num_rows > 0){
            while($item = $isItemReturn_query->fetch_assoc()){
                $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            $conn->query("UPDATE products SET stock = stock + $quantity WHERE id = $product_id");
            }  
        }
        if($action == 'delete_all'){
            $sql_delete = "DELETE FROM order_items WHERE order_id = $order_id";
            $sql_update_order = "UPDATE orders SET status = 'cancelled' WHERE id = $order_id";
            if($conn->query($sql_delete)===true && $conn->query($sql_update_order)===true){
                $_SESSION['message'] = "Hủy đơn hàng thành công.";
            }else{
                $_SESSION['message'] = "Lỗi khi hủy đơn hàng" . $conn->error;
            }
        }
        if($action == 'paid'){
    $sql_paid = "UPDATE orders SET status = 'paid' WHERE id = $order_id";
    if($conn->query($sql_paid) === true){
        // Trừ stock
        $items_query = $conn->query("SELECT product_id, quantity FROM order_items WHERE order_id = $order_id");
        if($items_query->num_rows > 0){
            while($item = $items_query->fetch_assoc()){
                $product_id = $item['product_id'];
                $quantity = $item['quantity'];
                $conn->query("UPDATE products SET stock = stock - $quantity WHERE id = $product_id");
            }
        }
        $_SESSION['message'] = "Thanh toán thành công!";
        $_SESSION['show_thank_you'] = true;
    } else {
        $_SESSION['message'] = "Lỗi khi cập nhật thanh toán.";
    }
}
     header("Location: order_items.php");
    exit; 
    } 
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>

<body class="bg-light">

    <div class="app-container">
        <h2 class="page-title mb-4">Đơn hàng của bạn</h2>

        <?php if(empty($orderlist)):?>
        <div class="alert alert-info text-center">Không có sản phẩm nào</div>
        <?php endif; ?>

        <?php foreach($orderlist as $orderData):?>
        <?php
                $order = $orderData['order'];
                $items = $orderData['items'];
                 $total = 0;
            if($items && $items->num_rows > 0){
                $items->data_seek(0);
                while($r = $items->fetch_assoc()){
                    $total += (float)$r['price'] * (int)$r['quantity'];
                }
                // reset pointer để sau này render lại list
                $items->data_seek(0);
            }
                $statusTrans = [
                    'pending' => 'Chưa xử lý',
                    'paid' => 'Đã thanh toán',
                    'shipping' => 'Đang giao hàng',
                    'completed' => 'Hoàn thành',
                    'cancelled' => 'Đã hủy'
                ];
        ?>
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-dark text-white">
                <h6 class="mb-0">Đơn hàng #<?= $order['id'] ?> | <?= $order['created_at'] ?> | <span
                        class="badge bg-info"><?= $statusTrans[$order['status']] ?? 'Không xác định' ?></span></h6>
            </div>
            <div class="card-body p-0">
                <!-- Desktop Table View -->
                <div class="d-none d-md-block">
                    <table class="table table-bordered table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Ảnh</th>
                                <th>Tên Sản phẩm</th>
                                <th>Số lượng</th>
                                <th>Màu</th>
                                <th>Giá</th>
                                <th>Tổng</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($items->num_rows >0):?>

                            <?php
                             while($row = $items->fetch_assoc()):
                            $subtotal = $row["price"] * $row["quantity"];
                            ?>
                            <tr>
                                <td width="120"><img src="../images/<?= $row['image'] ?>" width="100"
                                        style="object-fit: cover;"></td>
                                <td style="margin-top: 5px;"><?= $row["brand"] . " " . $row["model"] ?></td>
                                <td><?= $row["quantity"] ?></td>
                                <td><?= $row["color"] ?></td>
                                <td class="fw-bold"><?= number_format($row["price"]) ?>₫</td>
                                <td class="fw-bold"><?= number_format($subtotal) ?>₫</td>
                                <td class="text-center">
                                    <?php if($order['status'] === 'paid' || $order['status'] === 'completed' || $order['status'] === 'shipping'): ?>
                                    <a href="reviews.php?product_id=<?= $row['product_id'] ?>&back_url=order_items.php"
                                        class="btn btn-sm btn-outline-primary fw-bold mt-3">
                                        <i class="bi bi-star"></i> Đánh giá
                                    </a>
                                    <?php else: ?>
                                    <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Không có sản phẩm nào</td>
                            </tr>
                            <?php endif; ?>
                            <tr class="table-light fw-bold">
                                <td colspan="5" class="text-end">Tổng cộng:</td>
                                <td class="text-danger"><?= number_format($total) ?>₫</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="d-md-none p-3">
                    <?php $items->data_seek(0); ?>
                    <?php if($items->num_rows >0):?>
                    <?php while($row = $items->fetch_assoc()):
                    $subtotal = $row["price"] * $row["quantity"];
                    ?>
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="row g-2">
                            <div class="col-4">
                                <img src="../images/<?= $row['image'] ?>" class="img-fluid w-100"
                                    style="object-fit: cover; height: 100px;" alt="<?= $row['brand'] ?>">
                            </div>
                            <div class="col-8">
                                <h6 class="mb-2"><?= $row["brand"] . " " . $row["model"] ?></h6>
                                <p class="mb-1 small">Số lượng: <strong><?= $row["quantity"] ?></strong></p>
                                <p class="mb-1 small">Màu: <strong><?= $row["color"] ?></strong></p>
                                <p class="mb-1 small">Giá: <strong><?= number_format($row["price"]) ?>₫</strong></p>
                                <p class="mb-2 text-danger fw-bold">Tổng: <?= number_format($subtotal) ?>₫</p>
                                <?php if($order['status'] === 'paid' || $order['status'] === 'completed' || $order['status'] === 'shipping'): ?>
                                <a href="reviews.php?product_id=<?= $row['product_id'] ?>&back_url=order_items.php"
                                    class="btn btn-sm btn-outline-primary fw-bold w-100">
                                    <i class="bi bi-star"></i> Viết đánh giá
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>

                    <div class="mt-3 pt-2 border-top">
                        <h6 class="text-end">Tổng cộng: <span
                                class="text-danger fw-bold"><?= number_format($total) ?>₫</span></h6>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info mb-0">Không có sản phẩm nào</div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-footer bg-light">
                <?php if(! in_array($order['status'],['paid', 'shipping', 'completed'])):?>
                <a href="order_items.php?action=delete_all&id=<?= $order['id'] ?>" class="btn btn-danger btn-sm w-20"
                    onclick="return confirm('Bạn muốn hủy đơn hàng này?');">
                    Hủy đơn hàng
                </a>
                <?php endif; ?>
                <?php if($order['status'] === 'pending'): ?>
                <button class="btn btn-success btn-sm w-20" onclick="toggleQR(<?= $order['id'] ?>, <?= $total ?>)">
                    Thanh Toán
                </button>
                <?php elseif($order['status'] === 'paid'): ?>
                <p class="fw-bold d-inline-block m-0">Trạng thái đơn hàng : </p>
                <p class="text-success fw-bold d-inline-block m-0">
                    Đã thanh toán</p>
                <?php elseif($order['status'] === 'shipping'): ?>
                <p class="fw-bold d-inline-block m-0">Trạng thái đơn hàng : </p>
                <p class="text-success fw-bold d-inline-block m-0">Đang giao hàng </p>
                <?php elseif($order['status'] === 'completed'): ?>
                <p class="fw-bold d-inline-block m-0">Trạng thái đơn hàng : </p>
                <p class="text-success fw-bold d-inline-block m-0">Đã nhận hàng </p>
                <?php endif; ?>
                <!-- Toggle QR -->

                <div id="qr_box_<?= $order['id'] ?>" class="mt-3 p-3 bg-white border rounded d-none">
                    <h6>Quét mã để thanh toán</h6>
                    <p>Số tiền: <strong class="text-danger"><?= number_format($total) ?>₫</strong></p>

                    <img src="../images/qr_thanhtoan.jpg" width="250" class="border rounded">

                    <div class="mt-3">
                        <button class="btn btn-primary btn-sm" onclick="confirmPayment(
                        <?= $order['id'] ?>
                        )">
                            Tôi đã thanh toán
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="toggleQR(<?= $order['id'] ?>)">
                            Đóng
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="mt-4">
            <a href="dashboard.php" class="btn btn-secondary">Quay lại</a>
        </div>
    </div>
    <script>
    function toggleQR(orderId, total = 0) {
        const box = document.getElementById("qr_box_" + orderId);
        box.classList.toggle("d-none");
    }
    // Gửi AJAX cập nhật trạng thái -> paid
    function confirmPayment(orderId) {
        if (!confirm("Xác nhận bạn đã thanh toán đơn hàng này?")) return;

        fetch("order_items.php?action=paid&id=" + orderId)
            .then(res => window.location.reload());
    }
    </script>

    <?php if (isset($_SESSION['show_thank_you']) && $_SESSION['show_thank_you']): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var thankYouModal = new bootstrap.Modal(document.getElementById('thankYouModal'));
        thankYouModal.show();
    });
    </script>
    <?php unset($_SESSION['show_thank_you']); ?>
    <?php endif; ?>

    <style>
    /* Review Button Styling */
    .btn-outline-primary {
        border-width: 1.5px;
        transition: all 0.3s ease;
    }

    .btn-outline-primary:hover {
        background-color: #0d6efd;
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(13, 110, 253, 0.3);
    }

    .btn-sm {
        padding: 0.4rem 0.8rem;
        font-size: 0.875rem;
        border-radius: 6px;
    }

    /* Desktop table review action cell */
    @media (min-width: 768px) {
        td.text-center .btn {
            min-width: 130px;
        }
    }

    /* Mobile button full width styling */
    @media (max-width: 767px) {
        .btn-outline-primary {
            display: block;
            margin-top: 0.5rem;
        }
    }
    </style>

    <!-- Modal cảm ơn -->
    <div class="modal fade" id="thankYouModal" tabindex="-1" aria-labelledby="thankYouModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="thankYouModalLabel">Cảm ơn bạn đã mua hàng!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Đơn hàng của bạn đã được thanh toán thành công. Cảm ơn bạn đã tin tưởng và sử dụng dịch vụ của
                        chúng tôi!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

</body>

</html>