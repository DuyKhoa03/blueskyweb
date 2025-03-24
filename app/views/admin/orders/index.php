<?php include 'app/views/shares/header.php'; ?>

<div class="container">
    <h1 class="page-title">Quản lý Đơn hàng</h1>

    <!-- Tabs lọc trạng thái -->
    <ul class="nav nav-tabs mb-3" id="statusTabs">
        <li class="nav-item">
            <a class="nav-link active" data-status="all" href="#">Tất cả</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-status="pending" href="#">Chờ xử lý</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-status="processing" href="#">Đang giao</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-status="completed" href="#">Hoàn tất</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-status="canceled" href="#">Đã huỷ</a>
        </li>
    </ul>

    <!-- Bảng đơn hàng -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Tên người dùng</th>
                    <th>Địa chỉ</th>
                    <th>Tổng tiền</th>
                    <th>Ngày đặt</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody id="order-list">
                <!-- Dữ liệu sẽ được render bởi JavaScript -->
            </tbody>
        </table>
    </div>
</div>

<script>
const token = <?php echo json_encode($_SESSION['jwtToken'] ?? ''); ?>;
let currentStatus = "all";

document.addEventListener("DOMContentLoaded", () => {
    if (!token) {
        alert("Vui lòng đăng nhập");
        window.location.href = "/blueskyweb/account/login";
        return;
    }

    loadOrders();

    // Xử lý khi click vào tab
    document.querySelectorAll("#statusTabs .nav-link").forEach(tab => {
        tab.addEventListener("click", function (e) {
            e.preventDefault();
            document.querySelectorAll("#statusTabs .nav-link").forEach(t => t.classList.remove("active"));
            this.classList.add("active");
            currentStatus = this.dataset.status;
            loadOrders();
        });
    });
});

// Hàm load đơn hàng từ API
function loadOrders() {
    fetch("/blueskyweb/api/order", {
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + token
        }
    })
    .then(res => res.json())
    .then(data => {
        const list = document.getElementById("order-list");
        list.innerHTML = "";

        const filtered = currentStatus === "all" ? data : data.filter(o => o.status === currentStatus);

        if (filtered.length === 0) {
            list.innerHTML = `<tr><td colspan="7" class="text-center text-muted">Không có đơn hàng nào.</td></tr>`;
            return;
        }

        filtered.forEach(order => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${order.id}</td>
                <td>${order.user_name || 'Không xác định'}</td>
                <td>${order.address}</td>
                <td>${order.total_amount.toLocaleString()} VND</td>
                <td>${order.created_at}</td>
                <td>
                    <select class="form-control form-control-sm status-select" data-id="${order.id}">
                        <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>Chờ xử lý</option>
                        <option value="processing" ${order.status === 'processing' ? 'selected' : ''}>Đang giao</option>
                        <option value="completed" ${order.status === 'completed' ? 'selected' : ''}>Hoàn tất</option>
                        <option value="canceled" ${order.status === 'canceled' ? 'selected' : ''}>Đã huỷ</option>
                    </select>
                    <button class="btn btn-sm btn-primary mt-1 update-status-btn" data-id="${order.id}">
                        <i class="fas fa-save"></i> Cập nhật
                    </button>
                </td>
                <td>
                    <a href="/blueskyweb/admin/orders/detail/${order.id}" class="btn btn-sm btn-info">
                        <i class="fas fa-eye"></i> Xem
                    </a>
                </td>
            `;
            list.appendChild(row);
        });
    })
    .catch(err => {
        document.getElementById("order-list").innerHTML =
            `<tr><td colspan="7" class="text-center text-danger">Lỗi tải dữ liệu: ${err.message}</td></tr>`;
    });
}

// Cập nhật trạng thái
document.addEventListener("click", function (e) {
    if (e.target.classList.contains("update-status-btn")) {
        const orderId = e.target.getAttribute("data-id");
        const select = document.querySelector(`.status-select[data-id="${orderId}"]`);
        const newStatus = select.value;

        if (!confirm(`Xác nhận cập nhật đơn #${orderId} thành "${newStatus}"?`)) return;

        fetch(`/blueskyweb/api/order/${orderId}`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "Authorization": "Bearer " + token
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === "success") {
                alert("Cập nhật thành công!");
                loadOrders();
            } else {
                alert("Cập nhật thất bại: " + data.message);
            }
        })
        .catch(err => alert("Lỗi: " + err.message));
    }
});
</script>

<?php include 'app/views/shares/footer.php'; ?>
