<?php
session_start();
require __DIR__ . "/../config/db.php";

$email = $_SESSION['reset_email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $otp = preg_replace('/\D/', '', $_POST['otp']); // chỉ lấy số

    $stmt = $conn->prepare("
        SELECT * FROM password_resets 
        WHERE email = ? AND otp = ? AND expires_at > NOW()
    ");
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $conn->query("DELETE FROM password_resets WHERE email = '$email'");
        $_SESSION['verified_otp'] = true;
        header("Location: reset_password.php");    
        exit;
    } else {
        $error = "OTP sai hoặc hết hạn!";
    }
}
?>
<?php include "../includes/header.php"; ?>

<div class="hero-section">
    <div class="hero-content">
        <div class="card auth-card">
            <div class="card-body">
                <h2 class="hero-title">Xác nhận OTP</h2>
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label" for="otp">Mã OTP</label>
                        <input class="form-control" id="otp" type="text" name="otp" placeholder="Nhập OTP" required>
                    </div>
                    <button class="btn btn-primary auth-submit" type="submit">Xác nhận</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include "../includes/footer.php"; ?>