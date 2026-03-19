<?php
require __DIR__ . "/../config/db.php";

$message = "";
$success = false;

// Khi submit form
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $email    = trim($_POST["email"]);
    $phone    = trim($_POST["phone"]);
    $address  = trim($_POST["address"]);
    $role     = trim($_POST["role"]);
    $password = trim($_POST["password"]);

    if ($username === "" || $email === "" || $password === "") {
        $message = "Username, Email và Password không được để trống!";
    } else {
        // Kiểm tra email trùng
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            $message = "Email này đã tồn tại!";
        } else {
            // Hash mật khẩu
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            // Insert user
            $stmt = $conn->prepare(
                "INSERT INTO users (username,email,phone,address,role,password,created_at) 
                 VALUES (?,?,?,?,?,?,NOW())"
            );
            $stmt->bind_param("ssssss", $username, $email, $phone, $address, $role, $password_hash);

            if ($stmt->execute()) {
                header("Location: admin_dashboard.php?view=users&added=1");
                exit;
            } else {
                $message = "Lỗi thêm người dùng: " . $conn->error;
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>

<div class="container mt-4">
    <h3 class="mb-3">Thêm người dùng mới</h3>

    <?php if ($message != ""): ?>
    <div class="alert alert-danger"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="mb-3">
            <label class="form-label">Username *</label>
            <input type="text" name="username" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Email *</label>
            <input type="email" name="email" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Mật khẩu *</label>
            <input type="password" name="password" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Số điện thoại</label>
            <input type="text" name="phone" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Địa chỉ</label>
            <textarea name="address" class="form-control" rows="3"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Vai trò</label>
            <select name="role" class="form-select">
                <option value="customer">Customer</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Thêm</button>
        <a href="admin_dashboard.php?view=users" class="btn btn-secondary">Hủy</a>
    </form>
</div>