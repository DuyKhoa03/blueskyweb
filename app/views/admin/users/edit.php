<?php include 'app/views/shares/header.php'; ?>

<div class="container">
    <h1 class="page-title">Chỉnh sửa Người dùng</h1>
    <form id="edit-user-form">
        <input type="hidden" id="user-id" value="<?php echo $userId; ?>">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" id="username" disabled>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" required>
        </div>
        <div class="form-group">
            <label for="fullname">Họ và Tên</label>
            <input type="text" class="form-control" id="fullname" required>
        </div>
        <div class="form-group">
            <label for="phone">Số điện thoại</label>
            <input type="text" class="form-control" id="phone" required>
        </div>
        <button type="submit" class="btn btn-primary">Cập nhật</button>
        <a href="/blueskyweb/admin/users" class="btn btn-secondary">Quay lại</a>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const token = <?php echo json_encode($_SESSION['jwtToken'] ?? ''); ?>;
    const userId = document.getElementById('user-id').value;

    // Lấy thông tin người dùng
    fetch(`/blueskyweb/api/user/${userId}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + token
        }
    })
    .then(response => response.json())
    .then(user => {
        if (user) {
            document.getElementById('username').value = user.username;
            document.getElementById('email').value = user.email;
            document.getElementById('fullname').value = user.fullname;
            document.getElementById('phone').value = user.phone;
        } else {
            alert('Không tìm thấy người dùng!');
            location.href = '/blueskyweb/admin/users';
        }
    })
    .catch(error => console.error("Lỗi khi tải thông tin người dùng:", error));

    // Xử lý form cập nhật
    document.getElementById('edit-user-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const data = {
            email: document.getElementById('email').value,
            fullname: document.getElementById('fullname').value,
            phone: document.getElementById('phone').value
        };

        fetch(`/blueskyweb/api/user/${userId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.message === 'User updated successfully') {
                alert('Cập nhật người dùng thành công!');
                location.href = '/blueskyweb/admin/users';
            } else {
                alert('Cập nhật thất bại: ' + (data.error || 'Lỗi không xác định'));
            }
        })
        .catch(error => console.error("Lỗi khi cập nhật người dùng:", error));
    });
});
</script>

<?php include 'app/views/shares/footer.php'; ?>