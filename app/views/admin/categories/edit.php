<?php include 'app/views/shares/header.php'; ?>

<div class="container">
    <h1 class="page-title">Chỉnh sửa Danh mục</h1>
    <form id="edit-category-form">
        <input type="hidden" id="category-id" value="<?php echo $category->id; ?>">
        <div class="form-group">
            <label for="name">Tên danh mục</label>
            <input type="text" class="form-control" id="name" value="<?php echo $category->name; ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Mô tả</label>
            <textarea class="form-control" id="description" rows="3" required><?php echo $category->description; ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Cập nhật</button>
        <a href="/blueskyweb/admin/categories" class="btn btn-secondary">Quay lại</a>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const token = <?php echo json_encode($_SESSION['jwtToken'] ?? ''); ?>;
    const categoryId = document.getElementById('category-id').value;

    // Xử lý form cập nhật
    document.getElementById('edit-category-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const data = {
            name: document.getElementById('name').value,
            description: document.getElementById('description').value
        };

        fetch(`/blueskyweb/api/category/${categoryId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.message === 'Category updated successfully') {
                alert('Cập nhật danh mục thành công!');
                location.href = '/blueskyweb/admin/categories';
            } else {
                alert('Cập nhật danh mục thất bại: ' + (data.message || 'Lỗi không xác định'));
            }
        })
        .catch(error => console.error("Lỗi khi cập nhật danh mục:", error));
    });
});
</script>

<?php include 'app/views/shares/footer.php'; ?>