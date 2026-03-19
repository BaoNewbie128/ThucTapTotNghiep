<?php
    session_start();
    require_once __DIR__ .  "/../config/db.php";
    if(!isset($_SESSION["user_id"])){
        header("Location: ../login.php");
        exit;
    }
    if(!isset($_GET["product_id"])){
        die("Thiếu mã sản phẩm.");
    }
    $user_id = $_SESSION["user_id"];
    $product_id = intval($_GET["product_id"]);
    $quantity = isset($_GET["quantity"]) ? max(1, intval($_GET["quantity"])) : 1;
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
    header("Location: dashboard.php");
    exit;
?>