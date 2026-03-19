<?php
    require __DIR__ . "/../config/db.php";
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
      
        $sql = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i",$user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $password = $user['password'];
        if(!empty($new_password)){
            if(strlen($new_password)< 6){
                die("Mật khẩu phải tối thiểu 6 ký tự");
            }
            $final_password = password_hash($new_password,PASSWORD_DEFAULT);
        }else {
            $final_password = $password;
        }
        $update = $conn->prepare("
        UPDATE users 
        SET username = ?,email = ?,phone = ?,address = ?,password = ?
        WHERE id = ?");
        $update->bind_param(
            "sssssi",
            $username,
            $email,
            $phone,
            $address,
            $final_password,
            $user_id
        );
        if($update->execute()){
            $success = "Cập nhật sản phẩm thành công!";
        header("Location: dashboard.php?view=profile");
            exit;
        }else{
            $error_message = "Lỗi cập nhật" . $conn->error;
        }
    }
?>
<a href="dashboard.php?view=profile" class="btn btn-secondary">Quay lại</a>
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
        <label class="form-label">Địa chỉ</label>
        <textarea name="address" class="form-control"><?= htmlspecialchars($address) ?></textarea>
        <button type="submit" class="btn btn-primary mt-3">Cập nhật</button>
    </div>
</form>