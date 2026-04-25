<?php
    require __DIR__ . "/../config/db.php";
    require_once __DIR__ . "/../validation.php";
    if(!isset($_SESSION["user_id"])){
        header("Location: ../login.php");
        exit;
    }
    $user_id = $_SESSION['user_id'];
    $success ="";
    $error_message = "";
    $sql2 = "SELECT username, email, phone, address FROM users WHERE id = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("i", $user_id);
    $stmt2->execute();
    $user = $stmt2->get_result()->fetch_assoc();
    $username = $user["username"];
    $email = $user["email"];
    $phone = $user["phone"];
    $address = $user["address"];
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $new_password = trim($_POST['new_password'] ?? '');

    // VALIDATE
    if(!\Validator::required($username)){
        $error_message = "Tên không được để trống";
    } elseif(!\Validator::email($email)){
        $error_message = "Email không hợp lệ";
    } elseif(!\Validator::isNumber($phone)){
        $error_message = "SĐT phải là số";
    } else {

        // Lấy password cũ
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i",$user_id);
        $stmt->execute();
        $password = $stmt->get_result()->fetch_assoc()['password'];

        // Nếu nhập password mới
        if(!empty($new_password)){
            if(!\Validator::minLength($new_password,6)){
                $error_message = "Mật khẩu tối thiểu 6 ký tự";
            } else {
                $final_password = password_hash($new_password,PASSWORD_DEFAULT);
            }
        } else {
            $final_password = $password;
        }

        if(empty($error_message)){
            $update = $conn->prepare("
                UPDATE users 
                SET username = ?, email = ?, phone = ?, address = ?, password = ? 
                WHERE id = ?
            ");
            $update->bind_param("sssssi",
                $username,
                $email,
                $phone,
                $address,
                $final_password,
                $user_id
            );

            if($update->execute()){
                    $_SESSION["username"] = $username;
                    $_SESSION["email"] = $email;
                header("Location: /index.php?view=profile");
                exit;
            } else {
                $error_message = "Lỗi cập nhật: " . $conn->error;
            }
        }
    }
}
?>
<a href="/index.php" class="btn btn-secondary">Quay lại</a>
<h2 style="color: blue; margin-bottom: 20px;">Chỉnh sửa thông tin người dùng</h2>
<?php if(!empty($success)) :?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif;?>
<?php if(!empty($error_message)) :?>
<div class="alert alert-danger"><?= $error_message ?></div>
<?php endif;?>
<form method="POST" enctype="multipart/form-data" style="max-width: 600px;">
    <div class="mb-3">
        <label class="form-label">Tên người dùng</label>
        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($username) ?>" required>
        <br>
        <label class="form-label">email</label>
        <input type="text" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required> <br>
        <label class="form-label">Mật khẩu</label>
        <input type="password" name="new_password" class="form-control"> <br>
        <label class="form-label">Số điện thoại</label>
        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($phone) ?>" required> <br>
        <br>
        <label class="form-label">Địa chỉ chi tiết</label>
        <textarea name="address" class="form-control"
            placeholder="Số nhà, đường, khu vực..."><?= htmlspecialchars($address) ?></textarea>
        <button type="submit" class="btn btn-primary mt-3">Cập nhật</button>
    </div>
</form>