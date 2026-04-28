<?php
require_once __DIR__ . '/../includes/admin_auth_check.php';
require __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../validation.php";

// Lấy ID user
if (!isset($_GET["user_id"]) || empty($_GET["user_id"])) {
    die("Không tìm thấy người dùng.");
}

$user_id = intval($_GET["user_id"]);
$message = "";

// Lấy thông tin user hiện tại
$stmt = $conn->prepare("SELECT id, username, email, phone, address, role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Người dùng không tồn tại.");
}

$user = $result->fetch_assoc();
$stmt->close();

// Xử lý khi submit form
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $email    = trim($_POST["email"]);
    $phone    = trim($_POST["phone"]);
    $address  = trim($_POST["address"]);
    $role     = trim($_POST["role"]);
    $password = $_POST["password"] ?? "";

    if ($username === "" || $email === "") {
        $message = "Username và Email không được để trống!";
    } elseif ($password !== "" && !\Validator::minLength($password, 6)) {
        $message = "Mật khẩu mới phải có ít nhất 6 ký tự!";
    } else {
        if ($password !== "") {
            // Nếu admin nhập mật khẩu mới thì hash và ghi đè mật khẩu cũ.
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET username=?, email=?, phone=?, address=?, role=?, password=? WHERE id=?");
            $update->bind_param("ssssssi", $username, $email, $phone, $address, $role, $password_hash, $user_id);
        } else {
            $update = $conn->prepare("UPDATE users SET username=?, email=?, phone=?, address=?, role=? WHERE id=?");
            $update->bind_param("sssssi", $username, $email, $phone, $address, $role, $user_id);
        }

        if ($update->execute()) {
            header("Location: admin_dashboard.php?view=users&updated=1");
            exit;
        } else {
            $message = "Lỗi cập nhật: " . $conn->error;
        }
        $update->close();
    }
}
?>

<div class="container mt-4">
    <h3 class="mb-3">Sửa thông tin người dùng</h3>

    <?php if ($message != ""): ?>
    <div class="alert alert-danger"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Mật khẩu mới</label>
            <input type="password" name="password" class="form-control" placeholder="Để trống nếu không đổi mật khẩu">
            <div class="form-text">Nhập ít nhất 6 ký tự nếu muốn ghi đè mật khẩu hiện tại.</div>
        </div>

        <div class="mb-3">
            <label class="form-label">Số điện thoại</label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Địa chỉ</label>
            <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($user['address']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Vai trò</label>
            <select name="role" class="form-select">
                <option value="customer" <?= $user["role"] === "customer" ? "selected" : "" ?>>Customer</option>
                <option value="admin" <?= $user["role"] === "admin" ? "selected" : "" ?>>Admin</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Cập nhật</button>
        <a href="admin_dashboard.php?view=users" class="btn btn-secondary">Hủy</a>
    </form>
</div>