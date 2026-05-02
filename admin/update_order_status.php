<?php
require_once __DIR__ . '/../includes/admin_auth_check.php';
require __DIR__ . "/../config/db.php";

if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    $_SESSION['message'] = "Không tìm thấy ID đơn hàng.";
    $_SESSION['message_type'] = "danger";
    header("Location: admin_dashboard.php?view=orders");
    exit;
}

$order_id = intval($_GET['order_id']);

if ($order_id <= 0) {
    $_SESSION['message'] = "ID đơn hàng không hợp lệ.";
    $_SESSION['message_type'] = "danger";
    header("Location: admin_dashboard.php?view=orders");
    exit;
}

$statusOptions = ['pending', 'paid', 'shipping', 'completed', 'cancelled', 'pending_payment', 'cod_pending'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
    } catch (Exception $e) {
        $_SESSION['message'] = "Lỗi bảo mật: CSRF token không hợp lệ.";
        $_SESSION['message_type'] = "danger";
        header("Location: admin_dashboard.php?view=orders");
        exit;
    }

    $status = $_POST['status'] ?? '';
    if (!in_array($status, $statusOptions, true)) {
        $_SESSION['message'] = "Trạng thái không hợp lệ.";
        $_SESSION['message_type'] = "danger";
        header("Location: admin_dashboard.php?view=orders");
        exit;
    }

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("SELECT id, status FROM orders WHERE id = ? FOR UPDATE");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$order) {
            throw new Exception("Đơn hàng không tồn tại.");
        }

        $old_status = $order['status'];

        // Xử lý hoàn trả stock khi hủy đơn hàng
        if ($status === 'cancelled' && $old_status !== 'cancelled') {
            $stmt = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $itemsToReturn = $stmt->get_result();
            $stmt->close();

            $stmtStock = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
            while ($item = $itemsToReturn->fetch_assoc()) {
                $quantity = intval($item['quantity']);
                $product_id = intval($item['product_id']);
                $stmtStock->bind_param("ii", $quantity, $product_id);
                $stmtStock->execute();
            }
            $stmtStock->close();
        }

        // Cập nhật trạng thái
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $order_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        $_SESSION['message'] = "Cập nhật trạng thái đơn hàng thành công.";
        $_SESSION['message_type'] = "success";
        header("Location: admin_dashboard.php?view=orders");
        exit;
    } catch (Throwable $e) {
        $conn->rollback();
        error_log("Lỗi cập nhật đơn hàng ID $order_id: " . $e->getMessage());
        $_SESSION['message'] = "Lỗi: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
        header("Location: admin_dashboard.php?view=orders");
        exit;
    }
}

// GET request - hiển thị form
$stmt = $conn->prepare("SELECT id, status FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    $_SESSION['message'] = "Đơn hàng không tồn tại.";
    $_SESSION['message_type'] = "danger";
    header("Location: admin_dashboard.php?view=orders");
    exit;
}
?>

<div class="container mt-4">
    <h3 class="mb-3">Cập nhật trạng thái đơn hàng #<?= $order_id ?></h3>

    <div class="card p-4 shadow-sm">
        <p><strong>Trạng thái hiện tại:</strong>
            <span class="badge 
                <?php 
                switch($order['status']) {
                    case 'completed': echo 'bg-success'; break;
                    case 'cancelled': echo 'bg-danger'; break;
                    case 'shipping': echo 'bg-primary'; break;
                    case 'paid': echo 'bg-info'; break;
                    default: echo 'bg-warning'; 
                }
                ?>
            "><?= htmlspecialchars($order['status']) ?></span>
        </p>

        <form method="POST">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Chọn trạng thái mới:</label>
                <select name="status" class="form-select" required>
                    <?php foreach ($statusOptions as $s): ?>
                    <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Cập nhật</button>
                <a href="admin_dashboard.php?view=orders" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
<?php $conn->close(); ?>