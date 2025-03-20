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

        .navbar-brand img {
            height: 40px;
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

        /* Tùy chỉnh sidebar */
        .sidebar {
            position: fixed;
            top: 60px;
            left: 0;
            height: calc(100% - 60px);
            width: 250px;
            background-color: #2c3e50;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .sidebar.collapsed {
            width: 60px; /* Thu gọn trên desktop */
        }

        .sidebar.hidden {
            left: -250px; /* Ẩn trên mobile */
        }

        .sidebar .sidebar-header {
            padding: 20px;
            background-color: #34495e;
            color: #fff;
            text-align: center;
            font-size: 1.2rem;
            font-weight: bold;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .sidebar.collapsed .sidebar-header {
            padding: 20px 10px;
            font-size: 1rem;
        }

        .sidebar .nav-link {
            color: #dfe6e9 !important;
            padding: 15px 20px !important;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }

        .sidebar.collapsed .nav-link {
            padding: 15px 10px !important;
            justify-content: center;
        }

        .sidebar .nav-link:hover {
            background-color: #34495e;
            color: #fff !important;
        }

        .sidebar .nav-link i {
            margin-right: 10px;
        }

        .sidebar.collapsed .nav-link i {
            margin-right: 0;
        }

        .sidebar.collapsed .nav-link span {
            display: none;
        }

        .sidebar.collapsed .sidebar-header span {
            display: none;
        }

        /* Tùy chỉnh nội dung chính */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            margin-top: 60px;
            transition: all 0.3s ease;
        }

        .main-content.collapsed {
            margin-left: 60px; /* Khi sidebar thu gọn trên desktop */
        }

        .main-content.hidden {
            margin-left: 0; /* Khi sidebar ẩn trên mobile */
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                left: -250px; /* Ẩn mặc định trên mobile */
            }

            .sidebar.collapsed {
                left: -250px; /* Đảm bảo sidebar ẩn khi collapsed trên mobile */
            }

            .sidebar.hidden {
                left: -250px; /* Ẩn trên mobile */
            }

            .main-content {
                margin-left: 0;
            }

            .main-content.collapsed {
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
        /* Tùy chỉnh footer */
.footer {
    background: linear-gradient(90deg, #007bff, #00c6ff);
    color: #fff;
    padding: 20px 0; /* Giảm padding */
    margin-top: 30px; /* Giảm margin-top */
}

.footer-title {
    font-size: 1.1rem; /* Giảm kích thước tiêu đề */
    font-weight: bold;
    color: #fff;
    margin-bottom: 10px; /* Giảm khoảng cách dưới */
    border-bottom: 2px solid #ffeb3b;
    padding-bottom: 5px;
}

.footer-link {
    color: #dfe6e9;
    text-decoration: none;
    transition: all 0.3s ease;
}

.footer-link:hover {
    color: #ffeb3b;
    text-decoration: underline;
}

.footer-divider {
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    margin: 15px 0; /* Giảm khoảng cách */
}

.footer .list-unstyled li {
    margin-bottom: 8px; /* Giảm khoảng cách giữa các dòng */
    display: flex;
    align-items: center;
}

.footer .list-unstyled li i {
    color: #ffeb3b;
    margin-right: 8px; /* Giảm khoảng cách icon */
}

.footer .text-muted {
    color: #dfe6e9 !important;
    font-size: 0.9rem; /* Giảm kích thước chữ bản quyền */
}

/* Responsive */
@media (max-width: 768px) {
    .footer .col-md-6 {
        text-align: center;
    }

    .footer .list-unstyled li {
        justify-content: center;
    }

    .footer-title {
        font-size: 1rem; /* Giảm kích thước tiêu đề trên mobile */
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
                    <button class="nav-link btn btn-link" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                </li>
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
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item" id="nav-login">
                    <a class="nav-link btn" href="/blueskyweb/account/login"><i class="fas fa-sign-in-alt mr-1"></i> Đăng nhập</a>
                </li>
                <li class="nav-item" id="nav-user" style="display: none;">
                    <a class="nav-link" href="/blueskyweb/account/profile" id="user-link"><i class="fas fa-user mr-1"></i></a>
                </li>
                <li class="nav-item" id="nav-logout" style="display: none;">
                    <a class="nav-link btn" href="/blueskyweb/account/logout" onclick="logout()"><i class="fas fa-sign-out-alt mr-1"></i> Đăng xuất</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <span><i class="fas fa-user-circle mr-2"></i> <?php echo $username ?? 'Khách'; ?></span>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="/blueskyweb/Product">
                    <i class="fas fa-box-open"></i> <span>Sản phẩm</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/blueskyweb/Category">
                    <i class="fas fa-tags"></i> <span>Danh mục</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/blueskyweb/Cart">
                    <i class="fas fa-shopping-cart"></i> <span>Giỏ hàng</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/blueskyweb/account/profile">
                    <i class="fas fa-user"></i> <span>Hồ sơ</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/blueskyweb/account/logout" onclick="logout()">
                    <i class="fas fa-sign-out-alt"></i> <span>Đăng xuất</span>
                </a>
            </li>
        </ul>
    </div>

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

            if (token) {
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

            // Toggle sidebar trên cả mobile và desktop
            document.getElementById('sidebarToggle').addEventListener('click', function () {
                const sidebar = document.getElementById('sidebar');
                const mainContent = document.getElementById('mainContent');
                const toggleIcon = this.querySelector('i');

                // Kiểm tra màn hình mobile hay desktop
                if (window.innerWidth <= 768) {
                    // Trên mobile: Ẩn/Hiển thị sidebar
                    sidebar.classList.toggle('hidden');
                    mainContent.classList.toggle('hidden');
                } else {
                    // Trên desktop: Thu gọn/Mở rộng sidebar
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('collapsed');
                }

                // Đổi icon khi toggle
                if (sidebar.classList.contains('collapsed') || sidebar.classList.contains('hidden')) {
                    toggleIcon.classList.remove('fa-bars');
                    toggleIcon.classList.add('fa-arrow-right');
                } else {
                    toggleIcon.classList.remove('fa-arrow-right');
                    toggleIcon.classList.add('fa-bars');
                }
            });
        });

        function logout() {
            localStorage.removeItem('jwtToken');
            window.location.href = '/blueskyweb/account/logout';
        }
    </script>