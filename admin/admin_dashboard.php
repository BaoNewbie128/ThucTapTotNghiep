<?php ob_start(); ?>
<?php 
include __DIR__ . "/../includes/admin_auth_check.php";
    include __DIR__ . "/../config/db.php";
    if (isset($_GET["view"]) && $_GET["view"] === "add-post" 
    && $_SERVER['REQUEST_METHOD'] === 'POST') {

    include __DIR__ . "/add_post.php";
    exit;
    }
    if (isset($_GET["view"]) && $_GET["view"] === "edit-post" 
    && $_SERVER['REQUEST_METHOD'] === 'POST') {

    include __DIR__ . "/edit_post.php";
    exit;
    }
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        if (isset($_GET["view"]) && $_GET["view"] === "delete-post") {

    include __DIR__ . "/delete_post.php";
    exit;
        }
    }
    if(isset($_GET['view']) &&  $_GET['view'] === "edit_order_status"
    && $_SERVER['REQUEST_METHOD']=== 'POST'){
        include __DIR__ . "/update_order_status.php";
        exit;
    }
    if(isset($_GET['view']) && $_GET['view'] === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST'){
    include __DIR__ . "/edit_product.php";
    exit;
    }
    if (isset($_GET["view"]) && $_GET["view"] === "delete" && $_SERVER['REQUEST_METHOD'] === 'POST') {
    include __DIR__ . "/delete_product.php";
    exit;
}
    // Truy vấn tổng số lượng sản phẩm 
    $sql = "SELECT COUNT(*) AS total_products FROM products";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $total_products = $row['total_products'];
        // Truy vấn tổng số lượng người dùng(customer)
    $sql2 = "SELECT COUNT(*) AS total_users FROM users WHERE role='customer'";
    $result2 = $conn->query($sql2);
    $row2 = $result2->fetch_assoc();
    $total_users = $row2['total_users'];
    // Truy vấn tổng số lượng đơn hàng
    $sql3 = "SELECT COUNT(*) AS total_orders FROM orders";
    $result3 = $conn->query($sql3);
    $row3 = $result3->fetch_assoc();
    $total_orders = $row3['total_orders'];
    // Truy vấn tổng số lượng admin trong hệ thống
    $sql4 = "SELECT COUNT(*) AS total_admins FROM users WHERE role='admin'";
    $result4 = $conn->query($sql4);
    $row4 = $result4->fetch_assoc();
    $total_admins = $row4['total_admins'];
    // Truy vấn tổng số lượng các đánh giá của khách hàng 
    $sql5 = "SELECT COUNT(*) AS total_reviews FROM reviews";
    $result5 = $conn->query($sql5);
    $row5 = $result5->fetch_assoc();
    $total_reviews = $row5['total_reviews'];

    // Truy vấn sản phẩm sắp hết hàng (stock < 10)
    $low_stock_sql = "SELECT id, brand, model, stock FROM products WHERE stock < 10 ORDER BY stock ASC LIMIT 10";
    $low_stock_result = $conn->query($low_stock_sql);
    $low_stock_products = [];
    if ($low_stock_result) {
        while ($row = $low_stock_result->fetch_assoc()) {
            $low_stock_products[] = $row;
        }
        $low_stock_result->free();
    }
    // $conn->close();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Trang Chủ - Admin - JDM World</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>

