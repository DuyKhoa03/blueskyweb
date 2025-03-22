<?php include 'app/views/shares/header.php'; ?>

<div class="container">
    <h1 class="page-title">Quản lý Sản phẩm</h1>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Danh sách sản phẩm</h2>
        <div>
            <a href="/blueskyweb/admin/products/add" class="btn btn-success btn-add-product me-2">
                <i class="fas fa-plus mr-1"></i> Thêm sản phẩm mới
            </a>
            <a href="/blueskyweb/admin/products/import" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-file-import me-1"></i> Import Excel
            </a>
        </div>
    </div>

    <!-- Tìm kiếm -->
    <div class="mb-3 d-flex" style="max-width: 400px;">
        <input type="text" id="searchInput" class="form-control me-2" placeholder="Tìm theo tên sản phẩm...">
        <button id="searchBtn" class="btn btn-primary">Tìm</button>
    </div>

    <!-- Scroll wrapper -->
    <div class="table-responsive">
        <div class="table-wrapper" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Tên sản phẩm</th>
                        <th>Mô tả</th>
                        <th>Giá</th>
                        <th>Danh mục</th>
                        <th>Ảnh</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody id="product-list">
                    <!-- Danh sách sản phẩm -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Phân trang -->
    <div id="pagination" class="mt-3 d-flex justify-content-center"></div>
</div>

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
            productList.innerHTML = '<tr><td colspan="7" class="text-center">Lỗi khi tải dữ liệu: ' + error.message + '</td></tr>';
        });
    }

    function renderPage(products, page) {
        const productList = document.getElementById('product-list');
        productList.innerHTML = '';

        const start = (page - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        const pageItems = products.slice(start, end);

        if (pageItems.length === 0) {
            productList.innerHTML = '<tr><td colspan="7" class="text-center">Không có sản phẩm nào.</td></tr>';
            return;
        }

        pageItems.forEach(product => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${product.id}</td>
                <td>${product.name}</td>
                <td>${product.description}</td>
                <td>${parseFloat(product.price).toLocaleString()} VND</td>
                <td>${product.category_name}</td>
                <td><img src="/blueskyweb/${product.image}" alt="${product.name}" style="width: 50px; height: 50px; object-fit: cover;"></td>
                <td>
                    <a href="/blueskyweb/admin/products/edit/${product.id}" class="btn btn-warning btn-sm btn-edit-product">
                        <i class="fas fa-edit mr-1"></i> Sửa
                    </a>
                    <button class="btn btn-danger btn-sm btn-delete-product" onclick="deleteProduct(${product.id})">
                        <i class="fas fa-trash-alt mr-1"></i> Xóa
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
            html += `<li class="page-item"><a href="#" class="page-link" onclick="goToPage(${currentPage - 1})">Trước</a></li>`;
        }

        for (let i = 1; i <= totalPages; i++) {
            html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a href="#" class="page-link" onclick="goToPage(${i})">${i}</a>
                     </li>`;
        }

        if (currentPage < totalPages) {
            html += `<li class="page-item"><a href="#" class="page-link" onclick="goToPage(${currentPage + 1})">Sau</a></li>`;
        }

        html += '</ul>';
        pagination.innerHTML = html;
    }

    window.goToPage = function(page) {
        currentPage = page;
        renderPage(allProducts, currentPage);
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