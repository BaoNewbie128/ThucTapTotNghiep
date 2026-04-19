<?php
session_start();
require __DIR__. "/../config/db.php";
require_once __DIR__ . "/../validation.php";
$email = $_SESSION['reset_email'] ?? '';
$verified = $_SESSION['verified_otp'] ?? false;
if(!$email || !$verified){
    die("Truy cập không hợp lệ!");
}
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $password = $_POST['password'];

    if(!\Validator::min($password,6)){
        $error = "Mật khẩu >= 6 ký tự";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hash, $email);

        if ($stmt->execute()) {
            unset($_SESSION['reset_email']);
            unset($_SESSION['verified_otp']);
            header("Location: ../login.php?reset=success");
            exit;
        } else {
            $error = "Đổi mật khẩu thất bại!";
        }
    }
}
?>
<?php include "../includes/header.php"; ?>

<div class="hero-section">
    <div class="hero-content">
        <div class="card auth-card">
            <div class="card-body">
                <h2 class="hero-title">Đặt lại mật khẩu</h2>
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label" for="password">Mật khẩu mới</label>
                        <input class="form-control" id="password" type="password" name="password"
                            placeholder="Mật khẩu mới" required>
                    </div>
                    <button class="btn btn-primary auth-submit" type="submit">Đổi mật khẩu</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include "../includes/footer.php"; ?>