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
const userid = null;
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

<h1>Giỏ hàng của bạn <?php echo $userid; ?></h1>
<ul class="list-group" id="cart-list">
    <!-- Danh sách sản phẩm trong giỏ hàng sẽ được tải từ API -->
</ul>
<button id="checkout-btn" class="btn btn-primary mt-3">Thanh toán</button>
<span id="cart-total" class="ml-3">Tổng tiền: 0 VND</span>

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

        // Lấy danh sách giỏ hàng trực tiếp bằng userId từ token
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
                return;
            }
            return response.json();
        })
        .then(cart => {
            if (!cart) return;
            const cartList = document.getElementById('cart-list');
            cartList.innerHTML = '';
            let totalCartPrice = 0; // Biến để lưu tổng tiền

            cart.forEach(item => {
                const cartItem = document.createElement('li');
                cartItem.className = 'list-group-item';
                cartItem.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5>${item.name}</h5>
                            <img src="${item.image}" alt="${item.name}" class="product-image mr-3">
                            <p>Giá: ${item.price} VND</p>
                            <p>Số lượng: <input type="number" min="1" value="${item.quantity}" onchange="updateCart(${item.id}, this.value)"></p>
                            <p>Thành tiền: ${item.total_price} VND</p>
                        </div>
                        <button class="btn btn-danger" onclick="removeFromCart(${item.id})">Xóa</button>
                    </div>
                `;
                cartList.appendChild(cartItem);
                totalCartPrice += parseFloat(item.total_price); // Cộng dồn tổng tiền
            });

            // Hiển thị tổng tiền
            document.getElementById('cart-total').textContent = `Tổng tiền: ${totalCartPrice.toLocaleString()} VND`;
        })
        .catch(error => console.error("Lỗi khi tải giỏ hàng:", error));
    });

    function updateCart(cartId, quantity) {
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
                location.reload(); // Tải lại trang để cập nhật tổng tiền
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
                    location.reload(); // Tải lại trang để cập nhật tổng tiền
                }
            })
            .catch(error => console.error("Lỗi khi xóa sản phẩm:", error));
        }
    }

    document.getElementById('checkout-btn').addEventListener('click', function () {
        alert('Chức năng thanh toán đang được phát triển!');
        // Có thể thêm logic gọi API thanh toán ở đây
    });
</script>