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
$orderDetails = $orderModel->getOrderDetailsById($orderId, null); // Admin xem được tất cả

if (empty($orderDetails)) {
    echo "<div class='alert alert-warning'>Không có dữ liệu chi tiết đơn hàng.</div>";
    include_once 'app/views/shares/footer.php';
    exit;
}

$order = $orderDetails[0];
?>

<div class="container mt-4">
<h5 class="text-info">👤 Thông tin người đặt</h5>
<ul>
    <li><strong>Họ tên:</strong> <?= $order['fullname'] ?></li>
    <li><strong>Email:</strong> <?= $order['email'] ?></li>
    <li><strong>Số điện thoại:</strong> <?= $order['user_phone'] ?></li>
</ul>

    <h3 class="text-primary mb-3">🧾 Chi tiết đơn hàng #<?= $order['id'] ?></h3>
    <p><strong>Người đặt:</strong> <?= $order['name'] ?> (<?= $order['phone'] ?>)</p>
    <p><strong>Địa chỉ:</strong> <?= $order['address'] ?></p>
    <p><strong>Ngày đặt:</strong> <?= $order['created_at'] ?></p>
    <p><strong>Trạng thái:</strong> <span class="badge badge-info"><?= $order['status'] ?></span></p>

    <hr>
    <h5>📦 Sản phẩm trong đơn:</h5>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>Hình</th>
                    <th>Tên sản phẩm</th>
                    <th>Giá</th>
                    <th>Số lượng</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php $total = 0;
                foreach ($orderDetails as $item):
                    $lineTotal = $item['price'] * $item['quantity'];
                    $total += $lineTotal;
                ?>
                    <tr>
                        <td><img src="/blueskyweb/<?= $item['image'] ?>" width="80" height="80" style="object-fit:cover"></td>
                        <td><?= $item['product_name'] ?></td>
                        <td><?= number_format($item['price'], 0, ',', '.') ?> VND</td>
                        <td><?= $item['quantity'] ?></td>
                        <td><?= number_format($lineTotal, 0, ',', '.') ?> VND</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="text-right">
        <h5><strong>Tổng cộng: <?= number_format($total, 0, ',', '.') ?> VND</strong></h5>
    </div>
    <a href="/blueskyweb/export/invoice/<?= $order['id'] ?>" class="btn btn-danger mb-3" target="_blank">
    <i class="fas fa-file-pdf"></i> In hóa đơn (PDF)
</a>

</div>

<?php include_once 'app/views/shares/footer.php'; ?>
