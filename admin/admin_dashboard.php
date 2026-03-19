<?php include __DIR__ . "/../includes/auth_check.php";
    include __DIR__ . "/../config/db.php";
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
                        <a class="nav-link nav-btn" href="admin_dashboard.php?view=users">
                            <img src="../images/admin.png" alt="người dùng" class="profile-img"> Quản lý người dùng</a>
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
                        include __DIR__ . "/delete_product.php";
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
                <?php  
                    }
                ?>
            </main>
        </div>
</body>

</html>