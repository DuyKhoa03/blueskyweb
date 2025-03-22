<?php include 'app/views/shares/header.php'; ?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">Quản lý Danh mục</h1>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="section-title">Danh sách danh mục</h2>
        <a href="/blueskyweb/admin/categories/add" class="btn btn-success btn-add-category">
            <i class="fas fa-plus mr-1"></i> Thêm danh mục mới
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Tên danh mục</th>
                    <th>Mô tả</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody id="category-list">
                <tr><td colspan="4" class="text-center text-muted empty-message">Đang tải danh mục...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const token = <?php echo json_encode($_SESSION['jwtToken'] ?? ''); ?>;

    // Gọi API để lấy danh sách danh mục
    fetch('/blueskyweb/api/category', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Lỗi khi lấy dữ liệu danh mục: ' + response.statusText);
        }
        return response.json();
    })
    .then(data => {
        const categoryList = document.getElementById('category-list');
        categoryList.innerHTML = ''; // Xóa nội dung mặc định

        if (!Array.isArray(data) || data.length === 0) {
            categoryList.innerHTML = '<tr><td colspan="4" class="text-center text-muted empty-message">Chưa có danh mục nào.</td></tr>';
            return;
        }

        data.forEach(category => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${category.id}</td>
                <td>${category.name || 'Chưa cập nhật'}</td>
                <td>${category.description || 'Chưa cập nhật'}</td>
                <td>
                    <a href="/blueskyweb/admin/categories/edit/${category.id}" class="btn btn-warning btn-sm btn-action">
                        <i class="fas fa-edit mr-1"></i> Sửa
                    </a>
                    <button class="btn btn-danger btn-sm btn-action" onclick="deleteCategory(${category.id})">
                        <i class="fas fa-trash-alt mr-1"></i> Xóa
                    </button>
                </td>
            `;
            categoryList.appendChild(row);
        });
    })
    .catch(error => {
        console.error("Lỗi khi tải danh sách danh mục:", error);
        const categoryList = document.getElementById('category-list');
        categoryList.innerHTML = '<tr><td colspan="4" class="text-center text-muted empty-message">Lỗi khi tải danh mục. Vui lòng thử lại sau.</td></tr>';
    });

    // Hàm xóa danh mục
    window.deleteCategory = function(id) {
        if (confirm('Bạn có chắc chắn muốn xóa danh mục này?')) {
            fetch(`/blueskyweb/api/category/${id}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + token
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.message === 'Category deleted successfully') {
                    alert('Xóa danh mục thành công!');
                    location.reload();
                } else {
                    alert('Xóa danh mục thất bại: ' + data.message);
                }
            })
            .catch(error => console.error("Lỗi khi xóa danh mục:", error));
        }
    };
});
</script>

<style>
    /* Tùy chỉnh tiêu đề chính */
    .page-title {
        font-size: 2.5rem;
        font-weight: bold;
        color: #007bff;
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }

    /* Tùy chỉnh tiêu đề phụ */
    .section-title {
        font-size: 1.5rem;
        font-weight: bold;
        color: #333;
    }

    /* Tùy chỉnh nút "Thêm danh mục mới" */
    .btn-add-category {
        background-color: #28a745;
        border: none;
        padding: 10px 20px;
        font-weight: bold;
        transition: all 0.3s ease;
    }

    .btn-add-category:hover {
        background-color: #218838;
        transform: translateY(-2px);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    /* Tùy chỉnh bảng */
    .table {
        background-color: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .table th {
        background-color: #343a40;
        color: #fff;
        font-weight: bold;
        text-align: center;
    }

    .table td {
        vertical-align: middle;
        text-align: center;
    }

    /* Tùy chỉnh nút hành động */
    .btn-action {
        padding: 5px 10px;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .btn-warning {
        background-color: #ffc107;
        border: none;
    }

    .btn-warning:hover {
        background-color: #e0a800;
        transform: translateY(-2px);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    .btn-danger {
        background-color: #dc3545;
        border: none;
    }

    .btn-danger:hover {
        background-color: #c82333;
        transform: translateY(-2px);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    /* Tùy chỉnh thông báo trống */
    .empty-message {
        font-size: 1.2rem;
        color: #6c757d;
        padding: 20px;
        background-color: #f8f9fa;
        border-radius: 5px;
        margin: 10px 0;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-title {
            font-size: 2rem;
        }

        .section-title {
            font-size: 1.2rem;
        }

        .table th, .table td {
            font-size: 0.9rem;
        }

        .btn-action {
            font-size: 0.8rem;
            padding: 4px 8px;
        }

        .empty-message {
            font-size: 1rem;
            padding: 15px;
        }
    }

    @media (max-width: 576px) {
        .table th, .table td {
            font-size: 0.8rem;
        }

        .btn-action {
            font-size: 0.7rem;
            padding: 3px 6px;
        }

        .btn-add-category {
            padding: 8px 15px;
            font-size: 0.9rem;
        }
    }
</style>

<?php include 'app/views/shares/footer.php'; ?>