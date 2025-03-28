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
$orderApiUrl = "http://localhost/blueskyweb/api/orders/user";
$ch = curl_init($orderApiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token"
]);
$response = curl_exec($ch);
curl_close($ch);
$orders = json_decode($response, true);

// Các trạng thái hiển thị cho người dùng
$statuses = [
    'pending' => ['label' => 'Đang xử lý', 'color' => 'warning'],
    'processing' => ['label' => 'Đang giao', 'color' => 'info'],
    'completed' => ['label' => 'Hoàn tất', 'color' => 'success'],
    'canceled' => ['label' => 'Đã huỷ', 'color' => 'danger']
];
?>

<div class="container py-5">
    <h2 class="text-center fw-bold mb-4 text-primary">Danh sách đơn hàng</h2>

    <ul class="nav nav-pills justify-content-center mb-4 gap-2" id="orderTabs" role="tablist">
        <?php $first = true; foreach ($statuses as $key => $status): ?>
            <li class="nav-item">
                <button class="nav-link <?= $first ? 'active' : '' ?> bg-<?= $status['color'] ?> text-white"
                        data-bs-toggle="pill" data-bs-target="#tab-<?= $key ?>" type="button" role="tab">
                    <?= $status['label'] ?>
                </button>
            </li>
        <?php $first = false; endforeach; ?>
    </ul>

    <div class="tab-content">
        <?php $first = true; foreach ($statuses as $statusKey => $status): 
            $filtered = array_filter($orders, function($o) use ($statusKey) {
                $mappedStatus = match ($o['status']) {
                    'unpaid' => 'pending',
                    'paid' => 'completed',
                    default => $o['status']
                };
                return $mappedStatus === $statusKey;
            }); ?>

            <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" id="tab-<?= $statusKey ?>">
                <?php if (empty($filtered)): ?>
                    <div class="alert alert-secondary text-center">Không có đơn hàng nào ở trạng thái này.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>Ngày đặt</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($filtered as $order): ?>
                                    <?php
                                    $displayStatus = match ($order['status']) {
                                        'unpaid' => 'pending',
                                        'paid' => 'completed',
                                        default => $order['status']
                                    };
                                    ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                        <td><?= number_format($order['total_amount'], 0, ',', '.') ?> VND</td>
                                        <td><span class="badge bg-<?= $statuses[$displayStatus]['color'] ?>">
                                            <?= $statuses[$displayStatus]['label'] ?></span></td>
                                        <td>
                                            <a href="/blueskyweb/account/order_detail?id=<?= $order['id'] ?>" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye"></i> Chi tiết
                                            </a>
                                            <?php if (in_array($order['status'], ['pending', 'unpaid'])): ?>
                                                <button class="btn btn-outline-danger btn-sm cancel-order-btn" data-id="<?= $order['id'] ?>">
                                                    <i class="fas fa-times"></i> Huỷ đơn
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
                    "Authorization": "Bearer <?= $token ?>",
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include_once 'app/views/shares/footer.php'; ?>
