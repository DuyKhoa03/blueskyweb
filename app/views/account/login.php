<?php include 'app/views/shares/header.php'; ?>

<section class="gradient-custom">
    <div class="container py-5">
        <div class="row d-flex justify-content-center align-items-center">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                <div class="card shadow-lg" style="border-radius: 1rem;">
                    <div class="card-body p-5 text-center">
                        <h2 class="page-title mb-2">Đăng nhập</h2>
                        <p class="text-muted mb-5">Vui lòng nhập tên đăng nhập và mật khẩu của bạn!</p>

                        <form id="login-form">
                            <div class="form-group mb-4">
                                <input type="text" name="username_or_email" class="form-control form-control-lg" placeholder="Tên đăng nhập hoặc Email" required />
                            </div>

                            <div class="form-group mb-4">
                                <input type="password" name="password" class="form-control form-control-lg" placeholder="Mật khẩu" required />
                            </div>

                            <p class="small mb-4">
                                <a href="#!" class="text-primary forgot-password">Quên mật khẩu?</a>
                            </p>

                            <button class="btn btn-primary btn-lg px-5" type="submit">Đăng nhập</button>

                            <div class="mt-4">
                                <p class="text-muted mb-2">Đăng nhập bằng:</p>
                                <div class="d-flex justify-content-center text-center">
                                    <a href="#!" class="social-icon text-primary mx-2"><i class="fab fa-facebook-f fa-lg"></i></a>
                                    <a href="#!" class="social-icon text-primary mx-2"><i class="fab fa-google fa-lg"></i></a>
                                </div>
                            </div>

                            <div class="mt-4">
                                <p class="mb-0">Bạn chưa có tài khoản? 
                                    <a href="/blueskyweb/account/register" class="text-primary fw-bold signup-link">Đăng ký</a>
                                </p>
                            </div>
                        </form>

                        <p id="login-error" class="text-danger mt-3" style="display: none;"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('login-form').addEventListener('submit', function (event) {
    event.preventDefault();
    const formData = new FormData(this);
    const jsonData = {};
    formData.forEach((value, key) => {
        jsonData[key] = value;
    });

    fetch('/blueskyweb/account/checkLogin', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(jsonData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.message === 'Đăng nhập thành công') {
            location.href = '/blueskyweb/Product';
        } else {
            document.getElementById('login-error').textContent = data.message || 'Đăng nhập thất bại';
            document.getElementById('login-error').style.display = 'block';
        }
    })
    .catch(error => {
        console.error("Lỗi đăng nhập:", error);
        document.getElementById('login-error').textContent = "Lỗi máy chủ, vui lòng thử lại!";
        document.getElementById('login-error').style.display = 'block';
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

    /* Tùy chỉnh nút đăng nhập */
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
        margin-top: 15px;
    }

    /* Tùy chỉnh liên kết */
    .forgot-password, .signup-link {
        color: #007bff;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .forgot-password:hover, .signup-link:hover {
        color: #0056b3;
        text-decoration: underline;
    }

    /* Tùy chỉnh biểu tượng mạng xã hội */
    .social-icon {
        color: #007bff;
        transition: all 0.3s ease;
    }

    .social-icon:hover {
        color: #0056b3;
        transform: translateY(-2px);
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

        .social-icon i {
            font-size: 1.2rem;
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

<?php include 'app/views/shares/footer.php'; ?>