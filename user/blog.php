<?php
$sql = "SELECT * FROM posts WHERE status='published' ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<h2 class="fw-bold mb-3">Tin tức & Blog JDM</h2>

<div class="row">
    <?php while($row = $result->fetch_assoc()): ?>
    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm">
            <img src="../images/<?= htmlspecialchars($row['thumbnail']) ?>" class="card-img-top">

            <div class="card-body">
                <h5><?= htmlspecialchars($row['title']) ?></h5>

                <a href="index.php?view=post&id=<?= $row['id'] ?>" class="btn btn-primary btn-sm mt-2">
                    Xem chi tiết
                </a>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>