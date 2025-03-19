<?php include 'app/views/shares/header.php'; ?>

<h1>Thêm sản phẩm mới</h1>

<form id="add-product-form" enctype="multipart/form-data">
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
            <!-- Các danh mục sẽ được tải từ API và hiển thị tại đây -->
        </select>
    </div>
    <div class="form-group">
        <label for="image">Chọn ảnh sản phẩm:</label>
        <input type="file" id="image" name="image" class="form-control" accept="image/*">
    </div>
    <button type="submit" class="btn btn-primary">Thêm sản phẩm</button>
</form>

<a href="/blueskyweb/Product" class="btn btn-secondary mt-2">Quay lại danh sách sản phẩm</a>

<?php include 'app/views/shares/footer.php'; ?>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Tải danh sách danh mục từ API
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
        })
        .catch(error => console.error('Lỗi tải danh mục:', error));

    // Xử lý sự kiện khi submit form
    document.getElementById('add-product-form').addEventListener('submit', function (event) {
        event.preventDefault(); 

        const formData = new FormData(this); // Tạo FormData để gửi file ảnh
        let category_id = document.getElementById('category_id').value.trim();
    if (!category_id || isNaN(category_id)) {
        alert('Vui lòng chọn danh mục hợp lệ!');
        return;
    }
    formData.set('category_id', category_id.toString()); // Đảm bảo là chuỗi số
        for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]); // In từng field gửi lên
    }
        fetch('/blueskyweb/api/product', {
            method: 'POST',
            body: formData // Gửi dữ liệu dưới dạng multipart/form-data
            
        })
        .then(response => response.json())
        .then(data => {
            console.log('Raw response:', data);
            if (data.message === 'Sản phẩm đã được tạo thành công') {
                location.href = '/blueskyweb/Product';
            } else {
                alert('Thêm sản phẩm thất bại');
            }
        })
        .catch(error => {
            console.error('Lỗi khi gửi yêu cầu:', error);
            alert('Lỗi: Không thể kết nối với máy chủ.');
        });
    });
});
</script>
