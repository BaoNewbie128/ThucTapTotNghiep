<?php
require_once __DIR__ . '/../includes/admin_auth_check.php';
    require __DIR__ . "/../config/db.php";
    $customers = [];
    $error_message ="";

    $search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
    $where_clauses = [];
    $params = [];
    $types = '';
    if (!empty($search_query)) {
        $keyword = '%' . $search_query . '%';
        $where_clauses[] = "(username LIKE ? OR email LIKE ?)";
        array_push($params, $keyword, $keyword);
        $types .= 'ss';
    }

    $where_sql = " WHERE role ='customer'";
    if(count($where_clauses) > 0) {
        $where_sql .= ' AND ' . implode(' AND ', $where_clauses);
    }
    $limit = 9;
$page = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
$offset = ($page -1) * $limit;
$count_sql = "SELECT COUNT(*) as total FROM (
    SELECT id 
    FROM users 
    {$where_sql}
    GROUP BY username, email,phone,address, created_at) AS temp";
    $count_stmt = $conn->prepare($count_sql);
    if ($types !== '') {
        $count_stmt->bind_param($types, ...$params);
    }
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_products = $count_result->fetch_assoc()['total'] ?? 0;
    $total_pages = ceil($total_products / $limit);
    $sql = "SELECT id, username, email,phone,address, created_at FROM users " . $where_sql . " ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($sql);
if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
if ($result === FALSE) {
    $error_message = '<div class="alert alert-danger text-center">Lỗi truy vấn: ' . $conn->error . '</div>';
} else {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
    }
    $result->free();
}
$conn->close();
?>
<h2 style="color: blue">Quản lý khách hàng</h2>
<form method="GET" class="d-flex gap-2 mb-5" style="flex-wrap: wrap;">
    <input type="hidden" name="view" value="customers">
    <input name="search" class="form-control" placeholder="Tìm tên người dùng/email"
        value="<?= htmlspecialchars($search_query) ?>" style="flex: 1 1 auto; min-width: 150px;">
    <button type="submit" class="btn btn-primary" style="flex: 0 0 auto;">Tìm</button>
</form>
<div class="row">
    <?php foreach($customers as $c) :?>
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5>Tên người dùng: <?= htmlspecialchars($c["username"]) ?></h5>
                <p class="text-muted">Email: <?= htmlspecialchars($c["email"]) ?></p>
                <p class="text-muted">Số điện thoại: <?= htmlspecialchars($c["phone"]) ?></p>
                <p class="text-muted">Địa chỉ: <?= htmlspecialchars($c["address"]) ?></p>
                <p class="text-muted">Ngày tạo: <?= htmlspecialchars($c["created_at"]) ?></p>
            </div>
        </div>
    </div>
    <?php endforeach;?>
    <?php if ($total_pages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">

            <!-- Nút trang trước -->
            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                <a class="page-link" href="?view=customers&search=<?= urlencode($search_query) ?>&page=<?= $page-1 ?>">
                    &laquo;
                </a>
            </li>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                <a class="page-link" href="?view=customers&search=<?= urlencode($search_query) ?>&page=<?= $i ?>">
                    <?= $i ?>
                </a>
            </li>
            <?php endfor; ?>

            <!-- Nút trang sau -->
            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                <a class="page-link" href="?view=customers&search=<?= urlencode($search_query) ?>&page=<?= $page+1 ?>">
                    &raquo;
                </a>
            </li>

        </ul>
    </nav>
    <?php endif; ?>
</div>