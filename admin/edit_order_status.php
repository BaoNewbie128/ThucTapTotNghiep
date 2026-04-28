<?php
require_once __DIR__ . '/../includes/admin_auth_check.php';
    require __DIR__ . "/../config/db.php";
    if(!isset($_GET['order_id'])){
         die("Không tìm thấy đơn hàng.");
    }
    $order_id = intval($_GET['order_id']);
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    if (!$order) {
        die("Đơn hàng không tồn tại.");
    }
    $success = "";
    $error_message = "";
    $statusOptions = [
    'pending'   => 'Chưa xử lý',
    'paid'      => 'Đã thanh toán',
    'shipping'  => 'Đang giao hàng',
    'completed' => 'Hoàn thành',
    'cancelled'  => 'Đã hủy',
    'pending_payment' => 'Chờ xác nhận thanh toán',
    'cod_pending' => 'Trả sau khi nhận hàng'
];

?>
<a href="admin_dashboard.php?view=orders" class="btn btn-secondary mb-3">Quay lại</a>
<h2 style="color: blue; margin-bottom: 20px;">Chỉnh sửa trạng thái</h2>
<h5 class="card-title">Đơn hàng #<?= $order_id ?></h5>
<br />
<form method="post" action="admin_dashboard.php?view=edit_order_status&order_id=<?= $order_id ?>">
    <?= csrf_field() ?>
    <label for="status"><strong>Trạng thái đơn hàng:</strong></label>

    <select name="status" id="status" class="form-select" style="max-width: 300px;">
        <?php foreach ($statusOptions as $value => $label): ?>
        <option value="<?= $value ?>" <?= ($order['status'] === $value) ? 'selected' : '' ?>>
            <?= $label ?>
        </option>
        <?php endforeach; ?>
    </select>

    <button type="submit" class="btn btn-primary mt-3">Cập nhật</button>
</form>