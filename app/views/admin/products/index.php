<?php include 'app/views/shares/header.php'; ?>

<div class="container">
    <h1 class="page-title">Quản lý Sản phẩm</h1>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Danh sách sản phẩm</h2>
        <a href="/blueskyweb/admin/products/add" class="btn btn-success btn-add-product">
            <i class="fas fa-plus mr-1"></i> Thêm sản phẩm mới
        </a>
    </div>
    <div class="table-responsive">
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
                <!-- Danh sách sản phẩm sẽ được tải bằng JavaScript -->
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const token = <?php echo json_encode($_SESSION['jwtToken'] ?? ''); ?>;
    if (!token) {
        alert('Vui lòng đăng nhập');
        location.href = '/blueskyweb/account/login';
        return;
    }

    // Gọi API để lấy danh sách sản phẩm
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
        if (!data) return;
        const productList = document.getElementById('product-list');
        productList.innerHTML = ''; // Xóa danh sách cũ

        if (Array.isArray(data)) {
            data.forEach(product => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${product.id}</td>
                    <td>${product.name}</td>
                    <td>${product.description}</td>
                    <td>${product.price.toLocaleString()} VND</td>
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
        } else {
            productList.innerHTML = '<tr><td colspan="7" class="text-center">Không có sản phẩm nào.</td></tr>';
        }
    })
    .catch(error => {
        console.error("Lỗi khi tải danh sách sản phẩm:", error);
        const productList = document.getElementById('product-list');
        productList.innerHTML = '<tr><td colspan="7" class="text-center">Lỗi khi tải dữ liệu: ' + error.message + '</td></tr>';
    });

    // Hàm xóa sản phẩm
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
                    location.reload();
                } else {
                    alert('Xóa sản phẩm thất bại: ' + data.message);
                }
            })
            .catch(error => console.error("Lỗi khi xóa sản phẩm:", error));
        }
    };

    // Kiểm tra hành vi của nút "Thêm" và "Sửa"
    document.querySelectorAll('.btn-add-product, .btn-edit-product').forEach(button => {
        button.addEventListener('click', function(e) {
            console.log('Navigating to:', this.href);
            // Đảm bảo không chặn sự kiện mặc định
        });
    });
});
</script>

<?php include 'app/views/shares/footer.php'; ?>