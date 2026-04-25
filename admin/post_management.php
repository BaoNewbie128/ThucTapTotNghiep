<?php
require_once __DIR__ . '/../includes/admin_auth_check.php';
$sql = "SELECT * FROM posts ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<div class="d-flex justify-content-between mb-3">
    <h3>Quản lý bài viết</h3>
    <a href="admin_dashboard.php?view=add-post" class="btn btn-success">Thêm bài viết</a>
</div>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Tiêu đề</th>
            <th>Ảnh</th>
            <th>Trạng thái</th>
            <th>Ngày tạo</th>
            <th>Hành động</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><img src="../images/<?= $row['thumbnail'] ?>" width="80"></td>
            <td>
                <?php if($row['status'] == 'published'): ?>
                <span class="badge bg-success">Hiển thị</span>
                <?php else: ?>
                <span class="badge bg-secondary">Ẩn</span>
                <?php endif; ?>
            </td>
            <td><?= $row['created_at'] ?></td>
            <td>
                <a href="admin_dashboard.php?view=edit-post&id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Sửa</a>
                <form method="POST" action="admin_dashboard.php?view=delete-post" class="d-inline"
                    onsubmit="return confirm('Xóa bài này?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Xóa</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>