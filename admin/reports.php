<?php
require __DIR__ . "/../config/db.php";

// Query doanh thu theo tháng (6 tháng gần nhất)
$revenue_sql = "SELECT YEAR(created_at) as year, MONTH(created_at) as month, SUM(total) as revenue 
                FROM orders 
                WHERE status != 'cancelled' 
                GROUP BY year, month 
                ORDER BY year DESC, month DESC 
                LIMIT 6";
$revenue_result = $conn->query($revenue_sql);
$revenue_data = [];
$labels = [];
$revenues = [];
if ($revenue_result) {
    while ($row = $revenue_result->fetch_assoc()) {
        $labels[] = $row['month'] . '/' . $row['year'];
        $revenues[] = $row['revenue'];
    }
    $revenue_result->free();
}

// Query top sản phẩm bán chạy
$top_products_sql = "SELECT p.brand, p.model, SUM(oi.quantity) as total_sold 
                     FROM order_items oi 
                     JOIN products p ON oi.product_id = p.id 
                     JOIN orders o ON oi.order_id = o.id 
                     WHERE o.status != 'cancelled' 
                     GROUP BY p.brand, p.model 
                     ORDER BY total_sold DESC 
                     LIMIT 10";
$top_result = $conn->query($top_products_sql);
$top_products = [];
if ($top_result) {
    while ($row = $top_result->fetch_assoc()) {
        $top_products[] = $row;
    }
    $top_result->free();
}

$conn->close();
?>

<h2 class="text-primary mb-4">Báo cáo thống kê</h2>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Doanh thu theo tháng</h5>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Top 10 sản phẩm bán chạy</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($top_products)): ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($top_products as $index => $product): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><?php echo ($index + 1) . '. ' . htmlspecialchars($product['brand'] . ' ' . $product['model']); ?></span>
                        <span class="badge bg-primary rounded-pill">
                            <?php echo $product['total_sold']; ?> sản phẩm đã bán
                        </span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p class="text-muted">Chưa có dữ liệu bán hàng.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_reverse($labels)); ?>,
        datasets: [{
            label: 'Doanh thu (₫)',
            data: <?php echo json_encode(array_reverse($revenues)); ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString() + '₫';
                    }
                }
            }
        }
    }
});
</script>