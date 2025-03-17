<?php 
include 'app/views/shares/header.php'; 
// Khởi động session nếu chưa có
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Lấy token từ session (nếu có)
$token = $_SESSION['jwtToken'] ?? null;
?>

<h1>Danh sách sản phẩm</h1>
<a href="/blueskyweb/Product/add" class="btn btn-success mb-2">Thêm sản phẩm mới</a>
<ul class="list-group" id="product-list">
    <!-- Danh sách sản phẩm sẽ được tải từ API và hiển thị tại đây -->
</ul>

<?php include 'app/views/shares/footer.php'; ?>

<script>
    // Chèn token từ PHP vào JavaScript
    const token = <?php echo json_encode($token); ?>;

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
                const productItem = document.createElement('li');
                productItem.className = 'list-group-item';
                productItem.innerHTML = ` 
                    <div class="d-flex align-items-center">
                        <img src="${product.image}" alt="${product.name}" class="product-image mr-3">
                        <div>
                            <h2><a href="/blueskyweb/Product/show/${product.id}">${product.name}</a></h2> 
                            <p>${product.description}</p> 
                            <p>Giá: ${product.price} VND</p> 
                            <p>Danh mục: ${product.category_name}</p> 
                            <a href="/blueskyweb/Product/edit/${product.id}" class="btn btn-warning">Sửa</a> 
                            <button class="btn btn-danger" onclick="deleteProduct(${product.id})">Xóa</button>
                            <button class="btn btn-primary" onclick="addToCart(${product.id})">Thêm vào giỏ</button>
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
                quantity: 1 // Mặc định thêm 1 sản phẩm
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
                updateCartCount(); // Cập nhật số lượng giỏ hàng trong header
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
        fetch('/blueskyweb/account/getUserById', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.user) {
                const userId = data.user.id;
                fetch(`/blueskyweb/api/cart/${userId}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + token
                    }
                })
                .then(response => response.json())
                .then(cart => {
                    const cartCount = cart.length || 0;
                    document.getElementById('cart-count').innerText = cartCount;
                })
                .catch(error => console.error("Lỗi khi cập nhật số lượng giỏ hàng:", error));
            }
        })
        .catch(error => console.error("Lỗi khi lấy userId:", error));
    }
</script>

<style>
    .product-image {
        max-width: 100px;
        height: auto;
        border-radius: 5px;
    }
</style>