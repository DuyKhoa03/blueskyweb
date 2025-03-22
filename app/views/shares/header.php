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
$role = null;
$isLoggedIn = false; // Biến để kiểm tra trạng thái đăng nhập
if ($token) {
    try {
        $tokenData = $jwtHandler->decode($token);
        $username = $tokenData['username'] ?? 'Không xác định';
        $userid = $tokenData['id'] ?? null;
        $role = $tokenData['role'] ?? null;
        $isLoggedIn = true; // Đánh dấu đã đăng nhập
    } catch (Exception $e) {
        unset($_SESSION['jwtToken']); // Xóa token nếu không hợp lệ
        $isLoggedIn = false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlueSky Shop</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="/blueskyweb/public/css/cart.css" rel="stylesheet">
    
    <style>
        /* Tùy chỉnh giao diện navbar */
        .navbar {
            background: linear-gradient(90deg, #007bff, #00c6ff);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 10px 20px;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 2000;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: #fff !important;
            display: flex;
            align-items: center;
        }

        .navbar-brand i {
            margin-right: 10px;
        }

        .nav-link {
            color: #fff !important;
            font-weight: 500;
            transition: color 0.3s ease;
            padding: 10px 15px !important;
        }

        .nav-link:hover {
            color: #ffeb3b !important;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
        }

        .cart-badge {
            background-color: #ffeb3b;
            color: #007bff;
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 50%;
            margin-left: 5px;
            font-size: 0.9rem;
        }

        .nav-item .btn {
            color: #fff;
            border: 1px solid #fff;
            padding: 5px 15px;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .nav-item .btn:hover {
            background-color: #ffeb3b;
            color: #007bff;
            border-color: #ffeb3b;
        }

        /* Tùy chỉnh nội dung chính */
        .main-content {
            padding: 20px;
            margin-top: 60px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }

            .navbar-brand {
                font-size: 1.2rem;
            }

            .nav-link {
                padding: 8px 10px !important;
            }

            .cart-badge {
                font-size: 0.8rem;
                padding: 3px 6px;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <a class="navbar-brand" href="/blueskyweb/Product">
            <i class="fas fa-shopping-bag mr-2"></i> BlueSky Shop
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/blueskyweb/Product"><i class="fas fa-box-open mr-1"></i> Sản phẩm</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/blueskyweb/Category"><i class="fas fa-tags mr-1"></i> Danh mục</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/blueskyweb/Cart">
                        <i class="fas fa-shopping-cart mr-1"></i> Giỏ hàng
                        <span id="cart-count" class="cart-badge">0</span>
                    </a>
                </li>
                <?php if ($role === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="/blueskyweb/admin">
                        <i class="fas fa-cog mr-1"></i> Quản lý Admin
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item" id="nav-login" style="<?php echo $isLoggedIn ? 'display: none;' : 'display: block;'; ?>">
                    <a class="nav-link btn" href="/blueskyweb/account/login"><i class="fas fa-sign-in-alt mr-1"></i> Đăng nhập</a>
                </li>
                <li class="nav-item" id="nav-user" style="<?php echo $isLoggedIn ? 'display: block;' : 'display: none;'; ?>">
                    <a class="nav-link" href="/blueskyweb/account/profile" id="user-link"><i class="fas fa-user mr-1"></i></a>
                </li>
                <li class="nav-item" id="nav-logout" style="<?php echo $isLoggedIn ? 'display: block;' : 'display: none;'; ?>">
                    <a class="nav-link btn" href="/blueskyweb/account/logout" onclick="logout()"><i class="fas fa-sign-out-alt mr-1"></i> Đăng xuất</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Nội dung chính -->
    <div class="main-content" id="mainContent">
        <div class="container-fluid">

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const token = <?php echo json_encode($token); ?>;
            const userId = <?php echo json_encode($userid); ?>;
            const isLoggedIn = <?php echo json_encode($isLoggedIn); ?>;

            if (token && isLoggedIn) {
                try {            
                    const username = <?php echo json_encode($username); ?>;
                    
                    document.getElementById('user-link').innerHTML = `<i class="fas fa-user mr-1"></i> ${username}`;
                    document.getElementById('nav-user').style.display = 'block';
                    document.getElementById('nav-login').style.display = 'none';
                    document.getElementById('nav-logout').style.display = 'block';

                    if (userId) {
                        fetch(`/blueskyweb/api/cart/${userId}`, {
                            method: 'GET',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': 'Bearer ' + token
                            }
                        })
                        .then(response => {
                            if (response.status === 401) {
                                console.error('Phiên đăng nhập không hợp lệ');
                                return;
                            }
                            return response.json();
                        })
                        .then(cart => {
                            if (cart && Array.isArray(cart)) {
                                const cartCount = cart.length;
                                document.getElementById('cart-count').innerText = cartCount;
                            }
                        })
                        .catch(error => console.error("Lỗi khi lấy số lượng giỏ hàng:", error));
                    }
                } catch (error) {
                    console.error("Lỗi giải mã token:", error);
                }
            } else {
                document.getElementById('nav-login').style.display = 'block';
                document.getElementById('nav-logout').style.display = 'none';
                document.getElementById('nav-user').style.display = 'none';
            }
        });

        function logout() {
            localStorage.removeItem('jwtToken');
            window.location.href = '/blueskyweb/account/logout';
        }
    </script>