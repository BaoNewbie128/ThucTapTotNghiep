<?php
session_start();
require __DIR__ . "/../config/db.php";
date_default_timezone_set('Asia/Ho_Chi_Minh');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email không hợp lệ!";
    } else {
        $message = "Nếu email tồn tại, chúng tôi sẽ gửi mã OTP.";

        $stmt = $conn->prepare("DELETE FROM password_resets WHERE expires_at <= NOW()");
        $stmt->execute();

        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM password_resets WHERE request_ip = ? AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
        $stmt->bind_param("s", $ip);
        $stmt->execute();
        $ipRequests = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);

        if ($ipRequests >= 5) {
            $error = "Bạn gửi OTP quá nhiều lần. Vui lòng thử lại sau 15 phút.";
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $stmt = $conn->prepare("SELECT id FROM password_resets WHERE email = ? AND last_sent_at > DATE_SUB(NOW(), INTERVAL 60 SECOND)");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $recentOtp = $stmt->get_result();

                if ($recentOtp->num_rows > 0) {
                    $error = "Vui lòng chờ 60 giây trước khi gửi lại OTP.";
                } else {
                    $otp = rand(100000, 999999);
                    $otpHash = password_hash((string)$otp, PASSWORD_DEFAULT);
                    $expires = date("Y-m-d H:i:s", strtotime("+5 minutes"));

                    $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
                    $stmt->bind_param("s", $email);
                    $stmt->execute();

                    // Store only the hash so a database leak cannot reveal a valid OTP.
                    $stmt = $conn->prepare("INSERT INTO password_resets (email, otp, expires_at, attempts, last_sent_at, request_ip) VALUES (?, ?, ?, 0, NOW(), ?)");
                    $stmt->bind_param("ssss", $email, $otpHash, $expires, $ip);
                    $stmt->execute();

                    $mail = new PHPMailer(true);

                    try {
                        $mail->isSMTP();
                        $mail->Host = SMTP_HOST;
                        $mail->SMTPAuth = true;
                        $mail->CharSet = 'UTF-8';
                        $mail->Encoding = 'base64';

                        $smtpUser = SMTP_USERNAME;
                        $smtpPass = SMTP_PASSWORD;
                        $smtpFrom = SMTP_FROM;
                        $smtpFromName = SMTP_FROM_NAME;
                        if ($smtpUser === '' || $smtpPass === '') {
                            throw new Exception('Chưa cấu hình SMTP_USERNAME/SMTP_PASSWORD.');
                        }

                        $mail->Username = $smtpUser;
                        $mail->Password = $smtpPass;

                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = SMTP_PORT;

                        $mail->setFrom($smtpFrom, $smtpFromName);
                        $mail->addAddress($email);
                        $mail->addReplyTo($smtpFrom, $smtpFromName);

                        $mail->isHTML(true);
                        $mail->Subject = 'Ma OTP dat lai mat khau JDM World';
                        $mail->Body = "<p>Ma OTP cua ban la: <strong>$otp</strong></p><p>Ma nay het han sau 5 phut.</p>";
                        $mail->AltBody = "Ma OTP cua ban la: $otp (het han 5 phut)";

                        $mail->send();
                        $_SESSION['reset_email'] = $email;
                        header("Location: verify_otp.php");
                        exit;
                    } catch (Exception $e) {
                        error_log('Reset password email failed: ' . ($mail->ErrorInfo ?: $e->getMessage()));
                        $error = "Gửi email thất bại. Vui lòng kiểm tra SMTP Gmail/App Password trong cấu hình máy chủ.";
                    }
                }
            }
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
                <?php if (!empty($message)): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
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