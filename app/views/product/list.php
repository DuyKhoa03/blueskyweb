<?php
include 'app/views/shares/header.php';
if (session_status() == PHP_SESSION_NONE) session_start();
$token = $_SESSION['jwtToken'] ?? null;
require_once 'app/utils/JWTHandler.php';
$jwtHandler = new JWTHandler();
$username = null;
$userid = null;
if ($token) {
    try {
        $tokenData = $jwtHandler->decode($token);
        $username = $tokenData['username'] ?? 'Không xác định';
        $userid = $tokenData['id'] ?? null;
    } catch (Exception $e) {
        unset($_SESSION['jwtToken']);
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title">Danh sách sản phẩm</h1>
</div>
<form id="searchForm" class="mb-4 d-flex" style="max-width: 400px;">
    <input type="text" class="form-control me-2" id="searchInput" placeholder="Tìm kiếm sản phẩm...">
    <button type="submit" class="btn btn-primary">Tìm kiếm</button>
</form>

<div class="row" id="product-list">
    <!-- Sản phẩm sẽ được hiển thị ở đây -->
</div>

<!-- Phân trang -->
<nav id="pagination" class="mt-4 d-flex justify-content-center"></nav>

<?php include 'app/views/shares/footer.php'; ?>

<script>
const token = <?php echo json_encode($token); ?>;
const userId = <?php echo json_encode($userid); ?>;

function getQueryParam(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
}

let allProducts = [];
let currentPage = 1;
const itemsPerPage = 8;

function loadProducts(keyword = '', ignoreCategory = false) {
    const categoryId = ignoreCategory ? null : getQueryParam('category');
    let url = '/blueskyweb/api/product';
    const params = [];

    if (categoryId) params.push(`category=${encodeURIComponent(categoryId)}`);
    if (keyword) params.push(`keyword=${encodeURIComponent(keyword)}`);

    if (params.length > 0) {
        url += '?' + params.join('&');
    }

    fetch(url, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + token
        }
    })
    .then(response => response.json())
    .then(data => {
        allProducts = data || [];
        currentPage = 1;
        renderProducts(currentPage);
    })
    .catch(error => {
        console.error("Lỗi khi tải sản phẩm:", error);
        document.getElementById('product-list').innerHTML = '<p class="text-danger">Không thể tải sản phẩm.</p>';
    });
}

function renderProducts(page) {
    const productList = document.getElementById('product-list');
    productList.innerHTML = '';

    const start = (page - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const pageItems = allProducts.slice(start, end);

    if (pageItems.length === 0) {
        productList.innerHTML = '<p>Không tìm thấy sản phẩm phù hợp.</p>';
        return;
    }

    pageItems.forEach(product => {
        const productItem = document.createElement('div');
        productItem.className = 'col-lg-3 col-md-4 col-sm-6 mb-4';
        productItem.innerHTML = `
            <div class="card product-card shadow-sm h-100">
                <img src="${product.image}" alt="${product.name}" class="card-img-top product-image">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title product-name">${product.name}</h5>
                    <p class="card-text product-price text-primary font-weight-bold">Giá: ${parseFloat(product.price).toLocaleString()} VND</p>
                    <p class="card-text product-category text-secondary">Danh mục: ${product.category_name}</p>
                    <div class="mt-auto d-flex justify-content-between">
                        <a href="/blueskyweb/Product/edit/${product.id}" class="btn btn-warning btn-sm btn-action">
                            <i class="fas fa-edit mr-1"></i> Sửa
                        </a> 
                        <a href="/blueskyweb/Product/show/${product.id}" class="btn btn-outline-secondary btn-sm mt-2">
    Xem chi tiết
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

    renderPagination();
}

function renderPagination() {
    const pagination = document.getElementById('pagination');
    pagination.innerHTML = '';

    const totalPages = Math.ceil(allProducts.length / itemsPerPage);
    if (totalPages <= 1) return;

    let html = '<ul class="pagination">';

    if (currentPage > 1) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(${currentPage - 1})">Trước</a></li>`;
    }

    for (let i = 1; i <= totalPages; i++) {
        html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
            <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
        </li>`;
    }

    if (currentPage < totalPages) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(${currentPage + 1})">Sau</a></li>`;
    }

    html += '</ul>';
    pagination.innerHTML = html;
}

function changePage(page) {
    currentPage = page;
    renderProducts(currentPage);
}

document.addEventListener("DOMContentLoaded", function () {
    if (!token) {
        alert('Vui lòng đăng nhập');
        location.href = '/blueskyweb/account/login';
        return;
    }

    loadProducts(); // Mặc định có thể có category

    document.getElementById('searchForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const keyword = document.getElementById('searchInput').value.trim();
        loadProducts(keyword, true); // 👉 true = bỏ qua category
    });
});

// Hàm xử lý khác giữ nguyên
function deleteProduct(id) {
    if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
        fetch(`/blueskyweb/api/product/${id}`, {
            method: 'DELETE',
            headers: { 'Authorization': 'Bearer ' + token }
        })
        .then(response => response.json())
        .then(data => {
            if (data.message === 'Sản phẩm đã bị xóa') {
                alert("Đã xóa sản phẩm.");
                loadProducts(); // reload lại
            } else {
                alert("Xóa thất bại: " + data.message);
            }
        })
        .catch(error => {
            console.error("Lỗi khi xóa sản phẩm:", error);
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
        body: JSON.stringify({ product_id: productId, quantity: 1 })
    })
    .then(response => response.json())
    .then(data => {
        if (data.message === 'Added to cart') {
            alert('Đã thêm vào giỏ!');
            updateCartCount();
        } else {
            alert('Thêm thất bại!');
        }
    })
    .catch(error => {
        console.error("Lỗi khi thêm vào giỏ:", error);
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
    .catch(error => console.error("Lỗi giỏ hàng:", error));
}
</script>

<link rel="stylesheet" href="/blueskyweb/public/css/product.css">
