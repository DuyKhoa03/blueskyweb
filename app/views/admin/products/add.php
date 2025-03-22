<?php include 'app/views/shares/header.php'; ?>

<div class="container">
    <h1 class="page-title">Thêm Sản phẩm Mới</h1>
    <form id="add-product-form" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Tên sản phẩm</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="description">Mô tả</label>
            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
        </div>
        <div class="form-group">
            <label for="price">Giá</label>
            <input type="number" class="form-control" id="price" name="price" required>
        </div>
        <div class="form-group">
            <label for="category_id">Danh mục</label>
            <select class="form-control" id="category_id" name="category_id" required>
                <option value="">Chọn danh mục</option>
            </select>
        </div>
        <div class="form-group">
            <label for="image">Ảnh sản phẩm</label>
            <input type="file" class="form-control-file" id="image" name="image" accept="image/*" required>
        </div>
        <button type="submit" class="btn btn-primary">Thêm sản phẩm</button>
        <a href="/blueskyweb/admin/products" class="btn btn-secondary">Quay lại</a>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const token = <?php echo json_encode($_SESSION['jwtToken'] ?? ''); ?>;

    // Lấy danh sách danh mục
    fetch('/blueskyweb/api/category', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const categorySelect = document.getElementById('category_id');
        data.forEach(category => {
            const option = document.createElement('option');
            option.value = category.id;
            option.textContent = category.name;
            categorySelect.appendChild(option);
        });
    })
    .catch(error => console.error("Lỗi khi tải danh mục:", error));

    // Xử lý form thêm sản phẩm
    document.getElementById('add-product-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('/blueskyweb/api/product', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.message === 'Sản phẩm đã được tạo thành công') {
                alert('Thêm sản phẩm thành công!');
                location.href = '/blueskyweb/admin/products';
            } else {
                alert('Thêm sản phẩm thất bại: ' + (data.message || 'Lỗi không xác định'));
            }
        })
        .catch(error => console.error("Lỗi khi thêm sản phẩm:", error));
    });
});
</script>

<?php include 'app/views/shares/footer.php'; ?>