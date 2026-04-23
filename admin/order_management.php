<?php
    require __DIR__ . "/../config/db.php";
    $orders = [];
    $error_message ="";
    $limit = 8;
$page = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
$offset = ($page -1) * $limit;
$count_sql = "SELECT COUNT(*) as total FROM (
    SELECT o.id 
    FROM orders o JOIN users u ON o.user_id = u.id 
    GROUP BY o.id) AS temp";
    $count_result = $conn->query($count_sql);
    $total_products = $count_result->fetch_assoc()['total'] ?? 0;
    $total_pages = ceil($total_products / $limit);
    $sql = "SELECT o.id,u.username,o.total,o.status,o.created_at FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.id DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
if ($result === FALSE) {
    $error_message = '<div class="alert alert-danger text-center">Lỗi truy vấn: ' . $conn->error . '</div>';
} else {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    }
    $result->free();
}
 $statusTrans = [
                    'pending' => 'Chưa xử lý',
                    'paid' => 'Đã thanh toán',
                    'shipping' => 'Đang giao hàng',
                    'completed' => 'Hoàn thành',
                    'cancelled' => 'Đã hủy',
                    'pending_payment' => 'Chờ xác nhận thanh toán'
                ];
$conn->close();
?>
<h2 style="color: blue">Quản lý đơn hàng</h2>
<div class="row">
    <?php foreach($orders as $o) :?>
    <div class="col-md-5 mb-4 mt-3">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Đơn hàng #<?= $o['id'] ?></h5>
                <p class="card-text"><strong>Tên khách hàng:</strong> <?= htmlspecialchars($o['username']) ?></p>
                <p class="card-text"><strong>Tổng tiền:</strong> <?= number_format($o['total'], 0, ',', '.') ?>₫</p>
                <p class="card-text"><strong>Trạng thái:</strong> <?= $statusTrans[$o['status']] ?? 'Không xác định' ?>
                    <a href="admin_dashboard.php?view=edit_order_status&order_id=<?= $o['id'] ?>"
                        class="btn btn-warning">Chỉnh sửa</a>
                </p>
                <p class="card-text"><strong>Ngày tạo:</strong> <?= htmlspecialchars($o['created_at']) ?></p>
                <a href="admin_dashboard.php?view=order_items&order_id=<?= $o['id'] ?>" class="btn btn-primary">Xem chi
                    tiết đơn hàng</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if ($total_pages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">

            <!-- Nút trang trước -->
            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                <a class="page-link" href="?view=orders&page=<?= $page-1 ?>">
                    &laquo;
                </a>
            </li>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                <a class="page-link" href="?view=orders&page=<?= $i ?>">
                    <?= $i ?>
                </a>
            </li>
            <?php endfor; ?>

            <!-- Nút trang sau -->
            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                <a class="page-link" href="?view=orders&page=<?= $page+1 ?>">
                    &raquo;
                </a>
            </li>

        </ul>
    </nav>
    <?php endif; ?>
</div>