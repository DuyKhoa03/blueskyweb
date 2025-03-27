<?php
include_once 'app/views/shares/header.php';
require_once 'app/models/OrderModel.php';
require_once 'app/config/database.php';

$orderId = $_GET['id'] ?? null;
if (!$orderId) {
    echo "<div class='alert alert-danger mx-5 my-4'>Không tìm thấy đơn hàng!</div>";
    include_once 'app/views/shares/footer.php';
    exit;
}

$db = (new Database())->getConnection();
$orderModel = new OrderModel($db);
$orderDetails = $orderModel->getOrderDetailsById($orderId, null); // Admin xem được tất cả

if (empty($orderDetails)) {
    echo "<div class='alert alert-warning mx-5 my-4'>Không có dữ liệu chi tiết đơn hàng.</div>";
    include_once 'app/views/shares/footer.php';
    exit;
}

$order = $orderDetails[0];
?>

<div class="container mt-5">
    <h1 class="page-title text-center mb-4">Chi tiết đơn hàng #<?= $order['id'] ?></h1>

    <!-- Thông tin người đặt và thông tin đơn hàng nằm cùng hàng -->
    <div class="row mb-4">
        <!-- Thông tin người đặt -->
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title text-info"><i class="fas fa-user me-2"></i>Thông tin người đặt</h5>
                    <ul class="list-unstyled">
                        <li><strong>Họ tên:</strong> <?= htmlspecialchars($order['fullname']) ?></li>
                        <li><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></li>
                        <li><strong>Số điện thoại:</strong> <?= htmlspecialchars($order['user_phone']) ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Thông tin đơn hàng -->
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title text-primary"><i class="fas fa-file-invoice me-2"></i>Thông tin đơn hàng</h5>
                    <ul class="list-unstyled">
                        <li><strong>Người đặt:</strong> <?= htmlspecialchars($order['name']) ?> (<?= htmlspecialchars($order['phone']) ?>)</li>
                        <li><strong>Địa chỉ:</strong> <?= htmlspecialchars($order['address']) ?></li>
                        <li><strong>Ngày đặt:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></li>
                        <li><strong>Trạng thái:</strong> 
                            <span class="badge 
                                <?= $order['status'] === 'pending' ? 'bg-warning text-dark' : 
                                    ($order['status'] === 'processing' ? 'bg-info' : 
                                    ($order['status'] === 'completed' ? 'bg-success' : 'bg-danger')) ?>">
                                <?= htmlspecialchars($order['status']) ?>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Sản phẩm trong đơn -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title"><i class="fas fa-box-open me-2"></i>Sản phẩm trong đơn</h5>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Hình</th>
                            <th>Tên sản phẩm</th>
                            <th>Giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total = 0;
                        foreach ($orderDetails as $item):
                            $lineTotal = $item['price'] * $item['quantity'];
                            $total += $lineTotal;
                        ?>
                            <tr>
                                <td><img src="/blueskyweb/<?= htmlspecialchars($item['image']) ?>" width="80" height="80" class="rounded" style="object-fit: cover;"></td>
                                <td><?= htmlspecialchars($item['product_name']) ?></td>
                                <td><?= number_format($item['price'], 0, ',', '.') ?> VND</td>
                                <td><?= $item['quantity'] ?></td>
                                <td><?= number_format($lineTotal, 0, ',', '.') ?> VND</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-end mt-3">
                <h5><strong>Tổng cộng:</strong> <span class="text-primary"><?= number_format($total, 0, ',', '.') ?> VND</span></h5>
            </div>
        </div>
    </div>

    <!-- Nút in hóa đơn -->
    <div class="text-end">
        <a href="/blueskyweb/export/invoice/<?= $order['id'] ?>" class="btn btn-danger shadow-sm" target="_blank">
            <i class="fas fa-file-pdf me-2"></i>In hóa đơn (PDF)
        </a>
    </div>
</div>

<style>
.page-title {
    color: #2c3e50;
    font-weight: 700;
}

.card {
    border: none;
    border-radius: 10px;
}

.card-body {
    padding: 2rem;
}

.card-title {
    color: #34495e;
    margin-bottom: 1.5rem;
}

.list-unstyled li {
    margin-bottom: 0.75rem;
    color: #495057;
}

.badge {
    font-size: 1rem;
    padding: 0.5em 1em;
}

.table th {
    background-color: #f8f9fa;
    color: #495057;
}

.table img {
    transition: all 0.3s ease;
}

.table img:hover {
    transform: scale(1.1);
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}

.btn-danger {
    border-radius: 5px;
    padding: 10px 20px;
    transition: all 0.3s ease;
}

.btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 0, 0, 0.3);
}

.text-primary {
    font-weight: 600;
}
</style>

<?php include_once 'app/views/shares/footer.php'; ?>