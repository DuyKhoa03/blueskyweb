<?php 
include 'app/views/shares/header.php'; 
// Khởi động session nếu chưa có
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Lấy token từ session (nếu có)
$token = $_SESSION['jwtToken'] ?? null;
?>

<h1>Danh sách danh mục</h1>
<a href="/blueskyweb/Category/add" class="btn btn-success mb-2">Thêm danh mục mới</a>
<ul class="list-group" id="category-list">
    <!-- Danh sách danh mục sẽ được tải từ API và hiển thị tại đây -->
</ul>

<?php include 'app/views/shares/footer.php'; ?>

<script>
    // Chèn token từ PHP vào JavaScript
    const token = <?php echo json_encode($token); ?>;

    document.addEventListener("DOMContentLoaded", function () {
        if (!token) {
            alert('Vui lòng đăng nhập');
            location.href = '/blueskyweb/account/login';
            return;
        }

        fetch('/blueskyweb/api/category', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            }
        })
        .then(response => {
            if (response.status === 401) {
                alert('Phiên đăng nhập không hợp lệ, vui lòng đăng nhập lại!');
                location.href = '/blueskyweb/account/login';
                return;
            }
            return response.json();
        })
        .then(data => {
            if (!data) return; // Nếu đã điều hướng thì không xử lý tiếp
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
        })
        .catch(error => console.error("Lỗi khi tải danh sách danh mục:", error));
    });

    function deleteCategory(id) {
        if (confirm('Bạn có chắc chắn muốn xóa danh mục này?')) {
            fetch(`/blueskyweb/api/category/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + token
                }
            })
            .then(response => {
                if (response.status === 401) {
                    alert('Phiên đăng nhập không hợp lệ, vui lòng đăng nhập lại!');
                    location.href = '/blueskyweb/account/login';
                    return;
                }
                return response.json();
            })
            .then(data => {
                if (!data) return; // Nếu đã điều hướng thì không xử lý tiếp
                if (data.message === 'Category deleted successfully') {
                    alert('Xóa danh mục thành công');
                    location.reload();
                } else {
                    alert('Xóa danh mục thất bại: ' + (data.message || 'Lỗi không xác định'));
                }
            })
            .catch(error => {
                console.error("Lỗi khi xóa danh mục:", error);
                alert('Lỗi hệ thống khi xóa danh mục!');
            });
        }
    }
</script>