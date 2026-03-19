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
        background: linear-gradient(135deg, rgba(26, 26, 46, 0.8) 0%, rgba(22, 33, 62, 0.8) 100%), url('images/BackGround.jpg') center/cover no-repeat;
        padding: 15px;
    }
    </style>
</head>

<body class="bg-light">
    <div class="login-container">
        <div class="card p-4 shadow-lg" style="width: 100%; max-width: 400px;">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Đăng nhập</h2>

                <?php if (!empty($error)):?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label for="login" class="form-label fw-600">Email hoặc Username</label>
                        <input type="text" class="form-control" id="login" name="login"
                            placeholder="Nhập email hoặc username" required>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label fw-600">Mật khẩu</label>
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="Nhập mật khẩu" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Đăng nhập</button>
                    <p class="mt-2">Bạn chưa có tài khoản ? <a class="text-decoration-none" href="register.php">Đăng ký
                            tại đây</a></p>
                    <a href="index.php" class="btn btn-outline-secondary w-100 mt-2 py-2"> Quay lại</a>
                    <p>Tài khoản admin: <strong>baobao </strong> password: <strong>12345678 </strong> </p>
                    <p>Tài khoản user: <strong>tientien </strong> password: <strong>123456789 </strong> </p>
                </form>
            </div>
        </div>
    </div>
</body>

</html>