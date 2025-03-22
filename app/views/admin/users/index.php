<?php include 'app/views/shares/header.php'; ?>

<div class="container">
    <h1 class="page-title">Quản lý Người dùng</h1>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Họ và Tên</th>
                    <th>Số điện thoại</th>
                    <th>Role</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody id="user-list">
                <!-- Danh sách người dùng sẽ được tải bằng JavaScript -->
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const token = <?php echo json_encode($_SESSION['jwtToken'] ?? ''); ?>;
    if (!token) {
        alert('Vui lòng đăng nhập');
        location.href = '/blueskyweb/account/login';
        return;
    }

    // Gọi API để lấy danh sách người dùng
    fetch('/blueskyweb/api/user', {
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
            return Promise.reject('Unauthorized');
        }
        if (!response.ok) {
            throw new Error('Lỗi khi gọi API: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        const userList = document.getElementById('user-list');
        userList.innerHTML = ''; // Xóa danh sách cũ

        // Kiểm tra nếu data là mảng
        if (Array.isArray(data)) {
            data.forEach(user => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${user.id}</td>
                    <td>${user.username}</td>
                    <td>${user.email}</td>
                    <td>${user.fullname}</td>
                    <td>${user.phone}</td>
                    <td>${user.role}</td>
                    <td>
                        <a href="/blueskyweb/admin/users/edit/${user.id}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Sửa
                        </a>
                    </td>
                `;
                userList.appendChild(row);
            });
        } else {
            // Hiển thị thông báo nếu không có dữ liệu
            userList.innerHTML = '<tr><td colspan="7" class="text-center">Không có người dùng nào.</td></tr>';
        }
    })
    .catch(error => {
        console.error("Lỗi khi tải danh sách người dùng:", error);
        const userList = document.getElementById('user-list');
        userList.innerHTML = '<tr><td colspan="7" class="text-center">Lỗi khi tải dữ liệu: ' + error.message + '</td></tr>';
    });
});
</script>

<?php include 'app/views/shares/footer.php'; ?>