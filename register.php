<?php
include  "config/db.php";
require_once __DIR__ . "/validation.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"]?? "");
    $email = trim($_POST["email"] ?? "");
    $password = ($_POST["password"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $role = "customer"; // mặc định khách hàng
    if (!\Validator::required($username) || !\Validator::required($email) || !\Validator::required($password)) {
        $error = "Vui lòng nhập đầy đủ thông tin!";
    }
    // Kiểm tra định dạng email
    elseif (!\Validator::email($email)) {
        $error = "Email không đúng định dạng!";
    }
    // Kiểm tra độ dài mật khẩu dấu gạch ở Validator là để lấy gloBal không báo lỗi 
    elseif (!\Validator::minLength($password, 6)) {
        $error = "Mật khẩu phải có ít nhất 6 ký tự!";
    }else{
 // kiểm tra email/username trùng
    $check = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $check->bind_param("ss", $email, $username);
    $check->execute();
    $exists = $check->get_result();

    if ($exists->num_rows > 0) {
        $error = "Email hoặc Username đã tồn tại!";
    } else {
        $password_hashed = password_hash($password,PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $username, $email, $password_hashed, $phone, $address, $role);

        if ($stmt->execute()) {
            header("Location: login.php?registered=1");
            exit;
        } else {
            $error = "Đăng ký thất bại: " . $conn->error;
            }
        }
    } 
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
    <title>Đăng ký - JDM World</title>
    <style>
        .register-container {
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

        .register-container::before {
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

        .register-form {
            width: 100%;
            max-width: 460px;
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
            margin-bottom: 18px;
        }

        .form-label {
            color: #333;
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 8px;
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

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body class="bg-light">
    <div class="register-container">
        <div class="register-form">
            <div class="card">
                <div class="card-body p-5">
                    <h2 class="card-title text-center mb-4">Tạo tài khoản</h2>

                    <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <strong>Lỗi:</strong> <?php echo $error; ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label for="username" class="form-label">Tên đăng nhập</label>
                            <input type="text" class="form-control" id="username" name="username"
                                placeholder="Nhập tên đăng nhập" required autofocus>
                        </div>

                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                placeholder="Nhập email" required>
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">Mật khẩu</label>
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Nhập mật khẩu (tối thiểu 6 ký tự)" required>
                        </div>

                        <div class="form-group">
                            <label for="phone" class="form-label">Số điện thoại <span style="color: #999;">(tùy chọn)</span></label>
                            <input type="text" class="form-control" id="phone" name="phone"
                                placeholder="Nhập số điện thoại">
                        </div>

                        <div class="form-group">
                            <label for="address" class="form-label">Địa chỉ <span style="color: #999;">(tùy chọn)</span></label>
                            <textarea class="form-control" id="address" name="address" 
                                placeholder="Nhập địa chỉ" rows="3"></textarea>
                        </div>

                        <button type="submit" class="btn btn-submit">Tạo tài khoản</button>
                        <a href="index.php" class="btn-secondary-link">← Quay lại</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>

</html>