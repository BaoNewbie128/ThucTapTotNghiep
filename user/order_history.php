<?php
    session_start();
    include __DIR__ . "/../config/db.php";
    if(!isset($_SESSION["user_id"])){
        header("Location: ../login.php");
        exit;
    }
    $user_id = intval($_SESSION["user_id"]);
    $sql = "SELECT id, total, status,created_at 
            FROM orders 
            WHERE user_id = ? 
            AND status IN ('paid','shipping','completed','cancelled')
            ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $orders = $stmt->get_result();
    $orderlist = [];
    if($orders->num_rows > 0){
        while($order = $orders->fetch_assoc()){
            $order_id = intval($order['id']);
            $sql2 = "SELECT oi.product_id,oi.quantity,p.brand,p.model,p.image,p.price,p.color
                 FROM order_items oi 
                 JOIN products p ON oi.product_id = p.id 
                  WHERE oi.order_id = ?";
                 $item_stmt = $conn->prepare($sql2);
                 $item_stmt->bind_param("i", $order_id);
                 $item_stmt->execute();
                 $items = $item_stmt->get_result();
        $orderlist[] = ['order'=>$order,'items'=>$items];
        }
    }
    $statusTrans = [
    'pending' => 'Chưa xử lý',
    'paid' => 'Đã thanh toán',
    'shipping' => 'Đang giao hàng',
    'completed' => 'Hoàn thành',
    'cancelled' => 'Đã hủy'
];
$statusConfig = [
    'pending' => [
        'text' => 'Chờ xử lý',
        'class' => 'status-pending',
        'icon' => '⏳'
    ],
    'paid' => [
        'text' => 'Đã thanh toán',
        'class' => 'status-paid',
        'icon' => '✔'
    ],
    'shipping' => [
        'text' => 'Đang giao',
        'class' => 'status-shipping',
        'icon' => '🚚'
    ],
    'completed' => [
        'text' => 'Hoàn thành',
        'class' => 'status-completed',
        'icon' => '🎉'
    ],
    'cancelled' => [
        'text' => 'Đã hủy',
        'class' => 'status-cancelled',
        'icon' => '✖'
    ]
];
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Lịch sử mua hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>

<body class="bg-light">

    <div class="container mt-4">
        <h2 class="mb-4">Lịch sử mua hàng</h2>

        <?php if(empty($orderlist)): ?>
        <div class="alert alert-info">Chưa có đơn hàng nào</div>
        <?php endif; ?>

        <?php foreach($orderlist as $data): 
        $order = $data['order'];
        $items = $data['items'];
        $total = 0;

        if($items && $items->num_rows > 0){
            $items->data_seek(0);
            while($r = $items->fetch_assoc()){
                $total += $r['price'] * $r['quantity'];
            }
            $items->data_seek(0);
        }
    ?>

        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-secondary text-white">
                Đơn hàng #<?= $order['id'] ?> | <b>Thời gian đặt :</b> <?= $order['created_at'] ?> |
                <?php $st = $statusConfig[$order['status']] ?? null; ?>

                <span class="status-badge <?= $st['class'] ?>">
                    <span class="icon"><?= $st['icon'] ?></span>
                    <?= $st['text'] ?>
                </span>
            </div>

            <div class="card-body p-0">
                <table class="table table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>Ảnh</th>
                            <th>Sản phẩm</th>
                            <th>Số lượng</th>
                            <th>Giá</th>
                            <th>Tổng</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while($row = $items->fetch_assoc()): 
                    $subtotal = $row['price'] * $row['quantity'];
                ?>
                        <tr>
                            <td width="100">
                                <img src="../images/<?= $row['image'] ?>" width="80">
                            </td>
                            <td><?= $row['brand']." ".$row['model'] ?></td>
                            <td><?= $row['quantity'] ?></td>
                            <td><?= number_format($row['price']) ?>₫</td>
                            <td><?= number_format($subtotal) ?>₫</td>
                        </tr>
                        <?php endwhile; ?>

                        <tr class="fw-bold">
                            <td colspan="4" class="text-end">Tổng:</td>
                            <td class="text-danger"><?= number_format($total) ?>₫</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <?php endforeach; ?>

        <a href="order_items.php" class="btn btn-dark">Quay lại đơn hàng</a>

    </div>

</body>

</html>