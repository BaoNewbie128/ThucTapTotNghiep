<?php
    require_once __DIR__ . "/../includes/admin_auth_check.php";
    include __DIR__ . "/../config/db.php";
    $success = "";
    $error = "";
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
                $image_name = upload_image_file($_FILES['image'] ?? [], __DIR__ . "/../images");
                $stmt = $conn->prepare("INSERT INTO products (brand, model, scale, price, stock, color, description, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssdisss", $brand, $model, $scale, $price, $stock, $color, $description, $image_name);
                $stmt->execute();
                $success = "Thêm sản phẩm thành công!";
            } catch (Throwable $e) {
                $error_message = $e->getMessage();
            }
        }
        if(empty($error_message)){
    header("Location: admin_dashboard.php?view=products");
    exit; 
        }
    }
    
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>

<body class="bg-light">
    <div class="app-container">
        <div class="mb-3">
            <a href="admin_dashboard.php?view=products" class="btn btn-secondary btn-sm">← Quay lại</a>
        </div>
        <h2 class="page-title mb-4">Thêm sản phẩm mới</h2>
        <?php if(!empty($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if(!empty($error_message)): ?>
        <div class="alert alert-danger"><?= $error_message ?></div>
        <?php endif; ?>

        <div class="card p-4 shadow-sm" style="max-width: 700px;">
            <form method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label fw-600">Hãng</label>
                    <input type="text" name="brand" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600">Mẫu</label>
                    <input type="text" name="model" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600">Tỉ lệ</label>
                    <input type="text" name="scale" class="form-control" required>
                </div>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-600">Giá</label>
                            <input type="number" name="price" step="0.01" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-600">Số lượng</label>
                            <input type="number" name="stock" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600">Màu</label>
                    <input type="text" name="color" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600">Hình ảnh</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600">Mô tả</label>
                    <textarea name="description" class="form-control" rows="5" required></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">Thêm sản phẩm</button>
                    <a href="admin_dashboard.php?view=products" class="btn btn-secondary flex-grow-1">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>