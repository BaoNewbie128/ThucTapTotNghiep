<?php
session_start();
include __DIR__ ."/../includes/auth_check.php";
require_once __DIR__ . "/../config/db.php";
if(!isset($_GET['back_url'])){
    $_SESSION['back_url'] =$_SERVER['HTTP_REFERER'] ?? 'dashboard.php';
}
if(isset($_GET['back_url'])){
    $_SESSION['back_url'] = $_GET['back_url'];
}
    if(!isset($_GET['product_id'])){
        die("<div class='alert alert-danger text-center'>Thiếu product_id!</div>");
    }
    $product_id = intval($_GET['product_id']);
    $user_id = $_SESSION['user_id'];
    $message = "";
    $message = "";

if(isset($_GET['msg'])){
    if($_GET['msg'] == 'deleted'){
        $message = "<div class='alert alert-success text-center'>Đánh giá đã được xóa!</div>";
    }
    if($_GET['msg'] == 'updated'){
        $message = "<div class='alert alert-success text-center'>Đánh giá đã được cập nhật!</div>";
    }
    if($_GET['msg'] == 'error'){
        $message = "<div class='alert alert-danger text-center'>Có lỗi xảy ra!</div>";
    }
}
    $sql = "SELECT brand, model,image FROM products WHERE id = $product_id";
    $prod = $conn->query($sql)->fetch_assoc();
    if(!$prod){
        die("<div class='alert alert-danger text-center'>Sản phẩm không tồn tại!</div>");
    }
    // delete
    if(isset($_GET['delete_review'])){
        $review_id = intval($_GET['delete_review']);
        $check = $conn->query("SELECT * FROM reviews WHERE id = $review_id AND user_id = $user_id");
        if($check->num_rows ===1){
            $conn->query("DELETE FROM reviews WHERE id = $review_id");
            header("Location: reviews.php?product_id=$product_id&msg=deleted");
            exit;
        }else{
            header("Location: reviews.php?product_id=$product_id&msg=error");
            exit;
        }
    }
    // edit
    if(isset($_POST['edit_review_id'])){
        $review_id = intval($_POST['edit_review_id']);
        $new_comment = $conn->real_escape_string($_POST['edit_comment']);
        $new_rating = intval($_POST['edit_rating'] ?? 10);
        if($new_rating < 1 || $new_rating > 10){
          $new_rating = 10;
        }
        $check = $conn->query("SELECT * FROM reviews WHERE id = $review_id AND user_id = $user_id");
        if($check->num_rows ===1){
            $conn->query("UPDATE reviews SET comment='$new_comment', rating=$new_rating WHERE id = $review_id");
            header("Location: reviews.php?product_id=$product_id&msg=updated");
            exit;
            
        }else{
            header("Location: reviews.php?product_id=$product_id&msg=error");
            exit;
            
        } 
    }

    // create
    if($_SERVER["REQUEST_METHOD"] === "POST"  && !isset($_POST['edit_review_id'])){
        $comment = $conn->real_escape_string($_POST['comment']);
        $rating = intval($_POST['rating'] ?? 10);
        if($rating < 1 || $rating > 10){
          $rating = 10;
        }
        $sql_insert = "INSERT INTO reviews (user_id, product_id, rating, comment, created_at) VALUES ($user_id, $product_id, $rating, '$comment', NOW())";
        if($conn->query($sql_insert) === TRUE){
            $message = "<div class='alert alert-success text-center'>Đánh giá đã được gửi !</div>";
        } else {
            $message = "<div class='alert alert-danger text-center'>Lỗi khi gửi đánh giá: " . $conn->error . "</div>";
        }
    }
    // read
    $sql_reviews = "SELECT r.id,r.user_id,r.rating, r.comment, r.created_at, u.username 
                    FROM reviews r 
                    JOIN users u ON r.user_id = u.id 
                    WHERE r.product_id = $product_id 
                    ORDER BY r.created_at DESC";
    $reviews = $conn->query($sql_reviews);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đánh giá sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <div class="modal fade" id="reviewModal" tabindex="1">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        Đánh giá sản phẩm
                        <?= htmlspecialchars($prod['brand'] . ' ' . $prod['model']) ?>
                        <img src="../images/<?= htmlspecialchars($prod['image']) ?>" alt="car"
                            style="width: 100px; height: 70px; object-fit: cover; border-radius: 6px;" class="me-3">
                    </h5>
                    <a href="<?= $_SESSION['back_url']  ??'dashboard.php' ?> " class="btn-close"></a>
                </div>
                <div class="modal-body">
                    <?= $message ?>
                    <h5 class="fw-bold mb-3">Viết đánh giá của bạn</h5>
                    <form method="POST" class="mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <!-- Ảnh xe -->

                            <div>
                                <select name="rating" class="form-select mb-2" style="width: 150px;">
                                    <option value="10">10</option>
                                    <option value="9">9</option>
                                    <option value="8">8</option>
                                    <option value="7">7</option>
                                    <option value="6">6</option>
                                    <option value="5">5</option>
                                    <option value="4">4</option>
                                    <option value="3">3</option>
                                    <option value="2">2</option>
                                    <option value="1">1</option>
                                </select>
                            </div>
                        </div>
                        <textarea name="comment" class="form-control mb-3" rows="4"
                            placeholder="Viết đánh giá của bạn ở đây..." required></textarea>
                        <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
                    </form>
                    <hr>
                    <h5 class="fw-bold mb-3">Tất cả đánh giá</h5>
                    <?php if($reviews->num_rows >0) :?>
                    <?php while ($r = $reviews->fetch_assoc()) : ?>
                    <div class="border round p-3 mb-3 bg-light">
                        <strong class="text-primary"><?= htmlspecialchars($r['username'])  ?></strong>

                        <p> <?= nl2br(htmlspecialchars($r['comment'])) ?>
                        </p>
                        <p class="mb-1">
                            <?php for ($i = 1; $i <= $r['rating']; $i++): ?> <?php endfor; ?>
                            <span class="text-muted">Đánh giá : <?= $r['rating'] ?> /10</span>
                        </p>
                        <small class="text-muted"> Đã đăng <?= $r['created_at'] ?></small>
                        <?php if($r['user_id'] == $user_id): ?>
                        <div class="mt-2">
                            <button class="btn btn-sm btn-warning"
                                onclick="openEditModal(<?= $r['id'] ?>, <?= $r['rating'] ?>, `<?= htmlspecialchars($r['comment'], ENT_QUOTES) ?>`)">
                                Sửa
                            </button>

                            <a href="?product_id=<?= $product_id ?>&delete_review=<?= $r['id'] ?>"
                                class="btn btn-sm btn-danger"
                                onclick="return confirm('Bạn chắc chắn muốn xóa đánh giá này?')">
                                Xóa
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <p class="text-muted">Chưa có đánh giá nào.</p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <a href="<?= $_SESSION['back_url'] ??'dashboard.php' ?>" class="btn btn-secondary">Đóng</a>
                </div>
            </div>
        </div>

    </div>
    <div class="modal fade" id="editReviewModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Sửa đánh giá</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="edit_review_id" id="edit_review_id">

                        <label class="form-label fw-bold">Điểm</label>
                        <select name="edit_rating" id="edit_rating" class="form-select mb-2">
                            <?php for($i=10; $i>=1; $i--): ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                            <?php endfor; ?>
                        </select>

                        <label class="form-label fw-bold">Nội dung</label>
                        <textarea name="edit_comment" id="edit_comment" class="form-control" rows="4"></textarea>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
    function openEditModal(id, rating, comment) {
        document.getElementById('edit_review_id').value = id;
        document.getElementById('edit_rating').value = rating;
        document.getElementById('edit_comment').value = comment;

        var modal = new bootstrap.Modal(document.getElementById('editReviewModal'));
        modal.show();
    }
    var modal = new bootstrap.Modal(document.getElementById('reviewModal'));
    modal.show();
    </script>
</body>

</html>