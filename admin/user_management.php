<?php
    require __DIR__ . "/../config/db.php";
    $users = [];
    $unique_name = [];
    $error_message = "";
    function truncate_description($text,$limit=100) {
        if(strlen($text)> $limit){
            $text = substr($text,0,$limit);
            $text =  substr($text,0,strpos($text, ' '));
            return $text . '...';
        }
        return $text;
    }
    $search_query = isset($_GET['search']) ? trim($_GET['search']) : "";
    $filter_role = isset($_GET['role']) ? trim($_GET['role']) : "";
    $sql = "SELECT id,username,email,phone,address,role,created_at FROM users WHERE 1";
    $params = [];
    $types = "";
    if(($search_query)){
        $sql .= " AND (username LIKE ? or email LIKE ? or phone LIKE ?)";
        $search_like = "%" . $search_query . "%";
        $params[] = $search_like;
        $params[] = $search_like;
        $params[] = $search_like;
        $types .= "sss";
    }
    if($filter_role && in_array($filter_role,['customer','admin'])){
        $sql .= " AND role = ?";
        $params[] = $filter_role;
        $types .="s";
    }
    $sql .=" ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    if(!$stmt){
        die("Lỗi chuẩn bị câu lệnh SQL : " . $conn->error);
    }
    if($params){
        $stmt->bind_param($types,...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()){
        $users[] = $row;
    }
    $stmt->close();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý người dùng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>

<body class="bg-light">
    <div class="app-container">
        <h2 class="page-title mb-4">Quản lý người dùng</h2>

        <!-- Filter Form -->
        <div class="card p-3 mb-4 shadow-sm">
            <form method="GET" class="row g-2 align-items-end">
                <input type="hidden" name="view" value="users">
                <div class="col-12 col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Tìm username, email, phone..."
                        value="<?= htmlspecialchars($search_query) ?>">
                </div>
                <div class="col-12 col-md-3">
                    <select name="role" class="form-select">
                        <option value="">Tất cả role</option>
                        <option value="customer" <?= $filter_role === "customer" ? "selected" : "" ?>>Customer</option>
                        <option value="admin" <?= $filter_role === "admin" ? "selected" : "" ?>>Admin</option>
                    </select>
                </div>
                <div class="col-12 col-md-3">

                    <button type="submit" class="btn btn-primary w-50">Lọc</button>
                    <a href="admin_dashboard.php?view=add-user" class="btn btn-success w-20">Thêm</a>
                </div>
            </form>
        </div>

        <!-- Desktop Table View -->
        <div class="d-none d-md-block">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Tên người dùng</th>
                        <th>Email</th>
                        <th>Số điện thoại</th>
                        <th>Địa chỉ</th>
                        <th>Vai trò</th>
                        <th>Ngày Tạo</th>
                        <th colspan="2">Tác vụ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)) : ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">Không tìm thấy người dùng nào</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['phone']) ?></td>
                        <td><?= htmlspecialchars(truncate_description($u['address'], 50)) ?></td>
                        <td><span
                                class="badge <?= $u['role'] === 'admin' ? 'bg-danger' : 'bg-success' ?>"><?= $u['role'] ?></span>
                        </td>
                        <td><?= $u['created_at'] ?></td>
                        <td><a href="admin_dashboard.php?view=edit-user&user_id=<?= $u['id'] ?>"
                                class="btn btn-warning btn-sm">Sửa</a></td>
                        <?php if($u['role'] === 'admin'):?>
                        <td></td>
                        <?php else:?>
                        <td><a href="admin_dashboard.php?view=delete-user&user_id=<?= $u['id'] ?>"
                                class="btn btn-danger btn-sm">Xóa</a></td>
                        <?php endif ;?>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="d-md-none">
            <?php if (empty($users)) : ?>
            <div class="alert alert-info text-center">Không tìm thấy người dùng nào</div>
            <?php else: ?>
            <div class="row g-3">
                <?php foreach ($users as $u): ?>
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="card-title mb-0"><?= htmlspecialchars($u['username']) ?></h6>
                                <span
                                    class="badge <?= $u['role'] === 'admin' ? 'bg-danger' : 'bg-success' ?>"><?= $u['role'] ?></span>
                            </div>
                            <p class="mb-2 small">
                                <strong>ID:</strong> <?= $u['id'] ?><br>
                                <strong>Email:</strong> <?= htmlspecialchars($u['email']) ?><br>
                                <strong>Số điện thoại:</strong> <?= htmlspecialchars($u['phone']) ?><br>
                                <strong>Địa chỉ:</strong>
                                <?= htmlspecialchars(truncate_description($u['address'], 40)) ?><br>
                                <strong>Ngày tạo:</strong> <?= $u['created_at'] ?>
                            </p>
                            <a href="admin_dashboard.php?view=edit-user&user_id=<?= $u['id'] ?>"
                                class="btn btn-warning btn-sm w-100">Sửa thông tin</a>
                            <hr>
                            <a href="admin_dashboard.php?view=delete-user&user_id=<?= $u['id'] ?>"
                                class="btn btn-danger btn-sm w-100">Xóa người dùng</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>