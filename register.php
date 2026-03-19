<?php
include  "config/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"]?? "");
    $email = trim($_POST["email"] ?? "");
    $password = ($_POST["password"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $role = "customer"; // mặc định khách hàng
    if ($username ==="" || $email ==="" || $password==="") {
        $error = "Vui lòng nhập đầy đủ thông tin!";
    }
    // Kiểm tra định dạng email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email không đúng định dạng!";
    }
    // Kiểm tra độ dài mật khẩu
    elseif (strlen($password) < 6) {
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
        background: linear-gradient(135deg, rgba(26, 26, 46, 0.8) 0%, rgba(22, 33, 62, 0.8) 100%), url('images/BackGround.jpg') center/cover no-repeat;
        padding: 15px;
    }
    </style>
</head>

<body class="bg-light">
    <div class="register-container">
        <div class="card p-4 shadow-lg" style="width: 100%; max-width: 400px;">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Đăng ký</h2>
                <?php if (!empty($error)):?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Tên đăng nhập</label>
                        <input type="text" class="form-control" id="username" name="username"
                            placeholder="Nhập tên đăng nhập" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Nhập email"
                            required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="Nhập mật khẩu" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Số điện thoại</label>
                        <input type="text" class="form-control" id="phone" name="phone"
                            placeholder="Nhập số điện thoại">
                    </div>
                    <div class="mb-4">
                        <label for="address" class="form-label">Địa chỉ</label>
                        <textarea class="form-control" id="address" name="address" placeholder="Nhập địa chỉ"
                            rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Đăng ký</button>
                    <a href="index.php" class="btn btn-outline-secondary w-100 mt-3 py-2"> Quay lại</a>
                </form>
            </div>
        </div>
    </div>

</body>

</html>