<?php
    require_once __DIR__ . "/../includes/admin_auth_check.php";
    require __DIR__ . "/../config/db.php";
   if(!isset($_GET['id'])){
       die("Không tìm thấy sản phẩm .");
   }
   $id = intval($_GET['id']);
   $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
   $stmt->bind_param("i", $id);
   $stmt->execute();
   $product = $stmt->get_result()->fetch_assoc();
   if(!$product){ die("Sản phẩm không tồn tại."); }
   $success = "";
    $error_message = "";
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        verify_csrf();
        $brand = trim($_POST['brand'] ?? '');
        $model = trim($_POST['model'] ?? '');
        $scale = trim($_POST['scale'] ?? '');
        $color = trim($_POST['color'] ?? '');
        $price = filter_var($_POST['price'] ?? null, FILTER_VALIDATE_FLOAT);
        $stock = filter_var($_POST['stock'] ?? null, FILTER_VALIDATE_INT);
        $description = trim($_POST['description'] ?? '');
        if($brand === '' || $model === '' || $scale === '' || $color === '' || $description === '' || $price === false || $price < 0 || $stock === false || $stock < 0){
            $error_message = "Dữ liệu sản phẩm không hợp lệ.";
        } else {
        try {
        $image_name = $product['image'];
        $uploaded = upload_image_file($_FILES['image'] ?? [], __DIR__ . "/../images");
        if($uploaded !== ''){ $image_name = $uploaded; }
        $stmt = $conn->prepare("UPDATE products SET brand=?, model=?, scale=?, price=?, stock=?, color=?, description=?, image=? WHERE id=?");
        $stmt->bind_param("sssdisssi", $brand, $model, $scale, $price, $stock, $color, $description, $image_name, $id);
          if($stmt->execute()){
              $stmt->close();
              $_SESSION['message'] = "Cập nhật sản phẩm thành công!";
              $_SESSION['message_type'] = "success";
              header("Location: admin_dashboard.php?view=products");
              exit; 
          } else {
              $error_message = "Lỗi cập nhật sản phẩm.";
          }
        } catch (Throwable $e) { $error_message = $e->getMessage(); }
        }

    }
?>
<a href="admin_dashboard.php?view=products" class="btn btn-secondary mb-3">Quay lại</a>
<h2 style="color: blue; margin-bottom: 20px;">Chỉnh sửa sản phẩm</h2>
<?php if(!empty($success)): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>
<?php if(!empty($error_message)): ?>
<div class="alert alert-danger"><?= $error_message ?></div>
<?php endif; ?>
<form method="POST" enctype="multipart/form-data" style="max-width: 600px;">
    <?= csrf_field() ?>
    <div class="mb-3">
        <label class="form-label">Hãng</label><br />
        <input type="text" name="brand" class="form-control" value="<?= htmlspecialchars($product['brand']) ?>"
            required><br />
        <label class="form-label">Mẫu</label><br />
        <input type="text" name="model" class="form-control" value="<?= htmlspecialchars($product['model']) ?>"
            required><br />
        <label class="form-label">Tỉ lệ</label><br />
        <input type="text" name="scale" class="form-control" value="<?= htmlspecialchars($product['scale']) ?>"
            required><br />
        <label class="form-label">Màu</label><br />
        <input type="text" name="color" class="form-control" value="<?= htmlspecialchars($product['color']) ?>"
            required><br />
        <label class="form-label">Giá</label><br />
        <input type="number" name="price" step="0.01" class="form-control"
            value="<?= htmlspecialchars($product['price']) ?>" required><br />
        <label class="form-label">Hình ảnh</label> <br />
        <?php if($product["image"]): ?>
        <img src="../images/<?= htmlspecialchars($product['image']) ?>" alt="Hình ảnh sản phẩm"
            style="width: 100px;"><br />
        <?php else : ?>
        <p>Không có hình ảnh</p>
        <?php endif; ?>
        <label class="form-label">Thay đổi hình ảnh</label><br />
        <input type="file" name="image" class="form-control"><br />
        <label class="form-label">Số lượng</label><br />
        <input type="number" name="stock" class="form-control" value="<?= htmlspecialchars($product['stock']) ?>"
            required><br />
        <label class="form-label">Mô tả</label><br />
        <textarea name="description" class="form-control" rows="5"
            required><?= htmlspecialchars($product['description']) ?></textarea><br />
        <button type="submit" class="btn btn-primary mt-3">Cập nhật</button>
    </div>
</form>