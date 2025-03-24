<?php include 'app/views/shares/header.php'; ?>

<div class="container py-5">
    <h1 class="page-title text-primary mb-4">Quản lý Sản phẩm</h1>

    <!-- Tiêu đề và nút hành động -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h2 class="section-title mb-0">Danh sách sản phẩm</h2>
        <div class="d-flex gap-2">
            <a href="/blueskyweb/admin/products/add" class="btn btn-success btn-add-product">
                <i class="fas fa-plus me-1"></i> Thêm sản phẩm mới
            </a>
            <a href="/blueskyweb/admin/products/import" class="btn btn-outline-primary btn-import">
                <i class="fas fa-file-import me-1"></i> Import Excel
            </a>
        </div>
    </div>

    <!-- Tìm kiếm -->
    <div class="mb-4 d-flex" style="max-width: 400px;">
        <input type="text" id="searchInput" class="form-control shadow-sm" placeholder="Tìm theo tên sản phẩm..." style="border-radius: 20px 0 0 20px; border-right: none;">
        <button id="searchBtn" class="btn btn-primary shadow-sm" style="border-radius: 0 20px 20px 0; padding: 0.5rem 1rem;">
            <i class="fas fa-search"></i>
        </button>
    </div>

    <!-- Bảng sản phẩm -->
    <div class="table-responsive shadow-sm rounded">
        <div class="table-wrapper" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-hover table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center">ID</th>
                        <th>Tên sản phẩm</th>
                        <th>Mô tả</th>
                        <th class="text-center">Giá</th>
                        <th class="text-center">Danh mục</th>
                        <th class="text-center">Ảnh</th>
                        <th class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody id="product-list">
                    <!-- Danh sách sản phẩm -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Phân trang -->
    <div id="pagination" class="mt-4 d-flex justify-content-center"></div>
</div>

<!-- Thêm CSS để cải thiện giao diện -->
<style>
/* Container chính */
.container {
    padding: 3rem 0;
}

/* Tiêu đề trang */
.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #007bff;
    border-bottom: 3px solid #007bff;
    padding-bottom: 0.5rem;
    position: relative;
}

.page-title::after {
    content: '';
    position: absolute;
    bottom: -3px;
    left: 0;
    width: 100px;
    height: 3px;
    background: linear-gradient(90deg, #007bff, #34c759);
}

/* Tiêu đề phần */
.section-title {
    font-size: 1.8rem;
    font-weight: 600;
    color: #343a40;
}

/* Nút thêm sản phẩm và import */
.btn-add-product, .btn-import {
    padding: 0.5rem 1rem;
    border-radius: 5px;
    transition: background-color 0.3s ease, transform 0.3s ease;
}

.btn-add-product {
    background-color: #28a745;
    border: none;
}

.btn-add-product:hover {
    background-color: #218838;
    transform: translateY(-2px);
}

.btn-import {
    border-color: #007bff;
    color: #007bff;
}

.btn-import:hover {
    background-color: #007bff;
    color: #fff;
    transform: translateY(-2px);
}

/* Thanh tìm kiếm */
#searchInput {
    border: 1px solid #ced4da;
    transition: border-color 0.3s ease;
}

#searchInput:focus {
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
    outline: none;
}

#searchBtn {
    border: 1px solid #007bff;
    border-left: none;
    transition: background-color 0.3s ease;
}

#searchBtn:hover {
    background-color: #0056b3;
}

/* Bảng sản phẩm */
.table-responsive {
    border-radius: 10px;
    overflow: hidden;
}

.table-wrapper {
    background-color: #fff;
}

.table {
    margin-bottom: 0;
}

.table th {
    background-color: #343a40;
    color: #fff;
    font-weight: 600;
    padding: 1rem;
}

.table td {
    padding: 0.75rem;
    vertical-align: middle;
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}

/* Cột ảnh */
.table img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 5px;
    transition: transform 0.3s ease;
}

.table img:hover {
    transform: scale(1.1);
}

/* Nút hành động */
.btn-edit-product, .btn-delete-product {
    padding: 0.3rem 0.8rem;
    font-size: 0.9rem;
    border-radius: 5px;
    transition: background-color 0.3s ease, transform 0.3s ease;
}

.btn-edit-product {
    background-color: #ffc107;
    border: none;
}

.btn-edit-product:hover {
    background-color: #e0a800;
    transform: translateY(-2px);
}

.btn-delete-product {
    background-color: #dc3545;
    border: none;
}

