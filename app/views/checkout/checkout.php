<?php 
include 'app/views/shares/header.php'; 

// Kiểm tra nếu chưa đăng nhập
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['jwtToken'])) {
    header('Location: /blueskyweb/account/login');
    exit();
}

require_once 'app/utils/JWTHandler.php'; 
$jwtHandler = new JWTHandler();

$token = $_SESSION['jwtToken'];
$userId = null;
$username = null;

try {
    $tokenData = $jwtHandler->decode($token);
    $userId = $tokenData['id'] ?? null;
    $username = $tokenData['username'] ?? 'Không xác định';
} catch (Exception $e) {
    unset($_SESSION['jwtToken']);
    header('Location: /blueskyweb/account/login');
    exit();
}

// Nếu không có userId, quay lại đăng nhập
if (!$userId) {
    header('Location: /blueskyweb/account/login');
    exit();
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title">Thanh toán - Xác nhận đơn hàng</h1>
</div>

<!-- Bảng danh sách sản phẩm trong giỏ hàng -->
<div id="cart-list" class="table-responsive mb-4">
    <table class="table table-bordered table-hover">
        <thead class="thead-light">
            <tr>
                <th>Hình ảnh</th>
                <th>Tên sản phẩm</th>
                <th>Giá</th>
                <th>Số lượng</th>
                <th>Thành tiền</th>
            </tr>
        </thead>
        <tbody id="cart-items"></tbody>
    </table>
</div>

<!-- Tổng tiền -->
<div id="cart-summary" class="d-flex justify-content-end align-items-center mb-4">
    <h4 id="cart-total">Tổng tiền: 0 VND</h4>
</div>

<!-- Form nhập thông tin -->
<div class="card p-4">
    <h3 class="mb-3">Thông tin giao hàng</h3>
    <form id="checkout-form">
        <div class="form-group">
            <label for="address">Địa chỉ giao hàng</label>
            <textarea id="address" name="address" class="form-control" rows="3" placeholder="Nhập địa chỉ giao hàng" required></textarea>
        </div>
        <button type="submit" class="btn btn-success btn-block">Xác nhận thanh toán</button>
    </form>
</div>

<?php include 'app/views/shares/footer.php'; ?>

<script>
    const token = <?php echo json_encode($token); ?>;
    const userId = <?php echo json_encode($userId); ?>;

    let totalCartPrice = 0; // Khởi tạo biến tổng tiền giỏ hàng

    document.addEventListener("DOMContentLoaded", function () {
        fetch(`/blueskyweb/api/cart/${userId}`, {
            method: 'GET',
            headers: { 'Authorization': 'Bearer ' + token }
        })
        .then(response => response.json())
        .then(cart => {
            const cartItems = document.getElementById('cart-items');
            const cartSummary = document.getElementById('cart-summary');
            cartItems.innerHTML = '';

            if (cart.length === 0) {
                cartSummary.style.display = 'none';
                cartItems.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Giỏ hàng trống.</td></tr>';
                return;
            }

            cartSummary.style.display = 'flex';
            totalCartPrice = 0;

            cart.forEach(item => {
                totalCartPrice += parseFloat(item.total_price || 0); // Tính tổng tiền
                const cartItem = document.createElement('tr');
                cartItem.innerHTML = `
                    <td><img src="${item.image}" alt="${item.name}" class="product-image"></td>
                    <td>${item.name}</td>
                    <td>${item.price.toLocaleString()} VND</td>
                    <td>${item.quantity}</td>
                    <td>${item.total_price.toLocaleString()} VND</td>
                `;
                cartItems.appendChild(cartItem);
            });

            // Hiển thị tổng tiền
            document.getElementById('cart-total').textContent = `Tổng tiền: ${totalCartPrice.toLocaleString()} VND`;
        });

        // Gửi thanh toán
        document.getElementById('checkout-form').addEventListener('submit', function (e) {
            e.preventDefault();
            const address = document.getElementById('address').value;

            fetch('/blueskyweb/api/checkout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + token
                },
                body: JSON.stringify({ address, totalCartPrice })
            })
            .then(response => response.json())
            .then(data => {
                if (data.message === 'Checkout successful') {
                    alert(`Thanh toán thành công! Mã đơn hàng: ${data.order_id}`);
                    location.href = '/blueskyweb/cart'; // Quay về giỏ hàng
                } else {
                    alert('Lỗi thanh toán: ' + data.message);
                }
            })
            .catch(error => console.error("Lỗi khi thanh toán:", error));
        });
    });
</script>

<style>
    /* Tùy chỉnh tiêu đề */
    .page-title {
        font-size: 2.5rem;
        font-weight: bold;
        color: #007bff;
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }

    /* Tùy chỉnh bảng giỏ hàng */
    .table {
        background-color: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .table th {
        background-color: #f8f9fa;
        color: #333;
        font-weight: bold;
        text-align: center;
    }

    .table td {
        vertical-align: middle;
        text-align: center;
    }

    .product-image {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 5px;
    }

    /* Tùy chỉnh tổng tiền */
    #cart-total {
        font-size: 1.5rem;
        font-weight: bold;
        color: #007bff;
    }

    /* Tùy chỉnh form thanh toán */
    .card {
        background-color: #f8f9fa;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .card h3 {
        font-size: 1.5rem;
        color: #333;
    }

    .form-control {
        border-radius: 5px;
        border: 1px solid #ced4da;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
    }

    .btn-success {
        background-color: #28a745;
        border: none;
        padding: 10px 20px;
        font-weight: bold;
        transition: all 0.3s ease;
    }

    .btn-success:hover {
        background-color: #218838;
        transform: translateY(-2px);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-title {
            font-size: 2rem;
        }

        .table th, .table td {
            font-size: 0.9rem;
        }

        .product-image {
            width: 80px;
            height: 80px;
        }

        #cart-total {
            font-size: 1.2rem;
        }

        .card h3 {
            font-size: 1.2rem;
        }
    }

    @media (max-width: 576px) {
        .table th, .table td {
            font-size: 0.8rem;
        }

        .product-image {
            width: 60px;
            height: 60px;
        }
    }
</style>