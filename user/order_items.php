<?php
    session_start();
    include __DIR__ . "/../config/db.php";
    require_once __DIR__ . "/../includes/security.php";
    if(!isset($_SESSION["user_id"])){
        header("Location: ../login.php");
        exit;
    }
    $user_id = intval($_SESSION["user_id"]);

    if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])){
        verify_csrf();
        $action = $_POST['action'];
        $order_id = intval($_POST['id']);
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("SELECT id, status FROM orders WHERE id = ? AND user_id = ? FOR UPDATE");
            $stmt->bind_param("ii", $order_id, $user_id);
            $stmt->execute();
            $orderRow = $stmt->get_result()->fetch_assoc();
            if (!$orderRow) {
                throw new Exception("Không tìm thấy đơn hàng.");
            }

            if($action === 'pending_payment'){
                if ($orderRow['status'] !== 'pending') {
                    throw new Exception("Đơn hàng không thể chuyển sang chờ thanh toán.");
                }
                $stmt = $conn->prepare("UPDATE orders SET status = 'pending_payment' WHERE id = ? AND user_id = ?");
                $stmt->bind_param("ii", $order_id, $user_id);
                $stmt->execute();
                $_SESSION['message'] = "Đã gửi yêu cầu thanh toán, vui lòng chờ xác nhận từ hệ thống.";
            } elseif($action === 'delete_all'){
                if (in_array($orderRow['status'], ['cancelled', 'shipping', 'completed'], true)) {
                    throw new Exception("Đơn hàng này không thể hủy.");
                }

                $stmt = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
                $stmt->bind_param("i", $order_id);
                $stmt->execute();
                $itemsToReturn = $stmt->get_result();
                $stmtStock = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
                while($item = $itemsToReturn->fetch_assoc()){
                    $quantity = intval($item['quantity']);
                    $product_id = intval($item['product_id']);
                    $stmtStock->bind_param("ii", $quantity, $product_id);
                    $stmtStock->execute();
                }

                $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ? AND user_id = ?");
                $stmt->bind_param("ii", $order_id, $user_id);
                $stmt->execute();
                $_SESSION['message'] = "Hủy đơn hàng thành công.";
            } else {
                throw new Exception("Thao tác không hợp lệ.");
            }
            $conn->commit();
        } catch (Throwable $e) {
            $conn->rollback();
            $_SESSION['message'] = $e->getMessage();
        }
        header("Location: order_items.php");
        exit;
    }

    $stmt = $conn->prepare("SELECT id,total,status,created_at,shipping_fee,discount FROM orders WHERE user_id = ? ORDER BY id DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $orders = $stmt->get_result();
    $orderlist = [];
    if($orders && $orders->num_rows >0){
        $stmtItems = $conn->prepare("SELECT oi.product_id,oi.quantity,p.brand,p.model,p.image,p.price,p.color
                     FROM order_items oi 
                     JOIN products p ON oi.product_id = p.id 
                     WHERE oi.order_id = ?");
        while($order = $orders->fetch_assoc()){
            $order_item_id = intval($order['id']);
            $stmtItems->bind_param("i", $order_item_id);
            $stmtItems->execute();
            $rows = $stmtItems->get_result()->fetch_all(MYSQLI_ASSOC);
            $orderlist[] = ['order' => $order, 'items' => $rows];
        }
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
        <a href="order_history.php" class="btn btn-primary mb-3">
            Xem lịch sử mua hàng
        </a>
        <h2 class="page-title mb-4">Đơn hàng của bạn</h2>

        <?php if(empty($orderlist)):?>
        <div class="alert alert-info text-center">Không có sản phẩm nào</div>
        <?php endif; ?>

        <?php foreach($orderlist as $orderData):?>
        <?php
                $order = $orderData['order'];
                $items = $orderData['items'];
                 $total = 0;
            if(!empty($items)){
                foreach($items as $r){
                    $total += (float)$r['price'] * (int)$r['quantity'];
                }
            }
                $discount = $order['discount'] ?? 0;
                $shipping_fee = $order['shipping_fee'] ?? 0;
                $grand_total = $total + $shipping_fee - $discount;
                if($grand_total < 0) $grand_total = 0;
                $statusTrans = [
                    'pending' => 'Chưa xử lý',
                    'paid' => 'Đã thanh toán',
                    'shipping' => 'Đang giao hàng',
                    'completed' => 'Hoàn thành',
                    'cancelled' => 'Đã hủy',
                    'pending_payment' => 'Chờ thanh toán'
                ];
        ?>

        <div class="card mb-4 shadow-sm">

            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Đơn hàng #<?= $order['id'] ?></h6>
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
                            <?php if(!empty($items)):?>

                            <?php
                             foreach($items as $row):
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
                            <?php endforeach; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Không có sản phẩm nào</td>
                            </tr>
                            <?php endif; ?>
                            <tr class="table-light fw-bold">
                                <td colspan="5" class="text-end">Tổng sản phẩm:</td>
                                <td class="text-danger"><?= number_format($total) ?>₫</td>
                            </tr>
                            <tr class="table-light fw-bold">
                                <td colspan="5" class="text-end">Phí ship:</td>
                                <td class="text-warning"><?= number_format($shipping_fee) ?>₫</td>
                            </tr>
                            <tr class="table-light fw-bold">
                                <td colspan="5" class="text-end">Giảm giá:</td>
                                <td class="text-success">-<?= number_format($discount) ?>₫</td>
                            </tr>
                            <tr class="table-light fw-bold">
                                <td colspan="5" class="text-end">Tổng cộng:</td>
                                <td class="text-danger"><?= number_format($grand_total) ?>₫</td>
                            </tr>
                        </tbody>
                    </table>

                </div>

                <!-- Mobile Card View -->
                <div class="d-md-none p-3">
                    <?php if(!empty($items)):?>
                    <?php foreach($items as $row):
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
                    <?php endforeach; ?>

                    <div class="mt-3 pt-2 border-top">
                        <h6 class="text-end">Tổng sản phẩm: <span
                                class="text-danger fw-bold"><?= number_format($total) ?>₫</span></h6>
                        <h6 class="text-end">Phí ship: <span
                                class="text-warning fw-bold"><?= number_format($shipping_fee) ?>₫</span></h6>
                        <h6 class="text-end">Giảm giá: <span
                                class="text-warning fw-bold">-<?= number_format($discount) ?>₫</span></h6>
                        <h6 class="text-end">Tổng cộng: <span
                                class="text-danger fw-bold"><?= number_format($grand_total) ?>₫</span></h6>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info mb-0">Không có sản phẩm nào</div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-footer bg-light">
                <?php if(! in_array($order['status'],['paid', 'shipping', 'completed','cancelled'])):?>
                <form method="post" action="order_items.php" class="d-inline"
                    onsubmit="return confirm('Bạn muốn hủy đơn hàng này?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete_all">
                    <input type="hidden" name="id" value="<?= $order['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm w-20">Hủy đơn hàng</button>
                </form>
                <?php endif; ?>
                <?php if($order['status'] === 'pending'): ?>
                <button class="btn btn-success btn-sm w-20" onclick="toggleQR(<?= $order['id'] ?>)">
                    Thanh Toán
                </button>
                <?php elseif($order['status'] === 'paid'): ?>
                <p class="fw-bold d-inline-block m-0">Trạng thái đơn hàng : </p>
                <p class="text-success fw-bold d-inline-block m-0">
                    Đã thanh toán</p>
                <?php elseif($order['status'] === 'pending_payment'): ?>
                <p class="fw-bold d-inline-block m-0">Trạng thái đơn hàng : </p>
                <p class="text-warning fw-bold m-0">Đang chờ admin xác nhận thanh toán</p>
                <?php elseif($order['status'] === 'shipping'): ?>
                <p class="fw-bold d-inline-block m-0">Trạng thái đơn hàng : </p>
                <p class="text-success fw-bold d-inline-block m-0">Đang giao hàng </p>
                <?php elseif($order['status'] === 'completed'): ?>
                <p class="fw-bold d-inline-block m-0">Trạng thái đơn hàng : </p>
                <p class="text-success fw-bold d-inline-block m-0">Đã nhận hàng </p>
                <?php elseif($order['status'] === 'cancelled'): ?>
                <p class="fw-bold d-inline-block m-0">Trạng thái đơn hàng : </p>
                <p class="text-danger fw-bold d-inline-block m-0">Đã hủy </p>
                <?php endif; ?>
                <!-- Toggle QR -->

                <div id="qr_box_<?= $order['id'] ?>" class="mt-3 p-3 bg-white border rounded d-none">
                    <h6>Quét mã để thanh toán</h6>
                    <p>Số tiền: <strong class="text-danger"><?= number_format($grand_total) ?>₫</strong></p>
                    <p>Vui lòng nhập nội dung chuyển khoản như sau: MA DON HANG <?=$order['id']  ?> </p>
                    <img src="../images/qr_thanhtoan.jpg" width="250" class="border rounded">
                    <div class="mt-3">
                        <form method="post" action="order_items.php" class="d-inline"
                            onsubmit="return confirm('Bạn đã chuyển khoản? Chờ admin xác nhận nhé!');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="pending_payment">
                            <input type="hidden" name="id" value="<?= $order['id'] ?>">
                            <button type="submit" class="btn btn-primary btn-sm">Tôi đã chuyển khoản</button>
                        </form>
                        <button class="btn btn-secondary btn-sm" onclick="toggleQR(<?= $order['id'] ?>)">
                            Đóng
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="mt-4">
            <a href="/index.php" class="btn btn-secondary">Quay lại</a>
        </div>
    </div>
    <script>
    function toggleQR(orderId) {
        const box = document.getElementById("qr_box_" + orderId);
        box.classList.toggle("d-none");
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