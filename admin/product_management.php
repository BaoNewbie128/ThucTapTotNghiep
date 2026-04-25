<?php
require_once __DIR__ . '/../includes/admin_auth_check.php';
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
if (!function_exists('bind_mysqli_params')) {
    function bind_mysqli_params(mysqli_stmt $stmt, string $types, array $params): void {
        if ($types === '') {
            return;
        }
        $refs = [];
        foreach ($params as $key => $value) {
            $refs[$key] = &$params[$key];
        }
        $stmt->bind_param($types, ...$refs);
    }
}
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
    $filter_brand = isset($_GET['brand']) ? (array)$_GET['brand'] : [];
    $filter_brand = array_values(array_filter($filter_brand, fn($brand) => trim($brand) !== ''));
    $where_clauses = [];
    $params = [];
    $types = '';
    $brand_condition = [];
    if (!empty($search_query)) {
        $keyword = '%' . $search_query . '%';
        $where_clauses[] = "(brand LIKE ? OR model LIKE ?)";
        array_push($params, $keyword, $keyword);
        $types .= 'ss';
    }
    if (!empty($filter_brand)) {
        foreach($filter_brand as $b){
            $brand_condition[] = "brand = ?";
            $params[] = trim($b);
            $types .= 's';
        }if(! empty($brand_condition)){
             $where_clauses[] = '(' . implode(' OR ' ,$brand_condition) . ')';
        }
       
    }

    $where_sql = count($where_clauses) > 0 ? ' WHERE ' . implode(' AND ', $where_clauses) : '';
    
    // Thêm phân trang( Tối đa 9 sản phẩm)
$limit = 9;
$page = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
$offset = ($page -1) * $limit;
$count_sql = "SELECT COUNT(*) as total FROM (
    SELECT id 
    FROM products 
    {$where_sql} ) AS temp";
    $count_stmt = $conn->prepare($count_sql);
    bind_mysqli_params($count_stmt, $types, $params);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_products = $count_result->fetch_assoc()['total'] ?? 0;
    $total_pages = ceil($total_products / $limit);
    
    $sql = "SELECT id, brand, model, scale, price, stock, color, image, description FROM products " . $where_sql . " ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($sql);
bind_mysqli_params($stmt, $types, $params);
$stmt->execute();
$result = $stmt->get_result();
if ($result === FALSE) {
    $error_message = '<div class="alert alert-danger text-center">Lỗi truy vấn: ' . $conn->error . '</div>';
} else {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
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
    <?php foreach ($unique_brands as $b): ?>
    <div class="form-check">
        <input type="checkbox" name="brand[]" value="<?= $b ?>" onchange="this.form.submit()"
            <?= (in_array($b, $filter_brand)) ? 'checked' : '' ?>>
        <label><?= $b ?></label>
    </div>
    <?php endforeach; ?>
    <input name="search" class="form-control mt-2" placeholder="Tìm..." value="<?= htmlspecialchars($search_query) ?>"
        onchange="this.form.submit()">
</form>
<a href="admin_dashboard.php?view=products" class="btn btn-danger mb-3">
    Xóa bộ lọc
</a>
<div class="row g-3">
    <?php foreach ($products as $p):
        $modal_id = 'modal-' . $p['id'];
         $short_description = truncate_description($p['description'], 20); ?>
    <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
        <div
            class="card shadow-sm h-100 d-flex flex-column <?php if ($p["stock"] < 10): ?>border-danger<?php endif; ?></div>">
            <img src="/../images/<?= $p['image'] ?>" class="card-img-top" style="height: 180px; object-fit: cover;">
            <div class="card-body d-flex flex-column flex-grow-1">
                <h5 class="card-title" style="font-size: 1rem; margin-bottom: 8px;">
                    <?= $p["brand"] . " " . $p["model"] ?></h5>
                <p class="text-muted small mb-1">Tỉ lệ: <?= $p["scale"] ?></p>
                <p class="text-muted small mb-2">Số lượng:
                    <?php if ($p["stock"] < 10): ?>
                    <span class="text-danger fw-bold"><?= $p["stock"] ?> (Sắp hết!)</span>
                    <?php else: ?>
                    <span class="text-success"><?= $p["stock"] ?></span>
                    <?php endif; ?>
                </p>
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
                    <form method="POST" action="admin_dashboard.php?view=delete" class="flex-grow-1"
                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger w-100">Xóa</button>
                    </form>
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
            <?php
            $brand_query = '';
            if (!empty($filter_brand)) {
                foreach ($filter_brand as $b) {
                    $brand_query .= '&brand[]=' . urlencode($b);
                }
            }
            $base_url = "?view=products&search=" . urlencode($search_query) . $brand_query;
            ?>

            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $base_url ?>&page=1">
                    << </a>
            </li>

            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $base_url ?>&page=<?= $page-1 ?>">
                    < </a>
            </li>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                <a class="page-link" href="<?= $base_url ?>&page=<?= $i ?>">
                    <?= $i ?>
                </a>
            </li>
            <?php endfor; ?>

            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $base_url ?>&page=<?= $page+1 ?>">></a>
            </li>

            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $base_url ?>&page=<?= $total_pages ?>">>></a>
            </li>

        </ul>
    </nav>
    <?php endif; ?>
</div>