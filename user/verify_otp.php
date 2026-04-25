<?php
session_start();
require __DIR__ . "/../config/db.php";

$email = $_SESSION['reset_email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $otp = preg_replace('/\D/', '', $_POST['otp'] ?? '');

    $stmt = $conn->prepare("
        SELECT * FROM password_resets 
        WHERE email = ? AND expires_at > NOW()
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0 && ($row = $result->fetch_assoc()) && (int)$row['attempts'] < 5 && password_verify($otp, $row['otp'])) {
        $delete = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
        $delete->bind_param("s", $email);
        $delete->execute();
        $_SESSION['verified_otp'] = true;
        header("Location: reset_password.php");    
        exit;
    } else {
        $update = $conn->prepare("UPDATE password_resets SET attempts = attempts + 1 WHERE email = ?");
        $update->bind_param("s", $email);
        $update->execute();

        $delete = $conn->prepare("DELETE FROM password_resets WHERE email = ? AND attempts >= 5");
        $delete->bind_param("s", $email);
        $delete->execute();
        $error = "OTP sai, hết hạn hoặc đã vượt quá số lần thử!";
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