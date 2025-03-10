<?php include 'app/views/shares/header.php'; ?>

<h1>Thêm danh mục mới</h1>

<form id="add-category-form">
    <div class="mb-3">
        <label for="name" class="form-label">Tên danh mục</label>
        <input type="text" class="form-control" id="name" name="name" required>
    </div>
    <div class="mb-3">
        <label for="description" class="form-label">Mô tả</label>
        <textarea class="form-control" id="description" name="description"></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Thêm</button>
    <a href="/blueskyweb/Category" class="btn btn-secondary">Quay lại</a>
</form>

<?php include 'app/views/shares/footer.php'; ?>

<script>
    document.getElementById("add-category-form").addEventListener("submit", function(event) {
        event.preventDefault();

        const formData = {
            name: document.getElementById("name").value,
            description: document.getElementById("description").value
        };

        fetch('/blueskyweb/api/category', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.message === 'Category created successfully') {
                alert('Thêm danh mục thành công!');
                window.location.href = "/blueskyweb/Category";
            } else {
                alert('Thêm danh mục thất bại!');
            }
        });
    });
</script>
