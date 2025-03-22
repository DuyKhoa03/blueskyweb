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

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title">Danh sách sản phẩm</h1>
</div>
<div class="row" id="product-list">
    <!-- Danh sách sản phẩm sẽ được tải từ API và hiển thị tại đây -->
</div>

<?php include 'app/views/shares/footer.php'; ?>

<script>
    // Chèn token từ PHP vào JavaScript
    const token = <?php echo json_encode($token); ?>;
    const userId = <?php echo json_encode($userid); ?>;

    document.addEventListener("DOMContentLoaded", function () {
        if (!token) {
            alert('Vui lòng đăng nhập');
            location.href = '/blueskyweb/account/login';
            return;
        }

        fetch('/blueskyweb/api/product', {
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
        .then(data => {
            if (!data) return;
            const productList = document.getElementById('product-list');
            productList.innerHTML = ''; // Xóa danh sách cũ trước khi load mới
            
            data.forEach(product => {
                const productItem = document.createElement('div');
                productItem.className = 'col-lg-3 col-md-4 col-sm-6 mb-4';
                productItem.innerHTML = ` 
                    <div class="card product-card shadow-sm h-100">
                        <img src="${product.image}" alt="${product.name}" class="card-img-top product-image">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title product-name">${product.name}</h5>
                            <p class="card-text product-description text-muted">${product.description}</p>
                            <p class="card-text product-price text-primary font-weight-bold">Giá: ${product.price.toLocaleString()} VND</p>
                            <p class="card-text product-category text-secondary">Danh mục: ${product.category_name}</p>
                            <div class="mt-auto d-flex justify-content-between">
                                <a href="/blueskyweb/Product/edit/${product.id}" class="btn btn-warning btn-sm btn-action">
                                    <i class="fas fa-edit mr-1"></i> Sửa
                                </a> 
                                <button class="btn btn-danger btn-sm btn-action" onclick="deleteProduct(${product.id})">
                                    <i class="fas fa-trash-alt mr-1"></i> Xóa
                                </button>
                                <button class="btn btn-primary btn-sm btn-action" onclick="addToCart(${product.id})">
                                    <i class="fas fa-cart-plus mr-1"></i> Thêm vào giỏ
                                </button>
                            </div>
                        </div>
                    </div>
                `; 
                productList.appendChild(productItem);
            });
        })
        .catch(error => console.error("Lỗi khi tải danh sách sản phẩm:", error));
    });

    function deleteProduct(id) {
        if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
            fetch(`/blueskyweb/api/product/${id}`, {
                method: 'DELETE',
                headers: { 'Authorization': 'Bearer ' + token }
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
                if (!data) return;
                if (data.message === 'Sản phẩm đã bị xóa') {
                    alert("Xóa sản phẩm thành công!");
                    location.reload();
                } else {
                    alert("Xóa sản phẩm thất bại: " + data.message);
                }
            })
            .catch(error => {
                console.error("Lỗi khi xóa sản phẩm:", error);
                alert("Lỗi hệ thống khi xóa sản phẩm!");
            });
        }
    }

    function addToCart(productId) {
        fetch('/blueskyweb/api/cart/store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: 1
            })
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
            if (!data) return;
            if (data.message === 'Added to cart') {
                alert('Đã thêm sản phẩm vào giỏ hàng!');
                updateCartCount();
            } else {
                alert('Thêm vào giỏ hàng thất bại: ' + (data.message || 'Lỗi không xác định'));
            }
        })
        .catch(error => {
            console.error("Lỗi khi thêm vào giỏ hàng:", error);
            alert("Lỗi hệ thống khi thêm vào giỏ hàng!");
        });
    }

    function updateCartCount() {
        fetch(`/blueskyweb/api/cart/${userId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            }
        })
        .then(response => response.json())
        .then(cart => {
            if (cart && Array.isArray(cart)) {
                document.getElementById('cart-count').innerText = cart.length;
            }
        })
        .catch(error => console.error("Lỗi khi cập nhật số lượng giỏ hàng:", error));
    }
</script>
<link rel="stylesheet" href="/blueskyweb/public/css/product.css">
