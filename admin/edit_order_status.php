<?php
    require __DIR__ . "/../config/db.php";
    if(!isset($_GET['order_id'])){
         die("Không tìm thấy đơn hàng.");
    }
    $order_id = intval($_GET['order_id']);
    $order = $conn->query("SELECT * FROM orders WHERE id = $order_id")->fetch_assoc();
    $success = "";
    $error_message = "";
    $statusOptions = [
    'pending'   => 'Chưa xử lý',
    'paid'      => 'Đã thanh toán',
    'shipping'  => 'Đang giao hàng',
    'completed' => 'Hoàn thành',
    'cancelled'  => 'Đã hủy'
];

    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $status = $conn->real_escape_string($_POST['status']);
        $sql = "UPDATE orders SET status='$status' WHERE id=$order_id";
        if($conn->query($sql) === TRUE){
            $success = "Cập nhật trạng thái đơn hàng thành công!";
            $order = $conn->query("SELECT * FROM orders WHERE id = $order_id")->fetch_assoc();
        } else {
            $error_message = "Lỗi: " . $sql . "<br>" . $conn->error;
        }
        header("Location: admin_dashboard.php?view=orders");
        exit; 
    }
?>
<a href="admin_dashboard.php?view=orders" class="btn btn-secondary mb-3">← Quay lại</a>
<h2 style="color: blue; margin-bottom: 20px;">Chỉnh sửa trạng thái</h2>
<h5 class="card-title">Đơn hàng #<?= $order_id ?></h5>
<br />
<form method="post">
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