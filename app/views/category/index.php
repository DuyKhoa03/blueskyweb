<?php include 'app/views/shares/header.php'; ?>

<h1>Danh sách danh mục</h1>
<a href="/blueskyweb/Category/add" class="btn btn-success mb-2">Thêm danh mục mới</a>
<ul class="list-group" id="category-list">
    <!-- Danh sách danh mục sẽ được tải từ API và hiển thị tại đây -->
</ul>

<?php include 'app/views/shares/footer.php'; ?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const token = localStorage.getItem('jwtToken');
        if (!token) {
            alert('Vui lòng đăng nhập');
            location.href = '/blueskyweb/account/login'; // Điều hướng đến trang đăng nhập 
            return;
        }
        fetch('/blueskyweb/api/category', {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            const categoryList = document.getElementById('category-list');
            categoryList.innerHTML = ''; // Xóa dữ liệu cũ nếu có
            data.forEach(category => {
                const categoryItem = document.createElement('li');
                categoryItem.className = 'list-group-item';
                categoryItem.innerHTML = ` 
                    <h2>${category.name}</h2> 
                    <p>${category.description}</p> 
                    <a href="/blueskyweb/Category/edit/${category.id}" class="btn btn-warning">Sửa</a> 
                    <button class="btn btn-danger" onclick="deleteCategory(${category.id})">Xóa</button> 
                `;
                categoryList.appendChild(categoryItem);
            });
        });
    });

    function deleteCategory(id) {
        if (confirm('Bạn có chắc chắn muốn xóa danh mục này?')) {
            fetch(`/blueskyweb/api/category/${id}`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.message === 'Category deleted successfully') {
                    alert('Xóa danh mục thành công');
                    location.reload();
                } else {
                    alert('Xóa danh mục thất bại');
                }
            });
        }
    }
</script>
