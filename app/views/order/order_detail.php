<?php
include_once 'app/views/shares/header.php';
require_once 'app/models/OrderModel.php';
require_once 'app/config/database.php';

$orderId = $_GET['id'] ?? null;
if (!$orderId) {
    echo "<div class='alert alert-danger'>Không tìm thấy đơn hàng!</div>";
    include_once 'app/views/shares/footer.php';
    exit;
}

$db = (new Database())->getConnection();
$orderModel = new OrderModel($db);
$orderDetails = $orderModel->getOrderDetailsById($orderId, $userid); // từ header.php có $userid

if (empty($orderDetails)) {
    echo "<div class='alert alert-warning'>Không tìm thấy chi tiết đơn hàng hoặc bạn không có quyền xem.</div>";
    include_once 'app/views/shares/footer.php';
    exit;
}

// Lấy thông tin chung từ dòng đầu
$order = $orderDetails[0];

// Map status
$statusMap = [
    'unpaid' => ['label' => 'Đang xử lý', 'color' => 'warning'],
    'paid' => ['label' => 'Hoàn tất', 'color' => 'success'],
    'pending' => ['label' => 'Đang xử lý', 'color' => 'warning'],
    'processing' => ['label' => 'Đang giao', 'color' => 'info'],
    'completed' => ['label' => 'Hoàn tất', 'color' => 'success'],
    'canceled' => ['label' => 'Đã huỷ', 'color' => 'danger']
];

$displayStatus = $statusMap[$order['status']] ?? ['label' => $order['status'], 'color' => 'secondary'];
?>

<div class="container mt-4">
    <h3 class="text-primary mb-3">🧾 Chi tiết đơn hàng #<?= $order['id'] ?></h3>
    <p><strong>Ngày đặt:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
    <p><strong>Trạng thái:</strong> 
        <span class="badge bg-<?= $displayStatus['color'] ?>">
            <?= $displayStatus['label'] ?>
        </span>
    </p>
    <p><strong>Người nhận:</strong> <?= $order['name'] ?> (<?= $order['phone'] ?>)</p>
    <p><strong>Địa chỉ giao:</strong> <?= $order['address'] ?></p>

    <hr>
    <h5>Sản phẩm:</h5>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Hình</th>
                    <th>Tên sản phẩm</th>
                    <th>Giá</th>
                    <th>Số lượng</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php $total = 0; foreach ($orderDetails as $item): 
                    $lineTotal = $item['price'] * $item['quantity'];
                    $total += $lineTotal;
                ?>
                    <tr>
                        <td><img src="/blueskyweb/<?= $item['image'] ?>" width="70" height="70" style="object-fit:cover"></td>
                        <td><?= $item['product_name'] ?></td>
                        <td><?= number_format($item['price'], 0, ',', '.') ?> VND</td>
                        <td><?= $item['quantity'] ?></td>
                        <td><?= number_format($lineTotal, 0, ',', '.') ?> VND</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="text-end">
        <h5><strong>Tổng cộng: <?= number_format($total, 0, ',', '.') ?> VND</strong></h5>
    </div>
</div>

<?php include_once 'app/views/shares/footer.php'; ?>
