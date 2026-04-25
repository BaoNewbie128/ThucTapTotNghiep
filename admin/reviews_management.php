<?php
require_once __DIR__ . '/../includes/admin_auth_check.php';

include __DIR__ ."/../includes/auth_check.php";
require_once __DIR__ . "/../config/db.php";
   if($_SESSION["role"] !== 'admin'){
       die("<div class='alert alert-danger text-center'>Bạn không có quyền truy cập trang này!</div>");
   }
    $message = "";
     $limit = 9;
$page = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
$offset = ($page -1) * $limit;
$count_sql = "SELECT COUNT(*) as total FROM (
    SELECT r.id 
    FROM reviews r JOIN users u ON r.user_id = u.id
    JOIN products p ON r.product_id = p.id 
    GROUP BY r.id) AS temp";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_products = $count_result->fetch_assoc()['total'] ?? 0;
    $total_pages = ceil($total_products / $limit);
    // read
    $sql = "SELECT r.id AS review_id,r.rating, r.comment, r.created_at, u.username,p.brand,p.model,p.image 
                    FROM reviews r 
                    JOIN users u ON r.user_id = u.id 
                    JOIN products p ON r.product_id = p.id
                    ORDER BY r.created_at DESC
                    LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $reviews = $stmt->get_result();

      // edit
    if(isset($_POST['edit_review_id'])){
        verify_csrf();
        $review_id = intval($_POST['edit_review_id']);
        $comment = trim($_POST['edit_comment'] ?? '');
        $rating = intval($_POST['edit_rating'] ?? 10);
        if($rating < 1 || $rating > 10){
          $rating = 10;
        }
        $stmt = $conn->prepare("UPDATE reviews SET comment = ?, rating = ? WHERE id = ?");
        $stmt->bind_param("sii", $comment, $rating, $review_id);
        if($stmt->execute()){
            $message = "<div class='alert alert-success text-center'>Đánh giá đã được cập nhật !</div>";
             header("Location: ?view=reviews&page=$page");
             exit();
        }else{
            $message = "<div class='alert alert-danger text-center'> Lỗi cập nhật ! </div> ";
        }
    }
      // delete
    if(isset($_POST['delete_review'])){
        verify_csrf();
        $review_id = intval($_POST['delete_review']);
        $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->bind_param("i", $review_id);
           if($stmt->execute()){
            $message = "<div class='alert alert-success text-center'>Đánh giá đã được xóa !</div>";
            header("Location: ?view=reviews&page=$page");
        exit();
        }else{
            $message = "<div class='alert alert-danger text-center'>Không thể xóa đánh giá này !</div>";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đánh giá sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</head>

<div class="bg-light">
    <div class="app-container">
        <h2 class="page-title text-primary">Quản lý đánh giá</h2>
        <?= $message ?>

        <?php if ($reviews->num_rows > 0): ?>
        <?php while ($r = $reviews->fetch_assoc()): ?>
        <div class="card p-3 mb-3 shadow-sm">
            <div class="row g-3 align-items-start">
                <div class="col-12 col-md-3">
                    <img src="../images/<?= $r['image'] ?>" class="img-fluid rounded w-100 mb-2 mb-md-0"
                        style="height:160px; object-fit:cover;"
                        alt="<?= htmlspecialchars($r['brand'].' '.$r['model']) ?>">
                </div>
                <div class="col-12 col-md-9">
                    <strong
                        class="text-primary fs-5 d-block mb-2"><?= htmlspecialchars($r['brand'] . ' ' . $r['model']) ?></strong>
                    <p class="mb-1"><strong>Người đánh giá:</strong> <?= htmlspecialchars($r['username']) ?></p>
                    <p class="mb-1"><strong>Điểm:</strong> <?= $r['rating'] ?>/10</p>
                    <p class="mb-1"><?= nl2br(htmlspecialchars($r['comment'])) ?></p>
                    <small class="text-muted d-block mb-2">Ngày: <?= $r['created_at'] ?></small>
                    <div class="mt-2 d-flex gap-2">
                        <button class="btn btn-warning btn-sm"
                            onclick="openEditModal(<?= $r['review_id'] ?>, <?= $r['rating'] ?>, `<?= htmlspecialchars($r['comment'], ENT_QUOTES) ?>`)">
                            Sửa</button>
                        <form method="POST" action="?view=reviews&page=<?= $page ?>" class="d-inline"
                            onsubmit="return confirm('Xóa đánh giá này?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="delete_review" value="<?= $r['review_id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Xóa</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
        <?php if ($total_pages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">

                <!-- Nút trang trước -->
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?view=reviews&page=<?= $page-1 ?>">
                        &laquo;
                    </a>
                </li>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="?view=reviews&page=<?= $i ?>">
                        <?= $i ?>
                    </a>
                </li>
                <?php endfor; ?>

                <!-- Nút trang sau -->
                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?view=reviews&page=<?= $page+1 ?>">
                        &raquo;
                    </a>
                </li>

            </ul>
        </nav>
        <?php endif; ?>
        <?php else: ?>
        <div class="alert alert-info text-center">Chưa có đánh giá nào.</div>
        <?php endif; ?>
    </div>


    <!-- EDIT MODAL -->
    <div class="modal fade" id="editReviewModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <form method="POST">
                    <?= csrf_field() ?>

                    <div class="modal-header">
                        <h5 class="modal-title">Sửa đánh giá</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <input type="hidden" name="edit_review_id" id="edit_review_id">

                        <label class="form-label fw-bold">Điểm (1–10)</label>
                        <select name="edit_rating" id="edit_rating" class="form-select mb-3">
                            <?php for ($i = 10; $i >= 1; $i--): ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                            <?php endfor; ?>
                        </select>

                        <label class="form-label fw-bold">Nội dung đánh giá</label>
                        <textarea name="edit_comment" id="edit_comment" class="form-control" rows="4"
                            required></textarea>

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-primary">Lưu</button>
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
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
    </script>

    </body>

</html>