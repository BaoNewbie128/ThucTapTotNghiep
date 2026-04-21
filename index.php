<?php
ob_start();
session_start();
require_once __DIR__ . "/config/db.php";

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
$error_message = "";

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

$search_query = isset($_GET['search']) ? $conn->real_escape_string(trim($_GET['search'])) : '';
$filter_brand = isset($_GET['brand']) ? $conn->real_escape_string(trim($_GET['brand'])) : '';

$where_clauses = [];
if ($search_query !== '') {
    // Tìm theo brand, model, description, image, color
    $s = $conn->real_escape_string($search_query);
    $where_clauses[] = "(
        brand LIKE '%$s%'
        OR model LIKE '%$s%'
        OR description LIKE '%$s%'
        OR image LIKE '%$s%'
        OR color LIKE '%$s%'
        OR price = '$s'
    )";
}
if ($filter_brand !== '' && $filter_brand !== 'all') {
    $b = $conn->real_escape_string($filter_brand);
    $where_clauses[] = "brand = '$b'";
}

$where_sql = count($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';
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

// GROUP_CONCAT với separator '||' để tránh vấn đề dữ liệu có dấu phẩy
$sql = "
SELECT
    GROUP_CONCAT(id ORDER BY id SEPARATOR '||') AS ids,
    brand,
    GROUP_CONCAT(color ORDER BY id SEPARATOR '||') AS colors,
    model,
    scale,
    GROUP_CONCAT(price ORDER BY id SEPARATOR '||') AS prices, -- show min price for group
    GROUP_CONCAT(stock ORDER BY id SEPARATOR '||') AS stocks,
    GROUP_CONCAT(image ORDER BY id SEPARATOR '||') AS images,
    description
FROM products
{$where_sql}
GROUP BY brand, model, scale, description
ORDER BY MIN(id) DESC
LIMIT $limit OFFSET $offset
";

$result = $conn->query($sql);

if ($result === FALSE) {
    $error_message = '<div class="alert alert-danger text-center">Lỗi truy vấn: ' . htmlspecialchars($conn->error) . '</div>';
} else {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // defensive: nếu field null -> set empty string
            $row['ids']    = $row['ids'] ?? '';
            $row['colors'] = $row['colors'] ?? '';
            $row['images'] = $row['images'] ?? '';
            $row['stocks'] = $row['stocks'] ?? '';

            // tách các danh sách
            $row['ids_list']    = $row['ids'] === '' ? [] : explode('||', $row['ids']);
            $row['colors_list'] = $row['colors'] === '' ? [] : explode('||', $row['colors']);
            $row['images_list'] = $row['images'] === '' ? [] : explode('||', $row['images']);
              // Xử lý giá theo màu sắc biến thể 
              $row['prices'] = $row['prices'] ?? '';
            $row['prices_list'] = $row['prices'] === '' ? [] : explode('||', $row['prices']);
            $row['stocks_list'] = $row['stocks'] === '' ? [] : explode('||', $row['stocks']);

            // cover image = hình đầu tiên nếu có, else placeholder
            $row['image_cover'] = count($row['images_list']) ? $row['images_list'][0] : 'placeholder.png';

            // push product
            $products[] = $row;

            // unique brands
            // if (!in_array($row['brand'], $unique_brands)) {
            //     $unique_brands[] = $row['brand'];
            // }
        }
    }
    $result->free();
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JDM Model Shop - Sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-2">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" style="color: aqua; font-size: 1.3rem;" href="dashboard.php">JDM World <img
                    src="../images/drift-car.png" class="jdm-img" alt="jdm world"></a>
            <div class="d-lg-none ms-auto me-2 text-white small">
                <span><?= htmlspecialchars($_SESSION["username"] ?? '') ?></span>
            </div>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <?php if (isset($_SESSION["user_id"])): ?>
                <div class="d-none d-lg-flex ms-3 me-3 text-white align-items-center">
                    <span>Chào <strong><?= htmlspecialchars($_SESSION["username"] ?? '') ?></strong></span>
                </div>
                <?php endif; ?>
                <ul class="navbar-nav me-3">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle btn btn-secondary btn-sm text-white" href="#"
                            id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Hãng xe:
                            <?= !empty($filter_brand) && $filter_brand !== 'all' ? htmlspecialchars($filter_brand) : 'Tất cả' ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item <?= ($filter_brand == 'all' || $filter_brand == '') ? 'active' : '' ?>"
                                    href="?search=<?= urlencode($search_query) ?>&brand=all">Tất
                                    cả</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <?php foreach ($unique_brands as $brand): ?>
                            <li><a class="dropdown-item <?= ($filter_brand == $brand) ? 'active' : '' ?>"
                                    href="?search=<?= urlencode($search_query) ?>&brand=<?= urlencode($brand) ?>">
                                    <?= htmlspecialchars($brand) ?>
                                </a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                </ul>

                <form class="d-flex flex-column flex-lg-row me-3 mb-2 mb-lg-0 w-20 w-lg-auto" method="GET">
                    <input type="hidden" name="brand" value="<?= htmlspecialchars($filter_brand) ?>">
                    <input style="flex: 1 1 auto; min-width: 150px;border-radius: 5px;" type="search"
                        placeholder="Tìm tên xe/hãng" aria-label="Search" name="search"
                        value="<?= htmlspecialchars($search_query) ?>">
                    <button class="btn btn-outline-light" type="submit">Tìm</button>
                </form>

                <ul class="navbar-nav ms-lg-auto">
                    <?php if (isset($_SESSION["user_id"])): ?>

                    <li class="nav-item">
                        <a class="nav-link" href="/user/cart_item.php">
                            <img src="../images/cart.png" class="cart-img"> Giỏ hàng
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="/user/order_items.php">
                            <img src="../images/order.png" class="order-img"> Đơn hàng
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="/user/dashboard.php?view=profile">
                            <img src="../images/profile.png" class="profile-img"> Hồ sơ
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="/user/wishlist.php">
                            ❤️ Yêu thích
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-danger" href="/logout.php">
                            <img src="../images/logout.png"> Đăng xuất
                        </a>
                    </li>

                    <?php else: ?>

                    <li class="nav-item">
                        <a class="nav-link btn btn-warning text-white" href="/login.php">Đăng nhập</a>
                    </li>

                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="app-container">
        <?php
    if (isset($_GET["view"]) && $_GET["view"] === "profile") {
        include __DIR__ . "/profile.php";
    } elseif (isset($_GET["view"]) && $_GET["view"] === "edit-profile") {
        include __DIR__ . "/edit_profile.php";
    } else { ?>
        <div class="mb-3">
            <h2 class="mb-0 fw-bold page-title">Các mô hình xe JDM</h2>
        </div>

        <?= $error_message ?>

        <div class="row g-3" id="productList">
            <?php if (!empty($products)): ?>
            <?php foreach ($products as $p): 
                    // bảo đảm các key tồn tại
                    $price = '';
                    if (!empty($p['prices_list'])) {
                        $min_price = min($p['prices_list']);
                            $price =  format_currency($min_price);
                        }
                    $image_path = htmlspecialchars($p['image_cover'] ?? 'placeholder.png');
                    $full_name = htmlspecialchars(($p['brand'] ?? '') . ' ' . ($p['model'] ?? ''));
                    $short_description = truncate_description($p['description'] ?? '', 20);

                    // id dùng cho modal (lấy id đầu tiên nếu có)
                    $first_id = isset($p['ids_list'][0]) ? $p['ids_list'][0] : '0';
                    $modal_id = 'modal-' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $first_id);

                    // Lấy đánh giá cho sản phẩm
                    $reviews = [];
                    $sql_reviews = "SELECT r.rating, r.comment, r.created_at, u.username FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = $first_id ORDER BY r.created_at DESC LIMIT 10";
                    $review_result = $conn->query($sql_reviews);
                    if ($review_result) {
                        while ($rev = $review_result->fetch_assoc()) {
                            $reviews[] = $rev;
                        }
                        $review_result->free();
                    }
                    $count_reviews = 0;
                    $sql_count = "SELECT COUNT(*) as total_reviews FROM reviews WHERE product_id = $first_id";
                    $count_result = $conn->query($sql_count);
                    if ($count_result) {
                        $count_reviews = $count_result->fetch_assoc()['total_reviews'] ?? 0;
                        $count_result->free();
                    }
                ?>
            <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                <div class="card shadow-sm h-100">
                    <img src="../images/<?= $image_path ?>" class="card-img-top product-img" alt="<?= $full_name ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= $full_name ?></h5>
                        <p class="card-text text-muted">Tỉ lệ: <?= htmlspecialchars($p['scale'] ?? '') ?></p>

                        <?php
                                // tính tổng stock hiển thị (sum stocks_list)
                                $total_stock = 0;
                                if (!empty($p['stocks_list'])) {
                                    foreach ($p['stocks_list'] as $s) {
                                        $total_stock += intval($s);
                                    }
                                }
                            ?>
                        <p class="card-text text-muted">
                            <?= $total_stock > 0 ? 'Số lượng: ' . htmlspecialchars($total_stock) : '<span class="text-danger">Hết hàng</span>' ?>
                        </p>

                        <p class="fw-bold text-danger"><?= $price ?></p>

                        <p class="card-text small mb-2">
                            <strong>Chi tiết về xe:</strong> <?= htmlspecialchars($short_description) ?>
                            <?php if (strlen($p['description'] ?? '') > 20): ?>
                            <a href="#" class="text-primary text-decoration-none fw-bold" data-bs-toggle="modal"
                                data-bs-target="#<?= $modal_id ?>"> Xem thêm</a>
                            <?php endif; ?>
                        </p>

                        <button class="btn btn-primary w-100" data-bs-toggle="modal"
                            data-bs-target="#chooseColor<?= $first_id ?>">
                            Thêm vào giỏ hàng
                        </button>
                        <button class="btn btn-secondary w-100" data-bs-toggle="modal"
                            data-bs-target="#reviewModal<?= $first_id ?>">Xem đánh giá (<?= $count_reviews ?>)</button>
                        <?php if (!isset($_SESSION["user_id"])): ?>
                        <button class="btn btn-outline-danger w-100" onclick="handleWishlist(<?= $first_id ?>)">
                            ❤️ Yêu thích
                        </button>
                        <?php else: ?>
                        <button class="btn btn-outline-danger w-100" data-bs-toggle="modal"
                            data-bs-target="#wishlistModal<?= $first_id ?>">
                            ❤️ Yêu thích
                        </button>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

            <!-- Modal chọn màu -->
            <div class="modal fade" id="chooseColor<?= $first_id ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form action="/user/cart_add.php" method="POST">
                            <input type="hidden" name="choose" value="1">
                            <div class="modal-header">
                                <h5 class="modal-title"><?= $full_name ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <?php if(count($p['colors_list']) == 1):
                                $only_pid = $p['ids_list'][0];
                                $only_color = $p['colors_list'][0];
                                $only_img = $p['images_list'][0] ?? $p['image_cover'];
                                $only_stock = intval($p['stocks_list'][0] ?? 0);
                               
                                ?>
                                <input type="hidden" name="product_id" value="<?= htmlspecialchars($only_pid) ?>">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="me-3" style="width:64px;height:48px">
                                        <img src="../images/<?= htmlspecialchars($only_img) ?>"
                                            style="max-width:100%;height:100%;object-fit:cover;">

                                    </div>
                                    <div>
                                        <div class="fw-bold">Màu: <?= htmlspecialchars($only_color) ?></div>
                                        <div class="small text-muted">Còn: <?= $only_stock ?></div>
                                        <p class="fw-bold text-danger"><?= $price ?></p>
                                    </div>
                                </div>
                                <?php else: ?>
                                <?php foreach ($p['colors_list'] as $index => $color): 
                                            $pid = $p['ids_list'][$index] ?? null;
                                            $stock = isset($p['stocks_list'][$index]) ? intval($p['stocks_list'][$index]) : 0;
                                            $img = isset($p['images_list'][$index]) ? $p['images_list'][$index] : $p['image_cover'];
                                            if ($pid === null) continue;
                                            $price_item = isset($p['prices_list'][$index]) ? format_currency($p['prices_list'][$index]) : '';
                                        ?>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="form-check me-3">
                                        <input class="form-check-input" type="radio" name="product_id"
                                            value="<?= htmlspecialchars($pid) ?>" id="opt<?= htmlspecialchars($pid) ?>"
                                            <?= $index === 0 ? 'required' : '' ?>>
                                    </div>
                                    <div class="me-3" style="width:64px;height:48px">
                                        <img src="../images/<?= htmlspecialchars($img) ?>"
                                            alt="<?= htmlspecialchars($color) ?>"
                                            style="max-width:100%;height:100%;object-fit:cover;">
                                    </div>

                                    <div>
                                        <label for="opt<?= htmlspecialchars($pid) ?>" class="form-label mb-0">Màu:
                                            <?= htmlspecialchars($color) ?></label>
                                        <div class="small text-muted">Còn: <?= $stock ?></div>
                                        <div class="small text-danger fw-bold"><?= $price_item ?></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <?php endif; ?>
                                <div class="mt-3">
                                    <label class="form-label fw-bold">Số lượng</label>
                                    <input type="number" name="quantity" class="form-control" value="1" min="1"
                                        max="quantity" required>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <input type="hidden" name="choose" value="1">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                <button type="submit" class="btn btn-success">Thêm vào giỏ</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Wishlist -->
            <div class="modal fade" id="wishlistModal<?= $first_id ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form action="/user/wishlist_add.php" method="POST">
                            <input type="hidden" name="redirect" value="<?= $_SERVER['REQUEST_URI'] ?>">
                            <div class="modal-header">
                                <h5><?= $full_name ?></h5>
                                <button class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">

                                <?php if(count($p['colors_list']) == 1):
                        $only_pid = $p['ids_list'][0];
                        $only_color = $p['colors_list'][0];
                        $only_img = $p['images_list'][0];
                    ?>

                                <input type="hidden" name="product_id" value="<?= $only_pid ?>">

                                <div class="d-flex align-items-center mb-2">
                                    <img src="../images/<?= $only_img ?>" width="60" class="me-2">
                                    <div>
                                        <div class="fw-bold">Màu: <?= $only_color ?></div>
                                    </div>
                                </div>

                                <?php else: ?>
                                <?php foreach ($p['colors_list'] as $i => $color): 
                            $pid = $p['ids_list'][$i];
                            $img = $p['images_list'][$i];
                        ?>

                                <div class="d-flex align-items-center mb-2">
                                    <input class="form-check-input" type="radio" name="product_id"
                                        value="<?= htmlspecialchars($pid) ?>" <?= $i === 0 ? 'checked' : '' ?> required>
                                    <img src="../images/<?= $img ?>" width="60" class="mx-2">
                                    <div>Màu: <?= $color ?></div>
                                </div>

                                <?php endforeach; ?>
                                <?php endif; ?>

                            </div>

                            <div class="modal-footer">
                                <button class="btn btn-danger">❤️ Thêm vào yêu thích</button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal chi tiết mô tả -->
            <div class="modal fade" id="<?= $modal_id ?>" tabindex="-1" aria-labelledby="<?= $modal_id ?>Label"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="<?= $modal_id ?>Label"><?= $full_name ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <?= nl2br(htmlspecialchars($p['description'] ?? '')) ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal đánh giá -->
            <div class="modal fade" id="reviewModal<?= $first_id ?>" tabindex="-1"
                aria-labelledby="reviewModalLabel<?= $first_id ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="reviewModalLabel<?= $first_id ?>"> <?= $full_name ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <?php if (!empty($reviews)): ?>
                            <?php foreach ($reviews as $rev): ?>
                            <div class="card mb-3 shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <strong class="text-primary"><?= htmlspecialchars($rev['username']) ?></strong>
                                    <div class="d-flex align-items-center">
                                        <?php for ($i = 1; $i <= $rev['rating']; $i++): ?>
                                        <span class="text-warning">⭐</span>
                                        <?php endfor; ?>
                                        <span class="ms-2 badge bg-secondary"><?= $rev['rating'] ?>/10</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="card-text mb-2"><?= nl2br(htmlspecialchars($rev['comment'])) ?></p>
                                    <small class="text-muted">Đã đăng: <?= $rev['created_at'] ?></small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <div class="alert alert-info text-center">
                                <i class="bi bi-chat-dots"></i> Chưa có đánh giá nào cho sản phẩm này.
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        </div>
                    </div>
                </div>
            </div>

            <?php endforeach; ?>
            <?php if ($total_pages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">

                    <!-- Nút trang trước -->
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link"
                            href="?search=<?= urlencode($search_query) ?>&brand=<?= urlencode($filter_brand) ?>&page=<?= $page-1 ?>">
                            &laquo;
                        </a>
                    </li>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link"
                            href="?search=<?= urlencode($search_query) ?>&brand=<?= urlencode($filter_brand) ?>&page=<?= $i ?>">
                            <?= $i ?>
                        </a>
                    </li>
                    <?php endfor; ?>

                    <!-- Nút trang sau -->
                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-link"
                            href="?search=<?= urlencode($search_query) ?>&brand=<?= urlencode($filter_brand) ?>&page=<?= $page+1 ?>">
                            &raquo;
                        </a>
                    </li>

                </ul>
            </nav>
            <?php endif; ?>
            <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">Không có sản phẩm nào phù hợp.</div>
            </div>
            <?php endif; ?>
        </div>

        <?php } // end else view ?>
    </div>
    <script>
    function handleWishlist(productId) {
        const modal = new bootstrap.Modal(document.getElementById('wishlistModal' + productId));
        modal.show();
    }
    </script>
</body>

</html>
<?php ob_end_flush(); ?>