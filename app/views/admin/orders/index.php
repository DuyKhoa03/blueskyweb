<?php include 'app/views/shares/header.php'; ?>

<div class="container">
    <h1 class="page-title">Quản lý Đơn hàng</h1>
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
                </tr>
            </thead>
            <tbody id="order-list">
                <!-- Danh sách đơn hàng sẽ được tải bằng JavaScript -->
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const token = <?php echo json_encode($_SESSION['jwtToken'] ?? ''); ?>;
    if (!token) {
        alert('Vui lòng đăng nhập');
        location.href = '/blueskyweb/account/login';
        return;
    }

    // Gọi API để lấy danh sách đơn hàng
    fetch('/blueskyweb/api/order', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + token
        }
    })
    .then(response => {
        if (response.status === 401) {
            alert('Phiên đăng nhập không hợp lệ, vui lòng đăng nhập lại!');
            location.href = '/blueskyweb/account/login';
            return Promise.reject('Unauthorized');
        }
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error('Lỗi khi gọi API: ' + response.status + ' - ' + text);
            });
        }
        return response.json();
    })
    .then(data => {
        const orderList = document.getElementById('order-list');
        orderList.innerHTML = ''; // Xóa danh sách cũ

        if (Array.isArray(data) && data.length > 0) {
            data.forEach(order => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${order.id}</td>
                    <td>${order.user_name || 'Không xác định'}</td>
                    <td>${order.address}</td>
                    <td>${order.total_amount.toLocaleString()} VND</td>
                    <td>${order.created_at}</td>
                    <td>${order.status}</td>
                `;
                orderList.appendChild(row);
            });
        } else {
            orderList.innerHTML = '<tr><td colspan="6" class="text-center">Không có đơn hàng nào.</td></tr>';
        }
    })
    .catch(error => {
        console.error("Lỗi khi tải danh sách đơn hàng:", error);
        const orderList = document.getElementById('order-list');
        orderList.innerHTML = '<tr><td colspan="6" class="text-center">Lỗi khi tải dữ liệu: ' + error.message + '</td></tr>';
    });
});
</script>

<?php include 'app/views/shares/footer.php'; ?>