<?php
session_start();
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/security.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /index.php");
    exit;
}

verify_csrf();

$product_id = intval($_POST["product_id"] ?? 0);
$quantity = isset($_POST["quantity"]) ? max(1, intval($_POST["quantity"])) : 1;

if (!isset($_SESSION['user_id'])) {
    $_SESSION['pending_cart'] = [
        "product_id" => $product_id,
        "quantity" => $quantity
    ];
    $_SESSION['redirect_after_login'] = is_safe_local_url($_SERVER['HTTP_REFERER'] ?? '/index.php');
    header("Location: /login.php");
    exit;
}

if ($product_id <= 0) {
    header("Location: /index.php");
    exit;
}

$user_id = intval($_SESSION["user_id"]);

$conn->begin_transaction();
try {
    $stmt = $conn->prepare("SELECT id, stock FROM products WHERE id = ? FOR UPDATE");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    if (!$product) {
        throw new Exception("Sản phẩm không tồn tại.");
    }

    $stmt = $conn->prepare("SELECT id FROM cart WHERE user_id = ? FOR UPDATE");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cart = $stmt->get_result()->fetch_assoc();
    if ($cart) {
        $cart_id = intval($cart['id']);
    } else {
        $stmt = $conn->prepare("INSERT INTO cart (user_id) VALUES (?)");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $cart_id = $conn->insert_id;
    }

    $stmt = $conn->prepare("SELECT quantity FROM cart_items WHERE cart_id = ? AND product_id = ? FOR UPDATE");
    $stmt->bind_param("ii", $cart_id, $product_id);
    $stmt->execute();
    $cart_item = $stmt->get_result()->fetch_assoc();
    $current_quantity = intval($cart_item['quantity'] ?? 0);
    if ($current_quantity + $quantity > intval($product['stock'])) {
        throw new Exception("Số lượng vượt quá tồn kho.");
    }

    if ($cart_item) {
        $stmt = $conn->prepare("UPDATE cart_items SET quantity = quantity + ? WHERE cart_id = ? AND product_id = ?");
        $stmt->bind_param("iii", $quantity, $cart_id, $product_id);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $cart_id, $product_id, $quantity);
        $stmt->execute();
    }
    $conn->commit();
    $_SESSION['message'] = "Đã thêm sản phẩm vào giỏ hàng!";
    $_SESSION['message_type'] = "success";
    header("Location: /user/cart_item.php");
    exit;
} catch (Throwable $e) {
    $conn->rollback();
    $_SESSION['message'] = $e->getMessage();
    header("Location: /index.php");
    exit;
}
?>