<body>
    <?php include __DIR__ . "/../includes/header.php"; ?>

    <div class="app-container container mt-4">
        <div class="row g-4">
            <div class="col-md-3 mb-3">
                <aside class="card p-3 shadow-sm">
                    <div class="mb-3 text-center">
                        <strong>Xin chào</strong>

                        <p class="mb-0"> <?php echo htmlspecialchars($_SESSION["username"]); ?></p>
                        <small class="muted">Vai trò: <?php echo htmlspecialchars($_SESSION["role"]); ?> <img
                                src="../images/admin.png" alt="admin" class="profile-img"></small>
                    </div>
                    <nav class="nav flex-column">
                        <a class="nav-link nav-btn" href="admin_dashboard.php?view=products"><img
                                src="../images/product.png" alt="sản phẩm" class="profile-img"> Quản lý sản phẩm</a>

                        <a class="nav-link nav-btn" href="admin_dashboard.php?view=orders"><img
                                src="../images/order_admin.png" alt="đơn hàng" class="profile-img"> Quản lý đơn hàng</a>
                        <a class="nav-link nav-btn" href="admin_dashboard.php?view=customers"><img
                                src="../images/profile.png" alt="khách hàng" class="profile-img"> Quản lý khách hàng</a>
                        <a class="nav-link nav-btn" href="admin_dashboard.php?view=reviews">
                            <img src="../images/review.png" alt="đánh giá" class="profile-img"> Quản lý đánh giá</a>
                        <a class="nav-link nav-btn" href="admin_dashboard.php?view=reports">
                            <img src="../images/report.png" alt="báo cáo" class="profile-img"> Báo cáo thống kê</a>
                        <a class="nav-link nav-btn" href="admin_dashboard.php?view=users">
                            <img src="../images/admin.png" alt="người dùng" class="profile-img"> Quản lý người dùng</a>
                        <a class="nav-link nav-btn" href="admin_dashboard.php?view=posts">
                            📰 Quản lý bài viết
                        </a>
                    </nav>
                </aside>
            </div>

            <!-- Main Content -->
            <main class="col-md-9" style="flex: 1;">
                <?php 
                    if(isset($_GET["view"]) && $_GET["view"] === "products") {
                        include __DIR__ . "/product_management.php";
                    }
                    elseif(isset($_GET["view"]) && $_GET["view"] === "add") {
                        include __DIR__ . "/add_product.php";
                    }elseif(isset($_GET["view"]) && $_GET["view"] === "edit") {
                        include __DIR__ . "/edit_product.php";
                    }elseif(isset($_GET["view"]) && $_GET["view"] === "delete") {
                        echo "<div class='alert alert-danger'>Thao tác xóa chỉ được thực hiện bằng POST.</div>";
                    }elseif(isset($_GET["view"]) && $_GET["view"] === "orders") {
                        include __DIR__ . "/order_management.php";
                    }elseif(isset($_GET["view"]) && $_GET["view"] === "order_items") {
                        include __DIR__ . "/order_items_management.php";
                    }
                    elseif(isset($_GET["view"]) && $_GET["view"] === "reviews") {
                        include __DIR__ . "/reviews_management.php";
                    }
                    elseif(isset($_GET["view"]) && $_GET["view"] === "edit_order_status") {
                        include __DIR__ . "/edit_order_status.php";
                    }
                    elseif(isset($_GET["view"]) && $_GET["view"] === "customers") {
                        include __DIR__ . "/customer_management.php";
                    }
                    elseif(isset($_GET["view"]) && $_GET["view"] === "users") {
                        include __DIR__ . "/user_management.php";
                    }
                    elseif(isset($_GET["view"]) && $_GET["view"] === "edit-user") {
                        include __DIR__ . "/edit_user.php";
                    }
                    elseif(isset($_GET["view"]) && $_GET["view"] === "add-user") {
                        include __DIR__ . "/add_user.php";
                    }
                    elseif(isset($_GET["view"]) && $_GET["view"] === "delete-user") {
                        include __DIR__ . "/delete_user.php";
                    }
                    elseif(isset($_GET["view"]) && $_GET["view"] === "reports") {
                        include __DIR__ . "/reports.php";
                    }
                    elseif(isset($_GET["view"]) && $_GET["view"] === "posts") {
                        include __DIR__ . "/post_management.php";
                    }
                    elseif(isset($_GET["view"]) && $_GET["view"] === "add-post") {
                        include __DIR__ . "/add_post_form.php";
                    }
                    elseif(isset($_GET["view"]) && $_GET["view"] === "edit-post") {
                        include __DIR__ . "/edit_post.php";
                    }
                    else {
                ?>
                <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
                    <div class="col">
                        <div class="card text-center p-3 shadow-sm">
                            <h3 class="h6">Tổng sản phẩm</h3>
                            <p class="display-6 text-danger mb-0"><?php echo htmlspecialchars($total_products); ?></p>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card text-center p-3 shadow-sm">
                            <h3 class="h6">Đơn hàng mới</h3>
                            <p class="display-6 text-primary mb-0"><?php echo htmlspecialchars($total_orders); ?></p>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card text-center p-3 shadow-sm">
                            <h3 class="h6">Số lượng khách hàng</h3>
                            <p class="display-6 text-success mb-0"><?php echo htmlspecialchars($total_users); ?></p>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card text-center p-3 shadow-sm">
                            <h3 class="h6">Số lượng admin trong máy chủ</h3>
                            <p class="display-6 text-success mb-0"><?php echo htmlspecialchars($total_admins); ?></p>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card text-center p-3 shadow-sm">
                            <h3 class="h6">Số lượng các đánh giá</h3>
                            <p class="display-6 text-success mb-0"><?php echo htmlspecialchars($total_reviews); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Cảnh báo kho hàng -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Cảnh báo kho hàng</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($low_stock_products)): ?>
                        <p class="text-muted">Các sản phẩm sau có số lượng tồn kho dưới 10:</p>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Mẫu xe</th>
                                        <th>Tồn kho</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($low_stock_products as $product): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['brand'] . ' ' . $product['model']); ?>
                                        </td>
                                        <td><span class="badge bg-danger"><?php echo $product['stock']; ?></span></td>
                                        <td>
                                            <a href="admin_dashboard.php?view=edit&id=<?php echo $product['id']; ?>"
                                                class="btn btn-sm btn-primary">Cập nhật</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <p class="text-success"><i class="bi bi-check-circle"></i> Tất cả sản phẩm đều có đủ tồn kho.
                        </p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php  
                    }
                ?>
            </main>
        </div>
</body>

</html>
<?php ob_end_flush(); ?>