<?php include 'app/views/shares/header.php'; ?>

<div class="container">
    <div class="row d-flex justify-content-center align-items-center">
        <div class="col-md-6">
            <div class="card shadow-lg p-4">
                <h2 class="text-center">Đăng ký tài khoản</h2>

                <div id="error-messages" class="text-danger text-center"></div>

                <form id="register-form">
                    <div class="form-group">
                        <label for="username">Tên đăng nhập:</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Nhập username" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Nhập email" required>
                    </div>
                    <div class="form-group">
                        <label for="fullname">Họ và tên:</label>
                        <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Nhập họ và tên" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Mật khẩu:</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Nhập mật khẩu" required>
                    </div>
                    <div class="form-group">
                        <label for="confirmpassword">Xác nhận mật khẩu:</label>
                        <input type="password" class="form-control" id="confirmpassword" name="confirmpassword" placeholder="Nhập lại mật khẩu" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Đăng ký</button>
                </form>

                <div class="text-center mt-3">
                    <p>Đã có tài khoản? <a href="/blueskyweb/account/login">Đăng nhập ngay</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

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
        if (data.message === 'success') {
            alert('Đăng ký thành công! Chuyển đến trang đăng nhập.');
            location.href = '/blueskyweb/account/login';
        } else {
            document.getElementById('error-messages').innerHTML = data.errors ? data.errors.join('<br>') : 'Đăng ký thất bại!';
        }
    })
    .catch(error => {
        console.error("Lỗi đăng ký:", error);
        document.getElementById('error-messages').innerHTML = "Lỗi máy chủ, vui lòng thử lại!";
    });
});
</script>
