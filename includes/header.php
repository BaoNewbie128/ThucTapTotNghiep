<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>jdm world</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>

<body>

    <header class="transparent-header"
        style="padding: 12px 15px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
        <h1 class="brand-title" style="margin: 0; min-width: 140px;">JDM WORLD <img src="../images/drift-car.png"
                class="jdm-img" alt="jdm world"></h1>

        <nav style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap; flex: 1; justify-content: flex-end;">


            <?php if (!isset($_SESSION["user_id"])): ?>
            <a href="/index.php" class="nav-btn" style="padding: 6px 10px; font-size: 0.9rem; margin: 0;">
                <img src="../images/home.png" alt="trang chủ" class="profile-img"> Trang chủ</a>
            <a href="/login.php" class="nav-btn" style="padding: 6px 10px; font-size: 0.9rem; margin: 0;"><img
                    src="../images/login.png" alt="đăng nhập" class="profile-img">
                Đăng nhập</a>
            <a href="/register.php" class="nav-btn primary-cta"
                style="padding: 6px 10px; font-size: 0.9rem; margin: 0;"><img src="../images/register.png" alt="đăng ký"
                    class="profile-img">
                Đăng ký</a>
            <?php else: ?>
            <a href="admin_dashboard.php" class="nav-btn" style="padding: 6px 10px; font-size: 0.9rem; margin: 0;">
                <img src="../images/home.png" alt="trang chủ" class="profile-img"> Trang
                chủ</a>
            <a href="/logout.php" class="nav-btn"
                style="background: var(--danger); border-color: var(--danger); padding: 6px 10px; font-size: 0.9rem; margin: 0;">
                <img src="../images/logout.png" alt="đăng xuất" class="profile-img"> Đăng xuất</a>
            <?php endif; ?>
        </nav>
    </header>

    <main>