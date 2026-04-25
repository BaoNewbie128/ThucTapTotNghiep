<?php
require_once __DIR__ . '/../includes/security.php';

$id = intval($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ? AND status = 'published'");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
if (!$post) {
    http_response_code(404);
    echo "<div class='alert alert-warning'>Bài viết không tồn tại hoặc đã bị ẩn.</div>";
    return;
}
?>

<h2><?= htmlspecialchars($post['title']) ?></h2>
<p class="text-muted"><?= $post['created_at'] ?></p>

<img src="../images/<?= htmlspecialchars($post['thumbnail']) ?>" class="img-fluid mb-3">

<div class="post-content">
    <?= clean_html_content($post['content']) ?>
</div>