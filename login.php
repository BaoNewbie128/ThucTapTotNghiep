<?php
session_start();
include "config/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $login = trim($_POST["login"]); // username hoặc email
    $password = $_POST["password"];
    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user["password"])) {
            // Lưu session
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["role"] = $user["role"];
            if ($user["role"] == "admin") {
                header("Location: admin/admin_dashboard.php");
            } else {
                header("Location: user/dashboard.php");
            }
            exit;
        }
    }

    $error = "Sai thông tin đăng nhập!";
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
    <title>Đăng nhập - JDM World</title>
    <style>
        .login-container {
            min-height: calc(100vh - 200px);
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(26, 26, 46, 0.95) 0%, rgba(22, 33, 62, 0.95) 100%), 
                        url('images/BackGround.jpg') center/cover no-repeat;
            padding: 15px;
            position: relative;
            overflow: hidden;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 50%, rgba(0, 212, 255, 0.08) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(0, 212, 255, 0.05) 0%, transparent 50%);
            pointer-events: none;
            animation: gradientShift 15s ease-in-out infinite;
        }

        @keyframes gradientShift {
            0%, 100% {
                transform: translate(0, 0);
            }
            50% {
                transform: translate(20px, -10px);
            }
        }

        .login-form {
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 1;
            animation: slideInUp 0.6s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card {
            background: rgba(255, 255, 255, 0.98);
            border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .card:hover {
            box-shadow: 0 25px 70px rgba(0, 212, 255, 0.2), 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .card-title {
            color: var(--accent);
            font-size: 1.8rem;
            font-weight: 900;
            letter-spacing: 0.5px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            color: #333;
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 10px;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.95);
            border: 2px solid #e6e9ee;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: #fff;
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(0, 212, 255, 0.15);
            transform: translateY(-2px);
        }

        .form-control::placeholder {
            color: #ccc;
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary) 0%, #0099cc 100%);
            color: #000;
            font-weight: 700;
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            width: 100%;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 8px 20px rgba(0, 212, 255, 0.3);
            cursor: pointer;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(0, 212, 255, 0.4);
        }

        .btn-submit:active {
            transform: translateY(-1px);
        }

        .text-center {
            text-align: center;
        }

        .link-text {
            color: #666;
            margin-top: 16px;
            font-size: 0.9rem;
        }

        .link-text a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
        }

        .link-text a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: width 0.3s ease;
        }

        .link-text a:hover::after {
            width: 100%;
        }

        .btn-secondary-link {
            background: rgba(0, 212, 255, 0.1);
            color: var(--primary);
            border: 2px solid var(--primary);
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            width: 100%;
            text-align: center;
            transition: all 0.3s ease;
            margin-top: 12px;
        }

        .btn-secondary-link:hover {
            background: var(--primary);
            color: #000;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 212, 255, 0.3);
        }

        .credentials-info {
            background: linear-gradient(135deg, rgba(0, 212, 255, 0.05) 0%, rgba(0, 212, 255, 0.02) 100%);
            border: 1px solid rgba(0, 212, 255, 0.2);
            border-radius: 8px;
            padding: 16px;
            margin-top: 20px;
            font-size: 0.85rem;
        }

        .credentials-info p {
            margin-bottom: 8px;
            color: #333;
        }

        .credentials-info strong {
            color: var(--primary);
            font-weight: 700;
        }
    </style>
</head>

<body class="bg-light">
    <div class="login-container">
        <div class="login-form">
            <div class="card">
                <div class="card-body p-5">
                    <h2 class="card-title text-center mb-4">Đăng nhập</h2>

                    <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <strong>Lỗi:</strong> <?php echo $error; ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label for="login" class="form-label">Email hoặc Username</label>
                            <input type="text" class="form-control" id="login" name="login"
                                placeholder="Nhập email hoặc username" required autofocus>
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">Mật khẩu</label>
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Nhập mật khẩu" required>
                        </div>

                        <button type="submit" class="btn btn-submit">Đăng nhập</button>
                    </form>

                    <p class="link-text text-center">
                        Bạn chưa có tài khoản? <a href="register.php">Đăng ký tại đây</a>
                    </p>

                    <a href="index.php" class="btn-secondary-link">← Quay lại</a>

                    <div class="credentials-info">
                        <p><strong>Tài khoản admin:</strong> baobao</p>
                        <p><strong>Mật khẩu:</strong> 12345678</p>
                        <hr style="margin: 10px 0; border: none; border-top: 1px solid rgba(0, 212, 255, 0.2);">
                        <p><strong>Tài khoản user:</strong> tientien</p>
                        <p><strong>Mật khẩu:</strong> 123456789</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>