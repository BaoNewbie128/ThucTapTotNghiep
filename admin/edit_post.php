<?php
require_once __DIR__ . '/../includes/admin_auth_check.php';
$id = intval($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
if (!$post) {
    die("Bài viết không tồn tại.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $title = trim($_POST['title'] ?? '');
    $content = clean_html_content(html_entity_decode((string)($_POST['content'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    $status = $_POST['status'] ?? 'draft';
    if ($title === '' || !in_array($status, ['published', 'draft'], true)) {
        die("Dữ liệu bài viết không hợp lệ.");
    }

    $thumbnail = $post['thumbnail'];

    try {
        if (!empty($_FILES['thumbnail']['name'])) {
            $thumbnail = upload_image_file($_FILES['thumbnail'], __DIR__ . '/../images');
        }
    } catch (Throwable $e) {
        die($e->getMessage());
    }

    $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ?, thumbnail = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $title, $content, $thumbnail, $status, $id);
    $stmt->execute();

    header("Location: admin_dashboard.php?view=posts");
    exit;
}

$editorContent = html_entity_decode((string)$post['content'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
?>

<h3>Sửa bài viết</h3>

<form method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>" class="form-control mb-2">

    <textarea name="content" id="editor" class="form-control mb-2"
        rows="6"><?= htmlspecialchars($editorContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>

    <?php if (!empty($post['thumbnail'])): ?>
    <img src="../images/<?= htmlspecialchars($post['thumbnail']) ?>" width="120" class="mb-2">
    <?php endif; ?>

    <input type="file" name="thumbnail" class="form-control mb-2">

    <select name="status" class="form-control mb-2">
        <option value="published" <?= $post['status']=='published'?'selected':'' ?>>Hiển thị</option>
        <option value="draft" <?= $post['status']=='draft'?'selected':'' ?>>Ẩn</option>
    </select>

    <button class="btn btn-primary">Cập nhật</button>
</form>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
ClassicEditor.create(document.querySelector('#editor'), {
    ckfinder: {
        uploadUrl: 'upload_image.php'
    }
}).catch(error => {
    console.error(error);
});
</script>