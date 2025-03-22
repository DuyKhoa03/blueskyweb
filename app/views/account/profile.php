<?php
// Khởi động session nếu chưa có
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Lấy token từ session (nếu có)
$token = $_SESSION['jwtToken'] ?? null;
require_once 'app/utils/JWTHandler.php'; // Để giải mã token
$jwtHandler = new JWTHandler();
$username = null;
$userid = null;
if ($token) {
    try {
        $tokenData = $jwtHandler->decode($token);
        $username = $tokenData['username'] ?? 'Không xác định';
        $userid = $tokenData['id'] ?? null;
    } catch (Exception $e) {
        unset($_SESSION['jwtToken']); // Xóa token nếu không hợp lệ
    }
}
?>
<?php include 'app/views/shares/header.php'; ?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">Thông tin tài khoản</h1>
    </div>

    <!-- Thông tin tài khoản -->
    <div class="card p-4 mb-4">
        <div class="profile-info">
            <ul class="list-unstyled">
                <li class="mb-3">
                    <i class="fas fa-user mr-2 text-primary"></i>
                    <strong>Họ và tên:</strong> <span id="profile-fullname"></span>
                </li>
                <li class="mb-3">
                    <i class="fas fa-id-badge mr-2 text-primary"></i>
                    <strong>Tên đăng nhập:</strong> <span id="profile-username"></span>
                </li>
                <li class="mb-3">
                    <i class="fas fa-envelope mr-2 text-primary"></i>
                    <strong>Email:</strong> <span id="profile-email"></span>
                </li>
                <li class="mb-3">
                    <i class="fas fa-phone mr-2 text-primary"></i>
                    <strong>Số điện thoại:</strong> <span id="profile-phone"></span>
                </li>
            </ul>
        </div>
        <button class="btn btn-primary" onclick="showEditForm()">Cập nhật thông tin</button>
    </div>

    <!-- Form cập nhật thông tin -->
    <div class="card p-4" id="edit-form" style="display:none;">
        <h3 class="mb-3">Cập nhật thông tin</h3>
        <!-- Thông báo lỗi -->
        <div id="error-message" class="alert alert-danger" style="display: none;"></div>
        <div class="form-group">
            <label for="edit-fullname">Họ và tên:</label>
            <input type="text" id="edit-fullname" class="form-control" placeholder="Nhập họ và tên">
        </div>
        <div class="form-group">
            <label for="edit-username">Tên đăng nhập:</label>
            <input type="text" id="edit-username" class="form-control" readonly>
        </div>
        <div class="form-group">
            <label for="edit-email">Email:</label>
            <input type="email" id="edit-email" class="form-control" placeholder="Nhập email">
        </div>
        <div class="form-group">
            <label for="edit-phone">Số điện thoại:</label>
            <input type="text" id="edit-phone" class="form-control" placeholder="Nhập số điện thoại">
        </div>
        <button class="btn btn-success btn-block mt-3" onclick="updateUser()">Lưu thay đổi</button>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const token = <?php echo json_encode($token); ?>;

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
            document.getElementById('profile-fullname').innerText = data.user.fullname || 'Chưa cập nhật';
            document.getElementById('profile-username').innerText = data.user.username || 'Chưa cập nhật';
            document.getElementById('profile-email').innerText = data.user.email || 'Chưa cập nhật';
            document.getElementById('profile-phone').innerText = data.user.phone || 'Chưa cập nhật';

            // Gán dữ liệu vào form cập nhật
            document.getElementById('edit-fullname').value = data.user.fullname || '';
            document.getElementById('edit-username').value = data.user.username || '';
            document.getElementById('edit-email').value = data.user.email || '';
            document.getElementById('edit-phone').value = data.user.phone || '';
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
    const token = <?php echo json_encode($token); ?>;
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
            showErrorMessage(data.error || 'Lỗi cập nhật!');
        }
    })
    .catch(error => {
        console.error("Lỗi cập nhật:", error);
        showErrorMessage('Có lỗi xảy ra khi cập nhật. Vui lòng thử lại sau.');
    });
}

// Hàm hiển thị thông báo lỗi
function showErrorMessage(message) {
    const errorMessageElement = document.getElementById("error-message");
    errorMessageElement.innerText = message;
    errorMessageElement.style.display = "block"; // Hiển thị thông báo lỗi
}
</script>

<style>
    /* Tùy chỉnh tiêu đề */
    .page-title {
        font-size: 2.5rem;
        font-weight: bold;
        color: #007bff;
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }

    /* Tùy chỉnh card */
    .card {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        border: none;
    }

    .card h3 {
        font-size: 1.5rem;
        color: #333;
    }

    /* Tùy chỉnh thông tin tài khoản */
    .profile-info ul li {
        display: flex;
        align-items: center;
        font-size: 1.1rem;
    }

    .profile-info ul li i {
        color: #007bff;
        margin-right: 10px;
    }

    .profile-info ul li strong {
        color: #333;
        min-width: 120px;
    }

    .profile-info ul li span {
        color: #6c757d;
    }

    /* Tùy chỉnh form cập nhật */
    .form-group label {
        font-weight: bold;
        color: #333;
    }

    .form-control {
        border-radius: 5px;
        border: 1px solid #ced4da;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
    }

    .form-control[readonly] {
        background-color: #e9ecef;
        cursor: not-allowed;
    }

    /* Tùy chỉnh thông báo lỗi */
    .alert-danger {
        font-size: 0.9rem;
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 5px;
    }

    /* Tùy chỉnh nút */
    .btn-primary {
        background-color: #007bff;
        border: none;
        padding: 10px 20px;
        font-weight: bold;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #0056b3;
        transform: translateY(-2px);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    .btn-success {
        background-color: #28a745;
        border: none;
        padding: 10px 20px;
        font-weight: bold;
        transition: all 0.3s ease;
    }

    .btn-success:hover {
        background-color: #218838;
        transform: translateY(-2px);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-title {
            font-size: 2rem;
        }

        .card h3 {
            font-size: 1.2rem;
        }

        .profile-info ul li {
            font-size: 1rem;
        }

        .profile-info ul li strong {
            min-width: 100px;
        }
    }

    @media (max-width: 576px) {
        .profile-info ul li {
            font-size: 0.9rem;
            flex-direction: column;
            align-items: flex-start;
        }

        .profile-info ul li strong {
            min-width: auto;
            margin-bottom: 5px;
        }
    }
</style>

<?php include 'app/views/shares/footer.php'; ?>