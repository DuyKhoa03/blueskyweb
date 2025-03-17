<?php
// Khởi động session nếu chưa có
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Lấy token từ session (nếu có)
$token = $_SESSION['jwtToken'] ?? null;
require_once 'app/utils/JWTHandler.php'; // Để giải mã token
$jwtHandler = new JWTHandler();
$username = null;
$userid = null;
if ($token) {
    try {
            $tokenData = $jwtHandler->decode($token);
            $username = $tokenData['username'] ?? 'Không xác định';
            $userid = $tokenData['id'] ?? null;
    } catch (Exception $e) {
        unset($_SESSION['jwtToken']); // Xóa token nếu không hợp lệ
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .product-image {
            max-width: 100px;
            height: auto;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="/blueskyweb/Product">Quản lý sản phẩm</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="/blueskyweb/Product">Danh sách sản phẩm</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/blueskyweb/Category">Danh sách danh mục</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/blueskyweb/Cart">Giỏ hàng</a>
                </li>
                <li class="nav-item" id="nav-login">
                    <a class="nav-link" href="/blueskyweb/account/login">Login</a>
                </li>

                <li class="nav-item" id="nav-user" style="display: none;">
    <a class="nav-link" href="/blueskyweb/account/profile" id="user-link"></a>
</li>

                <li class="nav-item" id="nav-logout" style="display: none;">
                    <a class="nav-link" href="#" onclick="logout()">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <script>
        function logout() {
            localStorage.removeItem('jwtToken');
            location.href = '/blueskyweb/account/login';
        }

        document.addEventListener("DOMContentLoaded", function () {
    const token = <?php echo json_encode($token); ?>;;

    if (token) {
        try {            
            const username = <?php echo json_encode($username); ?>;
            
            document.getElementById('user-link').innerText = username;
            document.getElementById('nav-user').style.display = 'block';

            document.getElementById('nav-login').style.display = 'none';
            document.getElementById('nav-logout').style.display = 'block';
        } catch (error) {
            console.error("Lỗi giải mã token:", error);
        }
    } else {
        document.getElementById('nav-login').style.display = 'block';
        document.getElementById('nav-logout').style.display = 'none';
        document.getElementById('nav-user').style.display = 'none';
    }
});

    </script>
    <div class="container mt-4"></div>
