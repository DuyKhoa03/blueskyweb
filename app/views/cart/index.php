<?php
include 'app/views/shares/header.php';
// Khởi động session nếu chưa có
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Lấy token từ session (nếu có)
$token = $_SESSION['jwtToken'] ?? null;
require_once 'app/utils/JWTHandler.php'; // Để giải mã token
$jwtHandler = new JWTHandler();
$username = null;
$userid = null; // Sửa lỗi cú pháp: bỏ const
if ($token) {
    try {
        $tokenData = $jwtHandler->decode($token);
        $username = $tokenData['username'] ?? 'Không xác định';
        $userid = $tokenData['id'] ?? null;
    } catch (Exception $e) {
        unset($_SESSION['jwtToken']); // Xóa token nếu không hợp lệ
        header('Location: /blueskyweb/account/login');
        exit();
    }
}
// Nếu không có userid, chuyển hướng về trang đăng nhập
if (!$userid) {
    header('Location: /blueskyweb/account/login');
    exit();
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title">Giỏ hàng của bạn</h1>
</div>

<!-- Bảng danh sách sản phẩm trong giỏ hàng -->
<div id="cart-list" class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead class="thead-light">
            <tr>
                <th>Hình ảnh</th>
                <th>Tên sản phẩm</th>
                <th>Giá</th>
                <th>Số lượng</th>
                <th>Thành tiền</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody id="cart-items">
            <tr><td colspan="6" class="text-center text-muted empty-cart-message">Đang tải giỏ hàng...</td></tr>
        </tbody>
    </table>
</div>

<!-- Tổng tiền & Nút Thanh toán -->
<div id="cart-summary" class="d-flex justify-content-end align-items-center mb-4">
    <h4 id="cart-total" class="mr-3">Tổng tiền: 0 VND</h4>
    <button id="checkout-btn" class="btn btn-primary">Thanh toán</button>
</div>

<?php include 'app/views/shares/footer.php'; ?>

<script>
    // Chèn token và userId từ PHP vào JavaScript
    const token = <?php echo json_encode($token); ?>;
    const userId = <?php echo json_encode($userid); ?>;

    document.addEventListener("DOMContentLoaded", function () {
        if (!token || !userId) {
            alert('Vui lòng đăng nhập');
            location.href = '/blueskyweb/account/login';
            return;
        }

        const cartItems = document.getElementById('cart-items');
        const cartSummary = document.getElementById('cart-summary');

        // Ẩn cart-summary ngay từ đầu
        cartSummary.classList.remove('show');

        // Lấy danh sách giỏ hàng
        fetch(`/blueskyweb/api/cart/${userId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            }
        })
        .then(response => {
            if (response.status === 401) {
                alert('Phiên đăng nhập không hợp lệ, vui lòng đăng nhập lại!');
                location.href = '/blueskyweb/account/login';
                return null;
            }
            if (!response.ok) {
                throw new Error('Lỗi khi lấy dữ liệu giỏ hàng: ' + response.statusText);
            }
            return response.json();
        })
        .then(cart => {
            if (!cart) return; // Nếu đã chuyển hướng do lỗi 401, không xử lý tiếp

            console.log('Dữ liệu giỏ hàng:', cart); // Kiểm tra dữ liệu trả về

            cartItems.innerHTML = ''; // Xóa nội dung mặc định

            // Kiểm tra xem cart có phải là mảng không
            if (!Array.isArray(cart) || cart.length === 0) {
                cartSummary.classList.remove('show'); // Ẩn tổng tiền và nút thanh toán
                cartItems.innerHTML = '<tr><td colspan="6" class="text-center text-muted empty-cart-message">Chưa có sản phẩm nào trong giỏ hàng.</td></tr>';
                return;
            }

            // Hiển thị tổng tiền và nút thanh toán
            cartSummary.classList.add('show');
            let totalCartPrice = 0;

            cart.forEach(item => {
                const cartItem = document.createElement('tr');
                cartItem.innerHTML = `
                    <td><img src="${item.image}" alt="${item.name}" class="product-image"></td>
                    <td>${item.name}</td>
                    <td>${item.price.toLocaleString()} VND</td>
                    <td>
                        <div class="input-group quantity-control">
                            <button class="btn btn-outline-secondary btn-sm" onclick="updateCart(${item.id}, ${item.quantity - 1})">-</button>
                            <input type="number" class="form-control text-center" min="1" value="${item.quantity}" onchange="updateCart(${item.id}, this.value)">
                            <button class="btn btn-outline-secondary btn-sm" onclick="updateCart(${item.id}, ${item.quantity + 1})">+</button>
                        </div>
                    </td>
                    <td>${item.total_price.toLocaleString()} VND</td>
                    <td>
                        <button class="btn btn-danger btn-sm" onclick="removeFromCart(${item.id})">
                            <i class="fas fa-trash-alt"></i> Xóa
                        </button>
                    </td>
                `;
                cartItems.appendChild(cartItem);
                totalCartPrice += parseFloat(item.total_price);
            });

            // Hiển thị tổng tiền
            document.getElementById('cart-total').textContent = `Tổng tiền: ${totalCartPrice.toLocaleString()} VND`;
        })
        .catch(error => {
            console.error("Lỗi khi tải giỏ hàng:", error);
            cartItems.innerHTML = '<tr><td colspan="6" class="text-center text-muted empty-cart-message">Lỗi khi tải giỏ hàng. Vui lòng thử lại sau.</td></tr>';
            cartSummary.classList.remove('show'); // Ẩn tổng tiền và nút thanh toán nếu có lỗi
        });

        // Chuyển hướng đến trang checkout khi bấm nút "Thanh toán"
        document.getElementById('checkout-btn').addEventListener('click', function () {
            location.href = '/blueskyweb/checkout';
        });
    });

    function updateCart(cartId, quantity) {
        if (quantity < 1) {
            alert('Số lượng phải lớn hơn hoặc bằng 1!');
            return;
        }

        fetch(`/blueskyweb/api/cart/${cartId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({ quantity: parseInt(quantity) })
        })
        .then(response => {
            if (response.status === 401) {
                alert('Phiên đăng nhập không hợp lệ, vui lòng đăng nhập lại!');
                location.href = '/blueskyweb/account/login';
                return;
            }
            return response.json();
        })
        .then(data => {
            if (data && data.message === 'Cart updated') {
                alert('Cập nhật giỏ hàng thành công');
                location.reload();
            }
        })
        .catch(error => console.error("Lỗi khi cập nhật giỏ hàng:", error));
    }

    function removeFromCart(cartId) {
        if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?')) {
            fetch(`/blueskyweb/api/cart/${cartId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + token
                }
            })
            .then(response => {
                if (response.status === 401) {
                    alert('Phiên đăng nhập không hợp lệ, vui lòng đăng nhập lại!');
                    location.href = '/blueskyweb/account/login';
                    return;
                }
                return response.json();
            })
            .then(data => {
                if (data && data.message === 'Removed from cart') {
                    alert('Xóa sản phẩm thành công');
                    location.reload();
                }
            })
            .catch(error => console.error("Lỗi khi xóa sản phẩm:", error));
        }
    }
</script>