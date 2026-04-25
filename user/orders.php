<?php
session_start();
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/security.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

$user_id = intval($_SESSION["user_id"]);
if (isset($_POST["action"]) && $_POST["action"] === "checkout") {
    verify_csrf();
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("SELECT id FROM cart WHERE user_id = ? FOR UPDATE");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $cart = $stmt->get_result()->fetch_assoc();
        if (!$cart) {
            throw new Exception("Giỏ hàng trống!");
        }
        $cart_id = intval($cart["id"]);

        $stmt = $conn->prepare("SELECT ci.product_id, ci.quantity, p.stock, p.price
                                FROM cart_items ci
                                JOIN products p ON ci.product_id = p.id
                                WHERE ci.cart_id = ? FOR UPDATE");
        $stmt->bind_param("i", $cart_id);
        $stmt->execute();
        $items_result = $stmt->get_result();

        $items = [];
        $total_amount = 0;
        while ($item = $items_result->fetch_assoc()) {
            $pid = intval($item['product_id']);
            $qty = intval($item['quantity']);
            $stock = intval($item['stock']);
            $price = (float)$item['price'];
            if ($qty <= 0 || $stock < $qty) {
                throw new Exception("Sản phẩm ID $pid không đủ tồn kho để đặt hàng!");
            }
            $items[] = ['product_id' => $pid, 'quantity' => $qty, 'price' => $price];
            $total_amount += $qty * $price;
        }
        if (!$items) {
            throw new Exception("Giỏ hàng trống!");
        }

        $shipping_fee = 30000;
        $discount_amount = min($total_amount, (float)($_SESSION['coupon']['discount'] ?? 0));
        $total_with_shipping = max(0, $total_amount + $shipping_fee - $discount_amount);

        $stmt = $conn->prepare("INSERT INTO orders (user_id, status, total, shipping_fee, discount) VALUES (?, 'pending', ?, ?, ?)");
        $stmt->bind_param("iddd", $user_id, $total_with_shipping, $shipping_fee, $discount_amount);
        $stmt->execute();
        $order_id = $conn->insert_id;

        $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
        foreach ($items as $item) {
            $stmt_item->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
            $stmt_item->execute();
            $stmt_stock->bind_param("iii", $item['quantity'], $item['product_id'], $item['quantity']);
            $stmt_stock->execute();
            if ($stmt_stock->affected_rows !== 1) {
                throw new Exception("Không thể trừ tồn kho sản phẩm ID {$item['product_id']}.");
            }
        }

        $stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_id = ?");
        $stmt->bind_param("i", $cart_id);
        $stmt->execute();
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cart_id, $user_id);
        $stmt->execute();
        unset($_SESSION['coupon']);
        $conn->commit();
        header("Location: order_items.php");
        exit;
    } catch (Throwable $e) {
        $conn->rollback();
        $_SESSION['message'] = $e->getMessage();
        header("Location: cart_item.php");
        exit;
    }
}

header("Location: cart_item.php");
exit;
?>