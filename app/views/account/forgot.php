<?php include 'app/views/shares/header.php'; ?>

<section class="gradient-custom">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg">
                    <div class="card-body p-4">
                        <h3 class="text-center mb-4 text-primary">Quên mật khẩu</h3>
                        <p class="text-center text-muted">Nhập email đã đăng ký để nhận mã đặt lại mật khẩu.</p>

                        <form id="forgot-form">
                            <div class="form-group mb-3">
                                <input type="email" class="form-control" name="email" placeholder="Email của bạn" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Gửi mã đặt lại mật khẩu</button>
                        </form>

                        <p id="forgot-message" class="mt-3 text-center"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById("forgot-form").addEventListener("submit", function (e) {
    e.preventDefault();
    const email = this.email.value;

    fetch('/blueskyweb/account/handleForgot', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email })
    })
    .then(res => res.json())
    .then(data => {
        const message = document.getElementById('forgot-message');
        if (data.status === 'success') {
            message.textContent = "Mã xác nhận đã được gửi tới email của bạn!";
            message.className = "text-success";
            setTimeout(() => {
            window.location.href = '/blueskyweb/account/verify_reset';
        }, 2000);
        } else {
            message.textContent = data.message || "Có lỗi xảy ra!";
            message.className = "text-danger";
        }
    })
    .catch(err => {
        document.getElementById('forgot-message').textContent = "Lỗi hệ thống!";
        document.getElementById('forgot-message').className = "text-danger";
        console.error(err);
    });
});
</script>

<?php include 'app/views/shares/footer.php'; ?>
