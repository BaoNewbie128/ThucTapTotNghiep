<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function verify_csrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        die('CSRF token khong hop le.');
    }
}

function is_safe_local_url(?string $url, string $fallback = '/index.php'): string {
    $url = trim((string)$url);
    if ($url === '') {
        return $fallback;
    }
    $parts = parse_url($url);
    if ($parts === false || isset($parts['scheme']) || isset($parts['host'])) {
        return $fallback;
    }
    if (str_starts_with($url, '//') || str_contains($url, "\r") || str_contains($url, "\n")) {
        return $fallback;
    }
    return $url;
}

function redirect_local(?string $url, string $fallback = '/index.php'): void {
    header('Location: ' . is_safe_local_url($url, $fallback));
    exit;
}

function require_login(): void {
    if (empty($_SESSION['user_id'])) {
        redirect_local('/login.php');
    }
}

function require_admin(): void {
    require_login();
    if (($_SESSION['role'] ?? '') !== 'admin') {
        http_response_code(403);
        die("<div class='alert alert-danger text-center'>Ban khong co quyen truy cap trang nay!</div>");
    }
}

function upload_image_file(array $file, string $targetDir, int $maxSize = 5242880): string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload file that bai.');
    }
    if (($file['size'] ?? 0) <= 0 || $file['size'] > $maxSize) {
        throw new RuntimeException('File anh qua lon hoac khong hop le.');
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Chi cho phep upload file anh JPG, PNG, GIF hoac WEBP.');
    }

    $dir = rtrim($targetDir, DIRECTORY_SEPARATOR);
    if (!is_dir($dir)) {
        throw new RuntimeException('Thu muc upload khong ton tai.');
    }
    $filename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    $target = $dir . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('Khong the luu file upload.');
    }
    return $filename;
}

function clean_html_content(string $html): string {
    $html = preg_replace('#<\s*(script|style|iframe|object|embed)[^>]*>.*?<\s*/\s*\1\s*>#is', '', $html);
    $html = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
    $html = preg_replace('/\s(href|src)\s*=\s*("|\')\s*javascript:[^"\']*\2/i', '', $html);
    return strip_tags($html, '<p><br><strong><b><em><i><u><ul><ol><li><blockquote><h2><h3><h4><a><img>');
}
?>