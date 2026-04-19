<?php
    session_start();
    require_once __DIR__ . "/../config/db.php";
    if(!isset($_SESSION["user_id"])){
        header("Location: ../login.php");
        exit;
    }
    $user_id = $_SESSION["user_id"];
    if (isset($_GET["action"]) && $_GET["action"] == "checkout") {
        $conn->begin_transaction();
        try{
        $sql = "SELECT id FROM cart WHERE user_id = $user_id FOR UPDATE";
        $result = $conn->query($sql);
             if ($result->num_rows == 0) {
                die("Giỏ hàng trống!");
            }
            $cart_id = $result->fetch_assoc()["id"];
            $items = $conn->query("SELECT ci.product_id, ci.quantity, p.stock ,p.price  
            FROM cart_items ci JOIN products p ON ci.product_id =p.id WHERE ci.cart_id = $cart_id 
            FOR UPDATE");
             $total_amount = 0;
            while ($item = $items->fetch_assoc()){
                $pid = $item['product_id'];
                $qty = $item['quantity'];
                $stock = $item['stock'];
                $price = $item['price'];
                if ($stock < $qty) {
                    throw new Exception("Sản phẩm ID $pid đã hết hàng, không thể đặt hàng!");
                }
                 $total_amount += $qty * $price;

            }
            $conn->query("INSERT INTO orders (user_id, status,total) VALUES ($user_id, 'pending', $total_amount)");
                $order_id = $conn->insert_id;
             $items = $conn->query("SELECT product_id, quantity FROM cart_items WHERE cart_id = $cart_id");
              while ($item = $items->fetch_assoc()) {
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
                $conn->commit();
                 header("Location: order_items.php");
                 exit;
        } catch (Exception $e) {
        $conn->rollback();

        echo "<div class='alert alert-danger text-center'>" . $e->getMessage() . "</div>";
    }
}
?>