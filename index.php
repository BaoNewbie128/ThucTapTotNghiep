<?php 
include "includes/header.php";
 ?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JDM World - Mô hình xe JDM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
    .hero-section {
        background: linear-gradient(135deg, rgba(26, 26, 46, 0.9) 0%, rgba(22, 33, 62, 0.9) 100%),
            url('images/BackGround.jpg') center/cover no-repeat;
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at 20% 50%, rgba(0, 212, 255, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 80% 80%, rgba(0, 212, 255, 0.05) 0%, transparent 50%);
        pointer-events: none;
        animation: gradientShift 15s ease-in-out infinite;
    }

    @keyframes gradientShift {

        0%,
        100% {
            transform: translate(0, 0);
        }

        50% {
            transform: translate(20px, -10px);
        }
    }

    .hero-content {
        text-align: center;
        color: #fff;
        position: relative;
        z-index: 1;
        animation: fadeInUp 0.8s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .hero-content h1 {
        font-size: 3.5rem;
        font-weight: 900;
        margin-bottom: 20px;
        letter-spacing: 3px;
        background: linear-gradient(135deg, #00d4ff 0%, #33e0ff 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-shadow: 0 4px 12px rgba(0, 212, 255, 0.3);
        animation: slideDown 0.8s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .hero-content p {
        font-size: 1.3rem;
        margin-bottom: 30px;
        text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.5);
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
        animation: slideUp 0.8s ease-out 0.2s both;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .hero-img {
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-20px);
        }
    }

    .cta-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
        animation: slideUp 0.8s ease-out 0.4s both;
    }

    .cta-btn {
        padding: 12px 30px;
        font-size: 1rem;
        font-weight: 700;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        position: relative;
        overflow: hidden;
    }

    .cta-btn::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }

    .cta-btn:hover::before {
        width: 300px;
        height: 300px;
    }

    .cta-primary {
        background: linear-gradient(135deg, #00d4ff 0%, #0099cc 100%);
        color: #000;
    }

    .cta-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 16px 32px rgba(0, 212, 255, 0.4);
        color: #000;
    }

    .cta-secondary {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        border: 2px solid rgba(0, 212, 255, 0.5);
    }

    .cta-secondary:hover {
        background: rgba(0, 212, 255, 0.2);
        border-color: #00d4ff;
        transform: translateY(-3px);
        box-shadow: 0 16px 32px rgba(0, 212, 255, 0.3);
    }
    </style>
</head>

<body class="bg-light">

    <div class="hero-section">
        <div class="hero-content">
            <div class="mb-4">
                <img src="images/drift-car.png" class="hero-img" alt="jdm world" style="height: 100px; width: auto;">
            </div>
            <h1 class="display-4 fw-bold mb-3">JDM WORLD</h1>
            <p class="lead">Khám phá bộ sưu tập mô hình xe JDM độc đáo và tuyệt vời nhất</p>

            <div class="cta-buttons">
                <?php if (!isset($_SESSION["user_id"])): ?>
                <a href="/login.php" class="cta-btn cta-primary">Đăng nhập ngay</a>
                <a href="/register.php" class="cta-btn cta-secondary">Tạo tài khoản</a>
                <?php else: ?>
                <a href="/user/dashboard.php" class="cta-btn cta-primary">Vào bộ sưu tập</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>
<?php include "includes/footer.php"; ?>