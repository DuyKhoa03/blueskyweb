<?php include 'app/views/shares/header.php'; ?>

<section class="gradient-custom">
    <div class="container py-5">
        <div class="row d-flex justify-content-center align-items-center">
            <div class="col-12 col-md-10 col-lg-8 col-xl-6">
                <div class="card shadow-lg" style="border-radius: 1rem;">
                    <div class="card-body p-5 p-md-5">
                        <h2 class="page-title mb-2 text-center">Đăng ký tài khoản</h2>
                        <p class="text-muted mb-5 text-center">Vui lòng điền thông tin để tạo tài khoản mới!</p>

                        <div id="error-messages" class="text-danger text-center mb-4"></div>

                        <form id="register-form">
                            <div class="row mb-4">
                                <div class="col-md-6 form-group">
                                    <label for="username">Tên đăng nhập:</label>
                                    <input type="text" class="form-control form-control-lg" id="username" name="username" placeholder="Nhập tên đăng nhập" required>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="fullname">Họ và tên:</label>
                                    <input type="text" class="form-control form-control-lg" id="fullname" name="fullname" placeholder="Nhập họ và tên" required>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-6 form-group">
                                    <label for="email">Email:</label>
                                    <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder="Nhập email" required>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="phone">Số điện thoại:</label>
                                    <input type="tel" class="form-control form-control-lg" id="phone" name="phone" placeholder="Nhập số điện thoại" required>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-6 form-group">
                                    <label for="password">Mật khẩu:</label>
                                    <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="Nhập mật khẩu" required>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="confirmpassword">Xác nhận mật khẩu:</label>
                                    <input type="password" class="form-control form-control-lg" id="confirmpassword" name="confirmpassword" placeholder="Nhập lại mật khẩu" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg btn-block">Đăng ký</button>
                        </form>

                        <div class="text-center mt-4">
                            <p class="mb-0">Đã có tài khoản? 
                                <a href="/blueskyweb/account/login" class="text-primary fw-bold signup-link">Đăng nhập ngay</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'app/views/shares/footer.php'; ?>

<script>
document.getElementById('register-form').addEventListener('submit', function (event) {
    event.preventDefault();

    const formData = new FormData(this);
    const jsonData = {};
    formData.forEach((value, key) => {
        jsonData[key] = value;
    });

    fetch('/blueskyweb/account/save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(jsonData)
    })
    .then(response => response.json())
    .then(data => {
        const errorMessages = document.getElementById('error-messages');
        errorMessages.innerHTML = ''; // Xóa lỗi cũ

        if (data.message === 'success') {
            alert('Đăng ký thành công! Chuyển đến trang đăng nhập.');
            location.href = '/blueskyweb/account/login';
        } else {
            if (data.errors) {
                // Hiển thị từng lỗi ngay trên giao diện
                Object.entries(data.errors).forEach(([key, value]) => {
                    errorMessages.innerHTML += `<p class="text-danger">${value}</p>`;
                });
            } else {
                errorMessages.innerHTML = '<p class="text-danger">Đăng ký thất bại!</p>';
            }
        }
    })
    .catch(error => {
        console.error("Lỗi đăng ký:", error);
        document.getElementById('error-messages').innerHTML = "<p class='text-danger'>Lỗi máy chủ, vui lòng thử lại!</p>";
    });
});
</script>

<style>
    /* Gradient nền */
    .gradient-custom {
        background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
        min-height: calc(100vh - 60px); /* Đảm bảo chiều cao tối thiểu trừ chiều cao navbar */
        position: relative;
        padding-bottom: 60px; /* Đảm bảo đủ không gian cho footer */
    }

    /* Tùy chỉnh card */
    .card {
        background-color: #fff;
        border-radius: 1rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border: none;
    }

    /* Tùy chỉnh tiêu đề */
    .page-title {
        font-size: 2rem;
        font-weight: bold;
        color: #007bff;
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }

    /* Tùy chỉnh form */
    .form-group label {
        font-weight: bold;
        color: #333;
    }

    .form-group input {
        border-radius: 5px;
        border: 1px solid #ced4da;
        transition: all 0.3s ease;
    }

    .form-group input:focus {
        border-color: #007bff;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
    }

    .form-group input::placeholder {
        color: #adb5bd;
    }

    /* Tùy chỉnh nút đăng ký */
    .btn-primary {
        background-color: #007bff;
        border: none;
        padding: 10px 30px;
        font-weight: bold;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #0056b3;
        transform: translateY(-2px);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    /* Tùy chỉnh thông báo lỗi */
    .text-danger {
        font-size: 0.9rem;
        padding: 10px;
        background-color: #f8d7da;
        border-radius: 5px;
    }

    /* Tùy chỉnh liên kết */
    .signup-link {
        color: #007bff;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .signup-link:hover {
        color: #0056b3;
        text-decoration: underline;
    }

    /* Tùy chỉnh văn bản phụ */
    .text-muted {
        color: #6c757d !important;
    }

    /* Đảm bảo footer nằm ở dưới cùng */
    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    .main-content {
        flex: 1 0 auto; /* Đảm bảo nội dung chính mở rộng để đẩy footer xuống */
    }

    .footer {
        flex-shrink: 0; /* Đảm bảo footer không bị co lại */
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-title {
            font-size: 1.8rem;
        }

        .card-body {
            padding: 3rem !important;
        }

        .btn-primary {
            padding: 8px 20px;
            font-size: 0.9rem;
        }

        /* Khi màn hình nhỏ, các input sẽ xếp chồng */
        .form-group {
            margin-bottom: 1rem;
        }
    }

    @media (max-width: 576px) {
        .page-title {
            font-size: 1.5rem;
        }

        .card-body {
            padding: 2rem !important;
        }

        .form-control-lg {
            font-size: 0.9rem;
        }

        .btn-primary {
            padding: 8px 15px;
            font-size: 0.8rem;
        }
    }
</style>