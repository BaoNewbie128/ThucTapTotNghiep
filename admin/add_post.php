<?php
require_once __DIR__ . '/../includes/admin_auth_check.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $title = trim($_POST['title'] ?? '');
    $content = clean_html_content((string)($_POST['content'] ?? ''));
    $status = $_POST['status'] ?? 'draft';
    if ($title === '' || !in_array($status, ['published', 'draft'], true)) {
        die("Dữ liệu bài viết không hợp lệ.");
    }

    // upload ảnh
    $thumbnail = '';
    try {
        if (!empty($_FILES['thumbnail']['name'])) {
            $thumbnail = upload_image_file($_FILES['thumbnail'], __DIR__ . '/../images');
        }
    } catch (Throwable $e) {
        die($e->getMessage());
    }

    $stmt = $conn->prepare("INSERT INTO posts (title, content, thumbnail, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $title, $content, $thumbnail, $status);
    $stmt->execute();

    header("Location: admin_dashboard.php?view=posts");
    exit;
}
?>