<?php
include_once 'app/views/shares/header.php';

if (session_status() == PHP_SESSION_NONE) session_start();

require_once 'app/utils/JWTHandler.php';
$jwtHandler = new JWTHandler();

$token = $_SESSION['jwtToken'] ?? null;
$isLoggedIn = false;
$userid = null;
$orders = [];

if ($token) {
    try {
        $tokenData = $jwtHandler->decode($token);
        $userid = $tokenData['id'] ?? null;
        $isLoggedIn = true;
    } catch (Exception $e) {
        unset($_SESSION['jwtToken']);
        header('Location: /blueskyweb/account/login');
        exit();
    }
}

if (!$isLoggedIn) {
    header('Location: /blueskyweb/account/login');
    exit();
}

// Gọi API lấy đơn hàng
$orderApiUrl = "http://localhost/blueskyweb/api/orders/user"; // Đảm bảo đúng endpoint
$ch = curl_init($orderApiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token"
]);
$response = curl_exec($ch);
curl_close($ch);
$orders = json_decode($response, true);
?>

<div class="container mt-5">
    <h2 class="mb-4 text-primary">📦 Danh sách đơn hàng của bạn</h2>

    <?php if (empty($orders)): ?>
        <div class="alert alert-info">Bạn chưa có đơn hàng nào.</div>
    <?php else: ?>
        <!-- Tabs -->
        <ul class="nav nav-tabs" id="orderTabs" role="tablist">
            <?php
            $statuses = ['pending' => '🕐 Chờ xử lý', 'processing' => '🔄 Đang giao', 'completed' => '✅ Hoàn tất', 'canceled' => '❌ Đã huỷ'];
            $first = true;
            foreach ($statuses as $key => $label): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $first ? 'active' : '' ?>" id="<?= $key ?>-tab" data-toggle="tab" href="#<?= $key ?>" role="tab"><?= $label ?></a>
                </li>
            <?php $first = false; endforeach; ?>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content mt-3">
            <?php
            $first = true;
            foreach ($statuses as $statusKey => $statusLabel):
                $filteredOrders = array_filter($orders, fn($o) => $o['status'] === $statusKey);
            ?>
                <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" id="<?= $statusKey ?>" role="tabpanel">
                    <?php if (empty($filteredOrders)): ?>
                        <div class="alert alert-secondary">Không có đơn hàng nào thuộc trạng thái này.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Ngày đặt</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($filteredOrders as $order): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($order['created_at']) ?></td>
                                            <td><?= number_format($order['total_amount'], 0, ',', '.') ?> VND</td>
                                            <td>
                                                <span class="badge badge-<?= $order['status'] === 'completed' ? 'success' : ($order['status'] === 'canceled' ? 'danger' : 'warning') ?>">
                                                    <?= htmlspecialchars($order['status']) ?>
                                                </span>
                                            </td>
                                            <td>
    <a href="/blueskyweb/account/order_detail?id=<?= $order['id'] ?>" class="btn btn-sm btn-info">
        <i class="fas fa-eye"></i> Xem chi tiết
    </a>

    <?php if ($order['status'] === 'pending'): ?>
        <button class="btn btn-sm btn-danger ml-1 cancel-order-btn" data-id="<?= $order['id'] ?>">
            <i class="fas fa-times-circle"></i> Huỷ đơn
        </button>
    <?php endif; ?>
</td>

                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php $first = false; endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".cancel-order-btn").forEach(btn => {
        btn.addEventListener("click", function () {
            const orderId = this.getAttribute("data-id");
            if (!confirm(`Bạn có chắc muốn huỷ đơn hàng #${orderId}?`)) return;

            fetch(`/blueskyweb/api/orders/cancel/${orderId}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": "Bearer <?= $token ?>"
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert("Đơn hàng đã được huỷ!");
                    location.reload();
                } else {
                    alert(data.message || "Huỷ đơn thất bại!");
                }
            })
            .catch(err => {
                console.error("Lỗi khi huỷ đơn:", err);
                alert("Lỗi khi huỷ đơn!");
            });
        });
    });
});
</script>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

<?php include_once 'app/views/shares/footer.php'; ?>
