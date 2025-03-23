<?php include 'app/views/shares/header.php'; ?>

<section class="gradient-custom">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-lg">
          <div class="card-body p-4">
            <h3 class="text-center text-primary mb-4">Đặt lại mật khẩu mới</h3>

            <form id="reset-form">
              <div class="form-group mb-3">
                <input type="password" name="password" class="form-control" placeholder="Mật khẩu mới" required minlength="6">
              </div>
              <div class="form-group mb-3">
                <input type="password" name="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu" required minlength="6">
              </div>
              <button type="submit" class="btn btn-primary w-100">Cập nhật mật khẩu</button>
            </form>

            <p id="reset-message" class="mt-3 text-center"></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
document.getElementById("reset-form").addEventListener("submit", function(e) {
    e.preventDefault();
    const pw = this.password.value;
    const cpw = this.confirm_password.value;
    if (pw !== cpw) {
        document.getElementById("reset-message").textContent = "Mật khẩu không khớp!";
        document.getElementById("reset-message").className = "text-danger text-center";
        return;
    }

    fetch("/blueskyweb/account/resetPasswordAction", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ password: pw })
    })
    .then(res => res.json())
    .then(data => {
        const msg = document.getElementById("reset-message");
        if (data.status === "success") {
            msg.textContent = "Cập nhật mật khẩu thành công! Đang chuyển hướng...";
            msg.className = "text-success text-center";
            setTimeout(() => window.location.href = "/blueskyweb/account/login", 2000);
        } else {
            msg.textContent = data.message || "Cập nhật thất bại!";
            msg.className = "text-danger text-center";
        }
    });
});
</script>

<?php include 'app/views/shares/footer.php'; ?>
