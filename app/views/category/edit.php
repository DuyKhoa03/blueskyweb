<?php include 'app/views/shares/header.php'; ?>

<h1>Sửa danh mục</h1>

<?php if (!empty($category)): ?>
    <form id="edit-category-form">
        <input type="hidden" id="id" name="id" value="<?= htmlspecialchars($category->id) ?>">

        <div class="form-group">
            <label for="name">Tên danh mục:</label>
            <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($category->name) ?>" required>
        </div>

        <div class="form-group">
            <label for="description">Mô tả:</label>
            <textarea id="description" name="description" class="form-control" required><?= htmlspecialchars($category->description) ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
    </form>
<?php else: ?>
    <p class="text-danger">Danh mục không tồn tại hoặc có lỗi khi tải dữ liệu.</p>
<?php endif; ?>

<a href="/blueskyweb/Category" class="btn btn-secondary mt-2">Quay lại danh sách danh mục</a>

<?php include 'app/views/shares/footer.php'; ?>

<script>
    document.getElementById('edit-category-form').addEventListener('submit', function (event) {
        event.preventDefault();

        const formData = new FormData(this);
        const jsonData = {};
        formData.forEach((value, key) => {
            jsonData[key] = value;
        });

        fetch(`/blueskyweb/api/category/${jsonData.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(jsonData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.message === 'Category updated successfully') {
                alert('Cập nhật danh mục thành công');
                location.href = '/blueskyweb/Category';
            } else {
                alert('Cập nhật danh mục thất bại');
            }
        })
        .catch(error => console.error("Lỗi khi cập nhật danh mục:", error));
    });
</script>
