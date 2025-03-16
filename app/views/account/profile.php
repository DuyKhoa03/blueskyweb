<?php include 'app/views/shares/header.php'; ?>
<div class="container mt-5">
    <h2>Thông tin tài khoản</h2>
    <div class="card p-4">
        <p><strong>Họ và tên:</strong> <span id="profile-fullname"></span></p>
        <p><strong>Tên đăng nhập:</strong> <span id="profile-username"></span></p>
        <p><strong>Email:</strong> <span id="profile-email"></span></p>
        <p><strong>Số điện thoại:</strong> <span id="profile-phone"></span></p>
        <button class="btn btn-primary" onclick="showEditForm()">Cập nhật thông tin</button>
    </div>

    <div class="card p-4 mt-3" id="edit-form" style="display:none;">
        <h3>Cập nhật thông tin</h3>
        <label>Họ và tên:</label>
        <input type="text" id="edit-fullname" class="form-control">
        
        <label>Username:</label>
        <input type="text" id="edit-username" class="form-control" readonly>
        
        <label>Email:</label>
        <input type="email" id="edit-email" class="form-control">
        
        <label>Số điện thoại:</label>
        <input type="text" id="edit-phone" class="form-control">
        
        <button class="btn btn-success mt-3" onclick="updateUser()">Lưu thay đổi</button>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const token = localStorage.getItem('jwtToken');

    if (!token) {
        alert("Bạn cần đăng nhập để xem trang này!");
        location.href = '/blueskyweb/account/login';
        return;
    }

    fetch('/blueskyweb/account/getUserById', {
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            document.getElementById('profile-fullname').innerText = data.user.fullname;
            document.getElementById('profile-username').innerText = data.user.username;
            document.getElementById('profile-email').innerText = data.user.email;
            document.getElementById('profile-phone').innerText = data.user.phone;

            // Gán dữ liệu vào form cập nhật
            document.getElementById('edit-fullname').value = data.user.fullname;
            document.getElementById('edit-username').value = data.user.username;
            document.getElementById('edit-email').value = data.user.email;
            document.getElementById('edit-phone').value = data.user.phone;
        } else {
            alert("Không lấy được thông tin user!");
        }
    })
    .catch(error => console.error("Lỗi khi lấy user:", error));
});

function showEditForm() {
    document.getElementById("edit-form").style.display = "block";
}

function updateUser() {
    const token = localStorage.getItem('jwtToken');
    const updatedData = {
        fullname: document.getElementById('edit-fullname').value,
        email: document.getElementById('edit-email').value,
        phone: document.getElementById('edit-phone').value
    };

    fetch('/blueskyweb/account/updateUser', {
        method: 'PUT',
        headers: {
            'Authorization': 'Bearer ' + token,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(updatedData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            alert("Cập nhật thành công!");
            location.reload();
        } else {
            alert("Lỗi cập nhật: " + (data.error || "Không xác định"));
        }
    })
    .catch(error => console.error("Lỗi cập nhật:", error));
}
</script>

<?php include 'app/views/shares/footer.php'; ?>
