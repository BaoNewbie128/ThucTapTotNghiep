<?php
    require __DIR__ . "/../config/db.php";
    $products = [];
    $unique_brands = [];
    // Lấy tất cả hãng xe để hiển thị dropdown
$brand_sql = "SELECT DISTINCT brand FROM products ORDER BY brand";
$brand_result = $conn->query($brand_sql);

if ($brand_result && $brand_result->num_rows > 0) {
    while ($row = $brand_result->fetch_assoc()) {
        $unique_brands[] = $row['brand'];
    }
}
    $error_message ="";

    function format_currency($amount) {
    return number_format($amount, 0, ',', '.') . '₫';
    }
    function truncate_description($text, $limit = 100) {
    if (strlen($text) > $limit) {
        $text = substr($text, 0, $limit);
        $text = substr($text, 0, strrpos($text, ' '));
        return $text . '...';
    }
    return $text;
}
$search_query = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
    $filter_brand = isset($_GET['brand']) ? $conn->real_escape_string($_GET['brand']) : '';
    $where_clauses = [];
    if (!empty($search_query)) {
        $where_clauses[] = "(brand LIKE '%$search_query%' OR model LIKE '%$search_query%')";
    }
    if (!empty($filter_brand) && $filter_brand !== 'all') {
        $where_clauses[] = "brand = '$filter_brand'";
    }

    $where_sql = count($where_clauses) > 0 ? ' WHERE ' . implode(' AND ', $where_clauses) : '';
    
    // Thêm phân trang( Tối đa 9 sản phẩm)
$limit = 9;
$page = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
$offset = ($page -1) * $limit;
$count_sql = "SELECT COUNT(*) as total FROM (
    SELECT id 
    FROM products 
    {$where_sql}
    GROUP BY brand,model,scale,description) AS temp";
    $count_result = $conn->query($count_sql);
    $total_products = $count_result->fetch_assoc()['total'] ?? 0;
    $total_pages = ceil($total_products / $limit);
    
    $sql = "SELECT id, brand, model, scale, price, stock, color, image, description FROM products " . $where_sql . "ORDER BY id DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
if ($result === FALSE) {
    $error_message = '<div class="alert alert-danger text-center">Lỗi truy vấn: ' . $conn->error . '</div>';
} else {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
            // if (!in_array($row['brand'], $unique_brands)) {
            //     $unique_brands[] = $row['brand'];
            // }
        }
    }
    $result->free();
}
$conn->close();
?>
<h2 style="color: blue">Quản lý sản phẩm</h2>
<a href="admin_dashboard.php?view=add" class="btn btn-success mb-3" style="flex: 0 0 auto;">Thêm 1 sản phẩm</a>
<form method="GET" class="d-flex gap-2 mb-4" style="flex-wrap: wrap;">
    <input type="hidden" name="view" value="products">

    <select name="brand" class="form-select" style="max-width:200px; flex: 1 1 auto; min-width: 120px;">
        <option value="all">Tất cả</option>
        <?php foreach ($unique_brands as $b): ?>
        <option value="<?= $b ?>" <?= ($filter_brand==$b?"selected":"") ?>><?= $b ?></option>
        <?php endforeach; ?>
    </select>

    <input name="search" class="form-control" placeholder="Tìm tên xe/hãng/màu"
        value="<?= htmlspecialchars($search_query) ?>" style="flex: 1 1 auto; min-width: 150px;">

    <button class="btn btn-primary" style="flex: 0 0 auto;">Lọc</button>

</form>
<div class="row g-3">
    <?php foreach ($products as $p):
        $modal_id = 'modal-' . $p['id'];
         $short_description = truncate_description($p['description'], 20); ?>
    <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
        <div class="card shadow-sm h-100 d-flex flex-column">
            <img src="/../images/<?= $p['image'] ?>" class="card-img-top" style="height: 180px; object-fit: cover;">
            <div class="card-body d-flex flex-column flex-grow-1">
                <h5 class="card-title" style="font-size: 1rem; margin-bottom: 8px;">
                    <?= $p["brand"] . " " . $p["model"] ?></h5>
                <p class="text-muted small mb-1">Tỉ lệ: <?= $p["scale"] ?></p>
                <p class="text-muted small mb-2">Số lượng: <?= $p["stock"] ?></p>
                <p class="text-muted small mb-2">Màu: <strong><?= $p["color"] ?></strong></p>
                <p class="fw-bold text-danger mb-2" style="font-size: 1.1rem;"><?= format_currency($p["price"]) ?></p>
                <p class="card-text small mb-3 flex-grow-1">
                    <strong>Chi tiết:</strong> <?= $short_description ?>

                    <?php if (strlen($p['description']) > 20): ?>
                    <a href="#" class="text-primary text-decoration-none fw-bold d-block mt-1" data-bs-toggle="modal"
                        data-bs-target="#<?= $modal_id ?>">
                        Xem thêm
                    </a>
                    <?php endif; ?>
                </p>
                <div class="d-flex gap-2">
                    <a href="admin_dashboard.php?view=edit&id=<?= $p['id'] ?>"
                        class="btn btn-sm btn-warning flex-grow-1">Sửa</a>
                    <a href="admin_dashboard.php?view=delete&id=<?= $p['id'] ?>"
                        class="btn btn-sm btn-danger flex-grow-1"
                        onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">Xóa</a>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="<?= $modal_id ?>" tabindex="-1" aria-labelledby="<?= $modal_id ?>Label"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="<?= $modal_id ?>Label">Chi tiết về xe:
                        <?= $p["brand"] . " " . $p["model"] ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?= nl2br(htmlspecialchars($p['description'])) ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach;  ?>
    <?php if ($total_pages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">

            <!-- Nút trang trước -->
            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                <a class="page-link"
                    href="?view=products&search=<?= urlencode($search_query) ?>&brand=<?= urlencode($filter_brand) ?>&page=<?= $page-1 ?>">
                    &laquo;
                </a>
            </li>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                <a class="page-link"
                    href="?view=products&search=<?= urlencode($search_query) ?>&brand=<?= urlencode($filter_brand) ?>&page=<?= $i ?>">
                    <?= $i ?>
                </a>
            </li>
            <?php endfor; ?>

            <!-- Nút trang sau -->
            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                <a class="page-link"
                    href="?view=products&search=<?= urlencode($search_query) ?>&brand=<?= urlencode($filter_brand) ?>&page=<?= $page+1 ?>">
                    &raquo;
                </a>
            </li>

        </ul>
    </nav>
    <?php endif; ?>
</div>