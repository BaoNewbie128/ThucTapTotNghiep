<?php
    session_start();
    require_once __DIR__ .  "/../config/db.php";
    $product_id = intval($_POST["product_id"]);
    $quantity = isset($_POST["quantity"]) ? max(1, intval($_POST["quantity"])) : 1;
        
    if(!isset($_SESSION['user_id'])){
        $_SESSION['pending_cart'] = [
            "product_id" => $product_id,
            "quantity" => $quantity
        ];
        $_SESSION['redirect_after_login'] =  $_SERVER['HTTP_REFERER'] ?? '/index.php';
        header("Location: /login.php");
        exit;
    }
    if($product_id <= 0){
    header("Location: /index.php");
    exit;
}
$check = $conn->query("SELECT id FROM products WHERE id = $product_id");
if($check->num_rows == 0){
    header("Location: /index.php");
    exit;
}
    $user_id = $_SESSION["user_id"];
    $sql = "SELECT id FROM cart WHERE user_id = $user_id";
    $result = $conn->query($sql);
    if($result->num_rows > 0){
        $cart_id = $result->fetch_assoc()["id"];    
    }else{
        $conn->query("INSERT INTO cart (user_id) VALUES ($user_id)");
        $cart_id = $conn->insert_id;
    }
    $sql2 = "SELECT quantity from cart_items WHERE cart_id = $cart_id AND product_id = $product_id";
    $result2 = $conn->query($sql2);
    if($result2->num_rows > 0){
        $conn->query("UPDATE cart_items 
                             SET quantity = quantity + $quantity 
                             WHERE cart_id = $cart_id AND product_id = $product_id");
    }else {
        $conn->query("INSERT INTO cart_items (cart_id, product_id, quantity) 
                             VALUES ($cart_id, $product_id, $quantity)");
    }
    header("Location: /user/cart_item.php");
    exit;
?>