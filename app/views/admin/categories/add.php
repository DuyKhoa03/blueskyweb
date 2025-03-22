<?php include 'app/views/shares/header.php'; ?>

<div class="container">
    <h1 class="page-title">Thêm Danh mục Mới</h1>
    <form id="add-category-form">
        <div class="form-group">
            <label for="name">Tên danh mục</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="description">Mô tả</label>
            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Thêm danh mục</button>
        <a href="/blueskyweb/admin/categories" class="btn btn-secondary">Quay lại</a>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const token = <?php echo json_encode($_SESSION['jwtToken'] ?? ''); ?>;

    // Xử lý form thêm danh mục
    document.getElementById('add-category-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const data = {
            name: document.getElementById('name').value,
            description: document.getElementById('description').value
        };

        fetch('/blueskyweb/api/category', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.message === 'Category created successfully') {
                alert('Thêm danh mục thành công!');
                location.href = '/blueskyweb/admin/categories';
            } else {
                alert('Thêm danh mục thất bại: ' + (data.message || 'Lỗi không xác định'));
            }
        })
        .catch(error => console.error("Lỗi khi thêm danh mục:", error));
    });
});
</script>

<?php include 'app/views/shares/footer.php'; ?>