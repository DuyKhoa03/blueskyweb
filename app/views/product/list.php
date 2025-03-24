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

<div class="container mt-5">
    <!-- Tiêu đề và thanh tìm kiếm trên cùng hàng -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h1 class="page-title text-primary mb-0">Danh sách sản phẩm</h1>
        <form id="searchForm" class="d-flex align-items-center" style="max-width: 400px; flex: 1;">
            <input type="text" class="form-control shadow-sm" id="searchInput" placeholder="Tìm kiếm sản phẩm..." style="border-radius: 20px 0 0 20px; border-right: none;">
            <button type="submit" class="btn btn-primary shadow-sm" style="border-radius: 0 20px 20px 0; padding: 0.5rem 1rem;">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>

    <!-- Danh sách sản phẩm -->
    <div class="row" id="product-list">
        <!-- Sản phẩm sẽ được hiển thị ở đây -->
    </div>

    <!-- Phân trang -->
    <nav id="pagination" class="mt-4 d-flex justify-content-center"></nav>
</div>

<?php include 'app/views/shares/footer.php'; ?>

<!-- Thêm CSS để cải thiện giao diện -->
<style>
/* Container chính */
.container {
    padding: 2rem 0;
}

/* Tiêu đề và thanh tìm kiếm */
.page-title {
    font-size: 2rem;
    font-weight: 700;
    color: #007bff;
    border-bottom: 2px solid #007bff;
    padding-bottom: 0.5rem;
}

/* Form tìm kiếm */
#searchForm {
    min-width: 300px;
}

#searchForm input {
    border: 1px solid #ced4da;
    transition: border-color 0.3s ease;
}

#searchForm input:focus {
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
    outline: none;
}

#searchForm button {
    border: 1px solid #007bff;
    border-left: none;
    transition: background-color 0.3s ease;
}

#searchForm button:hover {
    background-color: #0056b3;
}

/* Card sản phẩm */
.product-card {
    border: none;
    border-radius: 10px;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.product-image {
    height: 200px;
    width: 100%;
    object-fit: contain; /* Đảm bảo ảnh vừa khung mà không bị cắt */
    background-color: #f8f9fa; /* Nền sáng để ảnh nổi bật */
    padding: 10px; /* Khoảng cách bên trong để ảnh không sát viền */
    border-bottom: 1px solid #e9ecef;
    cursor: pointer;
    transition: opacity 0.3s ease;
}

.product-image:hover {
    opacity: 0.9;
}

.product-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: #343a40;
    margin-bottom: 0.5rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    cursor: pointer;
    transition: color 0.3s ease;
}

.product-name:hover {
    color: #007bff;
}

.product-price {
    font-size: 1rem;
    color: #28a745;
    margin-bottom: 0.5rem;
}

.product-category {
    font-size: 0.9rem;
    color: #6c757d;
    margin-bottom: 1rem;
}

/* Nút hành động */
.btn-action {
    padding: 0.3rem 0.8rem;
    font-size: 0.9rem;
    border-radius: 5px;
    transition: background-color 0.3s ease;
}

.btn-action i {
    font-size: 0.8rem;
}

.btn-primary {
    background-color: #007bff;
    border: none;
}

.btn-primary:hover {
    background-color: #0056b3;
}

/* Phân trang */
.pagination .page-link {
    border-radius: 5px;
    margin: 0 3px;
    color: #007bff;
    border: 1px solid #dee2e6;
    transition: background-color 0.3s ease, color 0.3s ease;
}

.pagination .page-link:hover {
    background-color: #007bff;
    color: #fff;
    border-color: #007bff;
}

.pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
    color: #fff;
}
</style>

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
        productList.innerHTML = '<p class="text-muted text-center w-100">Không tìm thấy sản phẩm phù hợp.</p>';
        return;
    }

    pageItems.forEach(product => {
        const productItem = document.createElement('div');
        productItem.className = 'col-lg-3 col-md-4 col-sm-6 mb-4';
        productItem.innerHTML = `
            <div class="card product-card shadow-sm h-100">
                <a href="/blueskyweb/Product/show/${product.id}">
                    <img src="${product.image}" alt="${product.name}" class="card-img-top product-image">
                </a>
                <div class="card-body d-flex flex-column">
                    <a href="/blueskyweb/Product/show/${product.id}" class="text-decoration-none">
                        <h5 class="card-title product-name">${product.name}</h5>
                    </a>
                    <p class="card-text product-price text-success font-weight-bold">Giá: ${parseFloat(product.price).toLocaleString()} VND</p>
                    <p class="card-text product-category text-secondary">Danh mục: ${product.category_name}</p>
                    <div class="mt-auto d-flex justify-content-end">
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
        html += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(${currentPage - 1})"><i class="fas fa-chevron-left"></i> Trước</a></li>`;
    }

    for (let i = 1; i <= totalPages; i++) {
        html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
            <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
        </li>`;
    }

    if (currentPage < totalPages) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(${currentPage + 1})">Sau <i class="fas fa-chevron-right"></i></a></li>`;
    }

    html += '</ul>';
    pagination.innerHTML = html;
}

function changePage(page) {
    currentPage = page;
    renderProducts(currentPage);
    window.scrollTo({ top: 0, behavior: 'smooth' }); // Cuộn lên đầu trang
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
        loadProducts(keyword, true); // true = bỏ qua category
    });
});

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