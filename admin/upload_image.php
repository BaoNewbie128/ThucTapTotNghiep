<?php
require_once __DIR__ . '/../includes/admin_auth_check.php';
header('Content-Type: application/json');

if (isset($_FILES['upload'])) {
    try {
        $filename = upload_image_file($_FILES['upload'], __DIR__ . '/../images');
        echo json_encode([
            "url" => "/images/" . $filename
        ]);
    } catch (Throwable $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
}