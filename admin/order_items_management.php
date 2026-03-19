<?php
require __DIR__ . "/../config/db.php";

if (!isset($_GET['order_id'])) {
    die("<div class='alert alert-danger text-center'>Thiếu order_id!</div>");
}

$order_id = intval($_GET['order_id']);
$sqlOrder = "
    SELECT 
        orders.id,
        orders.total,
        orders.status,
        orders.created_at,
        users.username,
        users.phone,
        users.email,
        users.address
    FROM orders
    JOIN users ON orders.user_id = users.id
    WHERE orders.id = $order_id
";

$resultOrder = $conn->query($sqlOrder);

if ($resultOrder->num_rows === 0) {
    die("<div class='alert alert-danger text-center'>Không tìm thấy đơn hàng!</div>");
}

$order = $resultOrder->fetch_assoc();

// Lấy danh sách sản phẩm trong đơn hàng
$sqlItems = "
    SELECT 
        order_items.quantity,
        order_items.price,
        products.model,
        products.brand,
        products.image,
        products.color
    FROM order_items
    JOIN products ON order_items.product_id = products.id
    WHERE order_items.order_id = $order_id
";

$resultItems = $conn->query($sqlItems);

$orderItems = [];
if ($resultItems->num_rows > 0) {
    while ($row = $resultItems->fetch_assoc()) {
        $orderItems[] = $row;
    }
}
 $statusTrans = [
                    'pending' => 'Chưa xử lý',
                    'paid' => 'Đã thanh toán',
                    'shipping' => 'Đang giao hàng',
                    'completed' => 'Hoàn thành',
                    'canceled' => 'Đã hủy'
                ];
$conn->close();
$total_price = 0;
?>
<a href="admin_dashboard.php?view=orders" class="btn btn-secondary mt-3 mb-3">Quay lại</a>
<h2 class="text-primary mb-4">Chi tiết đơn hàng #<?= $order['id'] ?></h2>

<div class="card mb-4 p-3 shadow" style="overflow-x: auto;">
    <h4>Thông tin khách hàng</h4>
    <p><strong>Tên khách:</strong> <?= htmlspecialchars($order['username']) ?></p>
    <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($order['phone']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
    <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($order['address']) ?></p>

    <hr>

    <h4>Thông tin đơn hàng</h4>
    <p><strong>Tổng tiền:</strong> <?= number_format($order['total'], 0, ',', '.') ?>₫</p>
    <p><strong>Trạng thái:</strong> <?= $statusTrans[$order['status']] ?? 'Không xác định' ?> <a
            href="admin_dashboard.php?view=edit_order_status&order_id=<?= $order['id'] ?>"
            class="btn btn-warning mt-3 mb-3">
            Chỉnh sửa trạng thái</a></p>
    <p><strong>Ngày tạo:</strong> <?= htmlspecialchars($order['created_at']) ?></p>
</div>

<h3 class="text-secondary mb-3">Sản phẩm trong đơn</h3>

<?php if (empty($orderItems)): ?>
<div class="alert alert-warning">Đơn hàng không có sản phẩm!</div>
<?php else: ?>
<!-- Desktop Table View -->
<div class="d-none d-md-block">
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Hình ảnh</th>
                <th>Hãng</th>
                <th>Mẫu</th>
                <th>Số lượng</th>
                <th>Màu</th>
                <th>Giá</th>
                <th>Tổng</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orderItems as $item): 
                        $total_price += $item['price'] * $item['quantity']; ?>
            <tr>
                <td>
                    <img src="../images/<?= $item['image'] ?>" width="100px" style="object-fit: cover; height: 100px;">
                </td>
                <td><?= htmlspecialchars($item['brand']) ?></td>
                <td><?= htmlspecialchars($item['model']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td><?= $item['color'] ?></td>
                <td><?= number_format($item['price'], 0, ',', '.') ?>₫</td>
                <td><?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>₫</td>
            </tr>
            <?php endforeach; ?>
            <tr class="table-active">
                <td colspan="7">
                    <h5 class="text-end mb-0">Tổng cộng: <strong
                            class="text-danger"><?= number_format($total_price) ?>₫</strong></h5>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Mobile Card View -->
<div class="d-md-none">
    <div class="row g-3">
        <?php foreach ($orderItems as $item): 
                    $total_price += $item['price'] * $item['quantity']; ?>
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="row g-0">
                    <div class="col-4">
                        <img src="../images/<?= $item['image'] ?>" alt="<?= htmlspecialchars($item['brand']) ?>"
                            style="width: 100%; height: 120px; object-fit: cover;">
                    </div>
                    <div class="col-8">
                        <div class="card-body p-2">
                            <h6 class="card-title mb-2"><?= htmlspecialchars($item['brand']) ?>
                                <?= htmlspecialchars($item['model']) ?></h6>
                            <div class="row text-sm mb-2">
                                <div class="col-6"><small><strong>Số lượng:</strong> <?= $item['quantity'] ?></small>
                                </div>
                                <div class="col-6"><small><strong>Màu:</strong> <?= $item['color'] ?></small>
                                </div>
                                <div class="col-6"><small><strong>Đơn giá:</strong>
                                        <?= number_format($item['price'], 0, ',', '.') ?>₫</small></div>
                            </div>
                            <div class="border-top pt-2">
                                <p class="mb-0"><strong>Thành tiền:</strong> <span
                                        class="text-danger fw-bold"><?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>₫</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="card mt-3 p-3 text-center border-danger" style="background: rgba(255, 71, 87, 0.05);">
        <h5 class="mb-0">Tổng cộng: <strong class="text-danger"><?= number_format($total_price) ?>₫</strong></h5>
    </div>
</div>

<?php endif; ?>

<div class="mt-4">
    <a href="admin_dashboard.php?view=orders" class="btn btn-secondary">Quay lại quản lý đơn hàng</a>
</div>
</div>
</body>

</html>