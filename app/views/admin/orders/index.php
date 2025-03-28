<?php include 'app/views/shares/header.php'; ?>

<div class="container mt-5">
    <h1 class="page-title text-center mb-4">Quản lý Đơn hàng</h1>

    <!-- Tabs lọc trạng thái -->
    <ul class="nav nav-tabs mb-4 shadow-sm" id="statusTabs">
        <li class="nav-item">
            <a class="nav-link active status-all" data-status="all" href="#">Tất cả</a>
        </li>
        <li class="nav-item">
            <a class="nav-link status-pending" data-status="pending" href="#">Chờ xử lý</a>
        </li>
        <li class="nav-item">
            <a class="nav-link status-processing" data-status="processing" href="#">Đang giao</a>
        </li>
        <li class="nav-item">
            <a class="nav-link status-completed" data-status="completed" href="#">Hoàn tất</a>
        </li>
        <li class="nav-item">
            <a class="nav-link status-canceled" data-status="canceled" href="#">Đã huỷ</a>
        </li>
    </ul>

    <!-- Bảng đơn hàng -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0">
                    <thead class="table-dark">
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
    </div>
</div>

<style>
/* (Style giữ nguyên như cũ – không thay đổi) */
.page-title { color: #2c3e50; font-weight: 700; }
.nav-tabs { border-radius: 10px; overflow: hidden; background-color: #f8f9fa; border: none; }
.nav-tabs .nav-item { flex: 1; text-align: center; }
.nav-tabs .nav-link { padding: 15px 20px; font-weight: 600; font-size: 1.1rem; border: none; color: white; }
.nav-tabs .status-all { background-color: #007bff; color: white; }
.nav-tabs .status-pending { background-color: #ffc107; color: #212529; }
.nav-tabs .status-processing { background-color: #17a2b8; color: white; }
.nav-tabs .status-completed { background-color: #28a745; color: white; }
.nav-tabs .status-canceled { background-color: #dc3545; color: white; }
.nav-tabs .nav-link.active { text-decoration: underline; font-weight: bold; }
.table-hover tbody tr:hover { background-color: inherit; }
.card { border: none; border-radius: 10px; }
.table { margin-bottom: 0; }
.table-dark { background-color: #343a40; color: white; }
.status-select { width: 120px; display: inline-block; margin-right: 5px; }
</style>

<script>
const token = <?php echo json_encode($_SESSION['jwtToken'] ?? ''); ?>;
let currentStatus = "all";

// Map trạng thái từ hệ thống backend sang status hiển thị
function mapStatus(status) {
    switch (status) {
        case 'unpaid': return 'pending';
        case 'paid': return 'completed';
        default: return status;
    }
}

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

        const filtered = currentStatus === "all"
            ? data
            : data.filter(o => mapStatus(o.status) === currentStatus);

        if (filtered.length === 0) {
            list.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-4">Không có đơn hàng nào.</td></tr>`;
            return;
        }

        filtered.forEach(order => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${order.id}</td>
                <td>${order.user_name || 'Không xác định'}</td>
                <td>${order.address}</td>
                <td>${parseFloat(order.total_amount).toLocaleString()} VND</td>
                <td>${new Date(order.created_at).toLocaleDateString('vi-VN')}</td>
                <td>
                    <select class="form-control form-control-sm status-select" data-id="${order.id}">
                        <option value="pending" ${mapStatus(order.status) === 'pending' ? 'selected' : ''}>Chờ xử lý</option>
                        <option value="processing" ${mapStatus(order.status) === 'processing' ? 'selected' : ''}>Đang giao</option>
                        <option value="completed" ${mapStatus(order.status) === 'completed' ? 'selected' : ''}>Hoàn tất</option>
                        <option value="canceled" ${mapStatus(order.status) === 'canceled' ? 'selected' : ''}>Đã huỷ</option>
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
            `<tr><td colspan="7" class="text-center text-danger py-4">Lỗi tải dữ liệu: ${err.message}</td></tr>`;
    });
}

// Cập nhật trạng thái
document.addEventListener("click", function (e) {
    if (e.target.classList.contains("update-status-btn")) {
        const orderId = e.target.getAttribute("data-id");
        const select = document.querySelector(`.status-select[data-id="${orderId}"]`);
        const newStatus = select.value;

        if (!confirm(`Xác nhận cập nhật đơn #${orderId} thành "${select.options[select.selectedIndex].text}"?`)) return;

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
