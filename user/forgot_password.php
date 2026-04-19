<?php
session_start();
require __DIR__ . "/../config/db.php";
date_default_timezone_set('Asia/Ho_Chi_Minh');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);

    // 1. Kiểm tra email tồn tại
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $error = "Email không tồn tại!";
    } else {

        // 2. Tạo OTP
        $otp = rand(100000, 999999);
        $expires = date("Y-m-d H:i:s", strtotime("+5 minutes"));

        // 3. Xóa OTP cũ
        $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        // 4. Lưu OTP mới
        $stmt = $conn->prepare("INSERT INTO password_resets (email, otp, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $otp, $expires);
        $stmt->execute();

        // 5. Gửi mail
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;

            $mail->Username = 'binkongu24@gmail.com';
            $mail->Password = 'yiyo etmj almh yutr';

            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('binkongu24@gmail.com', 'JDM WORLD');
            $mail->addAddress($email);

            $mail->Subject = 'OTP Reset Password';
            $mail->Body = "Mã OTP của bạn là: $otp (hết hạn 5 phút)";

            $mail->send();
            $_SESSION['reset_email'] = $email;
            header("Location: verify_otp.php");
            exit;

        } catch (Exception $e) {
            $error = "Gửi email thất bại: " . $mail->ErrorInfo;
        }
    }
}
?>
<?php include "../includes/header.php"; ?>
<div class="hero-section">
    <div class="hero-content">
        <div class="card auth-card">
            <div class="card-body">
                <h2 class="hero-title">Quên mật khẩu</h2>
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label" for="email">Email đăng ký</label>
                        <input class="form-control" id="email" type="email" name="email" placeholder="Nhập email"
                            required>
                    </div>
                    <button class="btn btn-primary auth-submit" type="submit">Gửi OTP</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include "../includes/footer.php"; ?>