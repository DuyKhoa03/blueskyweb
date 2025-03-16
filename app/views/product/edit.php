<?php include 'app/views/shares/header.php'; ?>

<h1>Sửa sản phẩm</h1>

<form id="edit-product-form" enctype="multipart/form-data">
    <input type="hidden" id="id" name="id">
    <div class="form-group">
        <label for="name">Tên sản phẩm:</label>
        <input type="text" id="name" name="name" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="description">Mô tả:</label>
        <textarea id="description" name="description" class="form-control" required></textarea>
    </div>
    <div class="form-group">
        <label for="price">Giá:</label>
        <input type="number" id="price" name="price" class="form-control" step="0.01" required>
    </div>
    <div class="form-group">
        <label for="category_id">Danh mục:</label>
        <select id="category_id" name="category_id" class="form-control" required>
            <!-- Các danh mục sẽ được tải từ API -->
        </select>
    </div>
    <div class="form-group">
        <label>Ảnh hiện tại:</label>
        <br>
        <img id="current-image" src="" alt="Ảnh sản phẩm" style="max-width: 200px; display: none;">
    </div>
    <div class="form-group">
        <label for="image">Chọn ảnh mới:</label>
        <input type="file" id="image" name="image" class="form-control" accept="image/*">
    </div>
    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
</form>

<a href="/blueskyweb/Product/list" class="btn btn-secondary mt-2">Quay lại danh sách sản phẩm</a>

<?php include 'app/views/shares/footer.php'; ?>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const productId = <?= $editId ?>; // Lấy ID sản phẩm cần sửa

    // Lấy danh mục sản phẩm từ API
    fetch('/blueskyweb/api/category')
        .then(response => response.json())
        .then(data => {
            const categorySelect = document.getElementById('category_id');
            data.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                categorySelect.appendChild(option);
            });

            // Lấy dữ liệu sản phẩm sau khi danh mục đã load
            fetch(`/blueskyweb/api/product/${productId}`)
                .then(response => response.json())
                .then(product => {
                    if (product) {
                        document.getElementById('id').value = product.id;
                        document.getElementById('name').value = product.name;
                        document.getElementById('description').value = product.description;
                        document.getElementById('price').value = product.price;
                        document.getElementById('category_id').value = product.category_id;

                        if (product.image) {
                            const imgElement = document.getElementById('current-image');
                            imgElement.src = `/blueskyweb/${product.image}`;
                            imgElement.style.display = "block";
                        }
                    } else {
                        alert("Sản phẩm không tồn tại!");
                    }
                })
                .catch(error => console.error("Lỗi tải sản phẩm:", error));
        })
        .catch(error => console.error("Lỗi tải danh mục:", error));

    // Xử lý sự kiện khi submit form
    document.getElementById('edit-product-form').addEventListener('submit', function (event) {
        event.preventDefault();

        const formData = new FormData(this); // Sử dụng FormData để gửi ảnh

        fetch(`/blueskyweb/api/product/${productId}`, {
            method: 'POST', // API phải hỗ trợ `POST` với `_method=PUT`
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.message === 'Sản phẩm đã được cập nhật thành công') {
                location.href = '/blueskyweb/Product';
            } else {
                alert('Cập nhật sản phẩm thất bại');
            }
        })
        .catch(error => {
            console.error("Lỗi khi gửi yêu cầu:", error);
            alert("Lỗi: Không thể kết nối với máy chủ.");
        });
    });
});
</script>
