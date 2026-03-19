<?php
    require __DIR__ . "/../config/db.php";
   if(!isset($_GET['id'])){
       die("Không tìm thấy sản phẩm .");
   }
   $id = intval($_GET['id']);
   $product = $conn->query("SELECT * FROM products WHERE id = $id")->fetch_assoc();
   $success = "";
    $error_message = "";
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $brand = $conn->real_escape_string($_POST['brand']);
        $model = $conn->real_escape_string($_POST['model']);
        $scale = $conn->real_escape_string($_POST['scale']);
        $color = $conn->real_escape_string($_POST['color']);
        $price = floatval($_POST['price']);
        $stock = $conn->real_escape_string($_POST['stock']);
        $description = $conn->real_escape_string($_POST['description']);
        $image_name = $product['image'];
        if(!empty($_FILES['image']['name'])){
          $image_name = time() . "_" . basename($_FILES['image']['name']);
          move_uploaded_file($_FILES['image']["tmp_name"], __DIR__ . "/../images/" . $image_name);
        } 
        $sql = "UPDATE products SET brand='$brand', model='$model', scale='$scale', price=$price, stock='$stock', color='$color', description='$description', image='$image_name' WHERE id=$id";
          if($conn->query($sql) === TRUE){
              $success = "Cập nhật sản phẩm thành công!";
              $product = $conn->query("SELECT * FROM products WHERE id = $id")->fetch_assoc();
          } else {
              $error_message = "Lỗi: " . $sql . "<br>" . $conn->error;
          }
              header("Location: admin_dashboard.php?view=products");
    exit; 
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