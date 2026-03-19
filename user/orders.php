<?php
    session_start();
    require_once __DIR__ . "/../config/db.php";
    if(!isset($_SESSION["user_id"])){
        header("Location: ../login.php");
        exit;
    }
    $user_id = $_SESSION["user_id"];
    if (isset($_GET["action"]) && $_GET["action"] == "checkout") {
    $sql = "SELECT id FROM cart WHERE user_id = $user_id";
    $result = $conn->query($sql);
    if ($result->num_rows == 0) {
        die("Giỏ hàng trống!");
    }
    $total_amount = 0;
    $cart_id = $result->fetch_assoc()["id"];
    $totals = $conn->query("SELECT ci.quantity,p.price FROM cart_items ci JOIN products p ON ci.product_id = p.id WHERE cart_id = $cart_id");
    while($total = $totals->fetch_assoc()) {
        $total_amount += $total['quantity'] * $total['price'];
    }
    $conn->query("INSERT INTO orders (user_id, status,total) VALUES ($user_id, 'pending', $total_amount)");
    $order_id = $conn->insert_id;
    $items = $conn->query("SELECT product_id, quantity FROM cart_items WHERE cart_id = $cart_id");
    while($item = $items->fetch_assoc()) {
        $pid = $item['product_id'];
        $qty = $item['quantity'];
        $priceQuery = $conn->query("SELECT price FROM products WHERE id = $pid");
        $price = $priceQuery->fetch_assoc()["price"];
        $conn->query("INSERT INTO order_items (order_id, product_id, quantity, price)
                      VALUES ($order_id, $pid, $qty, $price)");
        $conn->query("UPDATE products SET stock = stock - $qty WHERE id = $pid");
    }
    $conn->query("DELETE FROM cart_items WHERE cart_id = $cart_id");
    $conn->query("DELETE FROM cart WHERE id = $cart_id");
    header("Location: order_items.php");
    exit;
}
?>