<?php
    session_start();
    require_once __DIR__ . "/../config/db.php";
    if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}
    if (!isset($_SESSION['user_id'])) {
    $_SESSION['wishlist_pending'] = $_POST['product_id'];
    $_SESSION['redirect_after_login'] = $_POST['redirect'] ?? '../index.php';
    header("Location: ../login.php");
    exit();
}
    $user_id = $_SESSION['user_id'];
function truncate_description($text, $limit = 20) {
    if (strlen($text) > $limit) {
        $text = substr($text, 0, $limit);
        $text = substr($text, 0, strrpos($text, ' '));
        return $text . '...';
    }
    return $text;
}
$sql = "
SELECT GROUP_CONCAT(p.id ORDER BY p.id SEPARATOR '||')
AS ids,p.brand,GROUP_CONCAT(p.color ORDER BY p.id SEPARATOR '||') 
AS colors,p.model,p.scale,GROUP_CONCAT(p.price ORDER BY p.id SEPARATOR '||') AS prices,
GROUP_CONCAT(p.stock ORDER BY p.id SEPARATOR '||') AS stocks,
GROUP_CONCAT(p.image ORDER BY p.id SEPARATOR '||') AS images,
p.description
FROM wishlist w
JOIN products p ON w.product_id = p.id
WHERE w.user_id = ?
GROUP BY p.brand, p.model, p.scale,p.description
ORDER BY MAX(w.created_at) DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$products = [];

while ($row = $result->fetch_assoc()) {
    $row['ids_list'] = explode('||', $row['ids']);
    $row['colors_list'] = explode('||', $row['colors']);
    $row['images_list'] = explode('||', $row['images']);
    $row['stocks_list'] = explode('||', $row['stocks']);
    $row['prices_list'] = explode('||', $row['prices']);
    $row['image_cover'] = $row['images_list'][0] ?? 'placeholder.png';

    $products[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách yêu thích</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>

<body class="bg-light">
    <div class="app-container">
        <a href="/index.php" class="btn btn-secondary">Quay lại</a>
        <h2 class="page-title mb-4">Danh sách đã thích</h2>
        <div class="row g-3">
            <?php foreach ($products as $index => $p): 
    $price = !empty($p['prices_list']) 
    ? number_format(min($p['prices_list']), 0, ',', '.') . '₫'
    : '';
    $full_name = $p['brand'] . " " . $p['model'];
    $first_id = $p['ids_list'][0];

    $total_stock = array_sum(array_map('intval', $p['stocks_list']));
?>

            <div class="col-lg-4 col-md-6">
                <div class="card shadow-sm h-100">
                    <img src="../images/<?= $p['image_cover'] ?>" class="card-img-top">

                    <div class="card-body">
                        <h5><?= $full_name ?></h5>
                        <p>Tỉ lệ: <?= $p['scale'] ?></p>

                        <p>
                            <?= $total_stock > 0 
                    ? "Số lượng: $total_stock" 
                    : "<span class='text-danger'>Hết hàng</span>" ?>
                        </p>

                        <p class="text-danger fw-bold"><?= $price ?></p>

                        <?php
                        $short_description = truncate_description($p['description'], 20);
                        $modal_id = 'modal-' . $index;
                        ?>

                        <p class="card-text small mb-2">
                            <strong>Chi tiết:</strong> <?= htmlspecialchars($short_description) ?>

                            <?php if (strlen($p['description']) > 20): ?>
                            <a href="javascript:void(0)" class="text-primary fw-bold" data-bs-toggle="modal"
                                data-bs-target="#<?= $modal_id ?>">
                                Xem thêm
                            </a>
                            <?php endif; ?>
                        </p>
                        <button class="btn btn-primary w-100 mb-2" data-bs-toggle="modal"
                            data-bs-target="#chooseColor<?= $index ?>">
                            Thêm vào giỏ
                        </button>

                        <a href="reviews.php?product_id=<?= $first_id ?>&back_url=<?= urldecode($_SERVER['REQUEST_URI']) ?>"
                            class="btn btn-secondary w-100 mb-2">
                            Xem đánh giá
                        </a>

                        <button class="btn btn-danger w-100 btn-remove" data-id="<?= $first_id ?>">
                            Xóa khỏi yêu thích
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="chooseColor<?= $index ?>">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form action="/user/cart_add.php" method="POST">
                            <input type="hidden" name="choose" value="1">

                            <div class="modal-header">
                                <h5><?= $full_name ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">

                                <?php if(count($p['colors_list']) == 1):
                        $only_pid = $p['ids_list'][0];
                        $only_color = $p['colors_list'][0];
                        $only_img = $p['images_list'][0];
                        $only_stock = intval($p['stocks_list'][0]);
                        $only_price = number_format($p['prices_list'][0],0,',','.') . '₫';
                    ?>

                                <input type="hidden" name="product_id" value="<?= $only_pid ?>">

                                <div class="d-flex align-items-center mb-2">
                                    <img src="../images/<?= $only_img ?>" width="60" class="me-2">
                                    <div>
                                        <div class="fw-bold">Màu: <?= $only_color ?></div>
                                        <div class="small text-muted">Còn: <?= $only_stock ?></div>
                                        <div class="text-danger fw-bold"><?= $only_price ?></div>
                                    </div>
                                </div>

                                <?php else: ?>

                                <?php foreach ($p['colors_list'] as $i => $color): 
                        $pid = $p['ids_list'][$i];
                        $stock = intval($p['stocks_list'][$i]);
                        $img = $p['images_list'][$i];
                        $price_item = number_format($p['prices_list'][$i],0,',','.') . '₫';
                    ?>

                                <div class="d-flex align-items-center mb-2">
                                    <input type="radio" name="product_id" value="<?= $pid ?>" required>
                                    <img src="../images/<?= $img ?>" width="60" class="mx-2">

                                    <div>
                                        <div>Màu: <?= $color ?></div>
                                        <div class="small">Còn: <?= $stock ?></div>
                                        <div class="text-danger fw-bold"><?= $price_item ?></div>
                                    </div>
                                </div>

                                <?php endforeach; ?>
                                <?php endif; ?>

                                <input type="number" name="quantity" value="1" min="1" class="form-control mt-2">
                            </div>

                            <div class="modal-footer">
                                <button class="btn btn-success">Thêm vào giỏ</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php foreach ($products as $index => $p): 
    $full_name = $p['brand'] . " " . $p['model'];
    $modal_id = 'modal-' . $index;
?>

        <div class="modal fade" id="<?= $modal_id ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><?= $full_name ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <?= nl2br(htmlspecialchars($p['description'])) ?>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>

        <?php endforeach; ?>
    </div>
    <script>
    document.querySelectorAll('.btn-remove').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;

            fetch('wishlist_remove.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'product_id=' + id
                })
                .then(res => res.text())
                .then(data => {
                    if (data === "OK") {
                        this.closest('.col-lg-4').remove();
                    } else {
                        alert("Xóa thất bại!");
                    }
                });
        });
    });
    </script>
</body>

</html>