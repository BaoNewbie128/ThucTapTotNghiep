<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>jdm world</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
    .header-logo-container {
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .header-logo-container:hover {
        transform: scale(1.02);
    }

    .nav-separator {
        width: 1px;
        height: 24px;
        background: rgba(255, 255, 255, 0.2);
        margin: 0 8px;
    }

    .nav-badge {
        display: inline-block;
        background: linear-gradient(135deg, var(--danger) 0%, #ff6b7b 100%);
        color: white;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        font-size: 0.75rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 4px;
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            box-shadow: 0 0 0 0 rgba(0, 212, 255, 0.7);
        }

        50% {
            box-shadow: 0 0 0 8px rgba(0, 212, 255, 0);
        }
    }
    </style>
</head>

<body>

    <header class="transparent-header"
        style="padding: 12px 15px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">

        <div class="header-logo-container">
            <h1 class="brand-title" style="margin: 0; min-width: 140px;">JDM WORLD</h1>
            <img src="/images/drift-car.png" class="jdm-img" alt="jdm world" style="height: 40px; width: 40px;">
        </div>

        <nav style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap; flex: 1; justify-content: flex-end;">

            <?php if (!isset($_SESSION["user_id"])): ?>
            <a href="/index.php" class="nav-btn" style="padding: 6px 10px; font-size: 0.9rem; margin: 0;">
                <img src="/images/home.png" alt="trang chủ" class="profile-img" style="margin-right: 4px;"> Trang chủ
            </a>
            <a href="/login.php" class="nav-btn" style="padding: 6px 10px; font-size: 0.9rem; margin: 0;">
                <img src="/images/login.png" alt="đăng nhập" class="profile-img" style="margin-right: 4px;">
                Đăng nhập
            </a>
            <a href="/register.php" class="nav-btn primary-cta"
                style="padding: 6px 10px; font-size: 0.9rem; margin: 0;">
                <img src="/images/register.png" alt="đăng ký" class="profile-img" style="margin-right: 4px;">
                Đăng ký
            </a>
            <?php else: ?>
            <?php 
                $homeUrl = "/user/dashboard.php";
                 if(isset($_SESSION["role"]) && $_SESSION["role"] === "admin"){
                 $homeUrl = "/admin/admin_dashboard.php";
             }
            ?>
            <a href="<?= $homeUrl ?>" class="nav-btn" style="padding: 6px 10px; font-size: 0.9rem; margin: 0;">
                <img src="/images/home.png" alt="trang chủ" class="profile-img" style="margin-right: 4px;"> Trang chủ
            </a>
            <div class="nav-separator"></div>
            <a href="/user/profile.php" class="nav-btn" style="padding: 6px 10px; font-size: 0.9rem; margin: 0;">
                <img src="/images/profile.png" alt="hồ sơ" class="profile-img" style="margin-right: 4px;"> Hồ sơ
            </a>
            <a href="/user/orders.php" class="nav-btn" style="padding: 6px 10px; font-size: 0.9rem; margin: 0;">
                <img src="/images/order.png" alt="đơn hàng" class="profile-img" style="margin-right: 4px;"> Đơn hàng
            </a>
            <a href="/logout.php" class="nav-btn"
                style="background: linear-gradient(135deg, var(--danger) 0%, #ff6b7b 100%); border-color: var(--danger); padding: 6px 10px; font-size: 0.9rem; margin: 0;">
                <img src="/images/logout.png" alt="đăng xuất" class="profile-img" style="margin-right: 4px;"> Đăng xuất
            </a>
            <?php endif; ?>
        </nav>
    </header>

    <main>