.btn-delete-product:hover {
    background-color: #c82333;
    transform: translateY(-2px);
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

/* Responsive */
@media (max-width: 768px) {
    .page-title {
        font-size: 2rem;
    }

    .section-title {
        font-size: 1.5rem;
    }

    .btn-add-product, .btn-import {
        padding: 0.4rem 0.8rem;
        font-size: 0.9rem;
    }
}

@media (max-width: 576px) {
    .page-title {
        font-size: 1.8rem;
    }

    .section-title {
        font-size: 1.3rem;
    }

    #searchInput {
        font-size: 0.9rem;
    }

    #searchBtn {
        padding: 0.4rem 0.8rem;
    }
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const token = <?php echo json_encode($_SESSION['jwtToken'] ?? ''); ?>;
    if (!token) {
        alert('Vui lòng đăng nhập');
        location.href = '/blueskyweb/account/login';
        return;
    }

    let allProducts = [];
    let currentPage = 1;
    const itemsPerPage = 10;

    fetchProducts();

    function fetchProducts() {
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
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error('Lỗi khi gọi API: ' + response.status + ' - ' + text);
                });
            }
            return response.json();
        })
        .then(data => {
            if (!data || !Array.isArray(data)) return;
            allProducts = data;
            currentPage = 1;
            renderPage(allProducts, currentPage);
        })
        .catch(error => {
            console.error("Lỗi khi tải danh sách sản phẩm:", error);
            const productList = document.getElementById('product-list');
            productList.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Lỗi khi tải dữ liệu: ' + error.message + '</td></tr>';
        });
    }

    function renderPage(products, page) {
        const productList = document.getElementById('product-list');
        productList.innerHTML = '';

        const start = (page - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        const pageItems = products.slice(start, end);

        if (pageItems.length === 0) {
            productList.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Không có sản phẩm nào.</td></tr>';
            return;
        }

        pageItems.forEach(product => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="text-center">${product.id}</td>
                <td>${product.name}</td>
                <td>${product.description}</td>
                <td class="text-center">${parseFloat(product.price).toLocaleString()} VND</td>
                <td class="text-center">${product.category_name}</td>
                <td class="text-center"><img src="/blueskyweb/${product.image}" alt="${product.name}" style="width: 50px; height: 50px; object-fit: cover;"></td>
                <td class="text-center">
                    <a href="/blueskyweb/admin/products/edit/${product.id}" class="btn btn-warning btn-sm btn-edit-product">
                        <i class="fas fa-edit me-1"></i> Sửa
                    </a>
                    <button class="btn btn-danger btn-sm btn-delete-product" onclick="deleteProduct(${product.id})">
                        <i class="fas fa-trash-alt me-1"></i> Xóa
                    </button>
                </td>
            `;
            productList.appendChild(row);
        });

        renderPagination(products.length);
    }

    function renderPagination(totalItems) {
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        const pagination = document.getElementById('pagination');
        pagination.innerHTML = '';

        if (totalPages <= 1) return;

        let html = '<ul class="pagination">';

        if (currentPage > 1) {
            html += `<li class="page-item"><a href="#" class="page-link" onclick="goToPage(${currentPage - 1})"><i class="fas fa-chevron-left"></i> Trước</a></li>`;
        }

        for (let i = 1; i <= totalPages; i++) {
            html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a href="#" class="page-link" onclick="goToPage(${i})">${i}</a>
                     </li>`;
        }

        if (currentPage < totalPages) {
            html += `<li class="page-item"><a href="#" class="page-link" onclick="goToPage(${currentPage + 1})">Sau <i class="fas fa-chevron-right"></i></a></li>`;
        }

        html += '</ul>';
        pagination.innerHTML = html;
    }

    window.goToPage = function(page) {
        currentPage = page;
        renderPage(allProducts, currentPage);
        window.scrollTo({ top: 0, behavior: 'smooth' }); // Cuộn lên đầu trang
    }

    window.deleteProduct = function(id) {
        if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
            fetch(`/blueskyweb/api/product/${id}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + token
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.message === 'Sản phẩm đã bị xóa') {
                    alert('Xóa sản phẩm thành công!');
                    fetchProducts();
                } else {
                    alert('Xóa sản phẩm thất bại: ' + data.message);
                }
            })
            .catch(error => console.error("Lỗi khi xóa sản phẩm:", error));
        }
    };

    document.getElementById('searchBtn').addEventListener('click', function () {
        const keyword = document.getElementById('searchInput').value.trim().toLowerCase();
        const filtered = allProducts.filter(p =>
            p.name.toLowerCase().includes(keyword) ||
            p.description.toLowerCase().includes(keyword)
        );
        currentPage = 1;
        renderPage(filtered, currentPage);
    });

    document.getElementById('searchInput').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            document.getElementById('searchBtn').click();
        }
    });
});
</script>

<?php include 'app/views/shares/footer.php'; ?>