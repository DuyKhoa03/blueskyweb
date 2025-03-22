<?php include 'app/views/shares/header.php'; ?>

<div class="container">
    <h1 class="page-title">Chỉnh sửa Sản phẩm</h1>
    <form id="edit-product-form" enctype="multipart/form-data">
        <input type="hidden" id="product-id" name="id" value="<?php echo $editId; ?>">
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
            <input type="file" class="form-control-file" id="image" name="image" accept="image/*">
            <input type="hidden" id="current_image" name="current_image">
            <img id="preview-image" src="" alt="Ảnh sản phẩm" style="width: 100px; height: 100px; object-fit: cover; margin-top: 10px;">
        </div>
        <button type="submit" class="btn btn-primary">Cập nhật</button>
        <a href="/blueskyweb/admin/products" class="btn btn-secondary">Quay lại</a>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const token = <?php echo json_encode($_SESSION['jwtToken'] ?? ''); ?>;
    const productId = document.getElementById('product-id').value;

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

    // Lấy thông tin sản phẩm
    fetch(`/blueskyweb/api/product/${productId}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + token
        }
    })
    .then(response => response.json())
    .then(product => {
        if (product) {
            document.getElementById('name').value = product.name;
            document.getElementById('description').value = product.description;
            document.getElementById('price').value = product.price;
            document.getElementById('category_id').value = product.category_id;
            document.getElementById('current_image').value = product.image;
            document.getElementById('preview-image').src = "/blueskyweb/" + product.image;
        } else {
            alert('Không tìm thấy sản phẩm!');
            location.href = '/blueskyweb/admin/products';
        }
    })
    .catch(error => console.error("Lỗi khi tải thông tin sản phẩm:", error));

    // Xử lý form cập nhật
    document.getElementById('edit-product-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('_method', 'PUT');

        fetch(`/blueskyweb/api/product/${productId}`, {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.message === 'Sản phẩm đã được cập nhật') {
                alert('Cập nhật sản phẩm thành công!');
                location.href = '/blueskyweb/admin/products';
            } else {
                alert('Cập nhật sản phẩm thất bại: ' + (data.message || 'Lỗi không xác định'));
            }
        })
        .catch(error => console.error("Lỗi khi cập nhật sản phẩm:", error));
    });
});
</script>

<?php include 'app/views/shares/footer.php'; ?>