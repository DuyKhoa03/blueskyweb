<?php include 'app/views/shares/header.php'; ?>

<section class="gradient-custom">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-lg">
          <div class="card-body p-4">
            <h3 class="text-center text-primary mb-4">Xác nhận mã khôi phục</h3>
            <p class="text-muted text-center">Nhập mã 6 số đã được gửi tới email của bạn.</p>
            <?php
if (session_status() == PHP_SESSION_NONE) session_start();
$username = $_SESSION['reset_username'] ?? '';
if ($username) {
    echo "<p class='text-info text-center'><strong>Xin chào, tài khoản:</strong> $username</p>";
}
?>

            <form id="verify-form">
              <div class="form-group mb-3">
                <input type="text" name="code" class="form-control" placeholder="Nhập mã xác nhận" required />
              </div>
              <button type="submit" class="btn btn-primary w-100">Xác nhận mã</button>
            </form>

            <p id="verify-message" class="mt-3 text-center"></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
document.getElementById("verify-form").addEventListener("submit", function (e) {
    e.preventDefault();
    const code = this.code.value;

    fetch('/blueskyweb/account/verifyCode', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ code })
    })
    .then(res => res.json())
    .then(data => {
        const msg = document.getElementById("verify-message");
        if (data.status === "success") {
            msg.textContent = "Mã đúng, chuyển đến trang đặt lại mật khẩu...";
            msg.className = "text-success text-center";
            setTimeout(() => {
                window.location.href = "/blueskyweb/account/resetPassword";
            }, 1500);
        } else {
            msg.textContent = data.message || "Mã không hợp lệ!";
            msg.className = "text-danger text-center";
        }
    });
});
</script>

<?php include 'app/views/shares/footer.php'; ?>
