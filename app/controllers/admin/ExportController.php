<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'app/config/database.php';
require_once 'app/models/OrderModel.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use Mpdf\Mpdf;

class ExportController {
    public function invoice($id) {
        $db = (new Database())->getConnection();
        $orderModel = new OrderModel($db);
        $orderDetails = $orderModel->getOrderDetailsById($id);

        if (empty($orderDetails)) {
            die("Không tìm thấy đơn hàng.");
        }

        $order = $orderDetails[0];
        $html = "<h2>HÓA ĐƠN MUA HÀNG</h2>";
        $html .= "<p><strong>Khách hàng:</strong> {$order['fullname']} ({$order['email']})</p>";
        $html .= "<p><strong>Số điện thoại:</strong> {$order['user_phone']}</p>";
        $html .= "<p><strong>Địa chỉ:</strong> {$order['address']}</p>";
        $html .= "<p><strong>Ngày đặt:</strong> {$order['created_at']}</p>";
        $html .= "<p><strong>Trạng thái:</strong> {$order['status']}</p>";

        $html .= "<hr><table border='1' cellpadding='6' cellspacing='0' width='100%'>
                    <thead>
                        <tr>
                            <th>Tên sản phẩm</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead><tbody>";

        $total = 0;
        foreach ($orderDetails as $item) {
            $lineTotal = $item['price'] * $item['quantity'];
            $total += $lineTotal;
            $html .= "<tr>
                        <td>{$item['product_name']}</td>
                        <td>" . number_format($item['price'], 0, ',', '.') . " VND</td>
                        <td>{$item['quantity']}</td>
                        <td>" . number_format($lineTotal, 0, ',', '.') . " VND</td>
                      </tr>";
        }

        $html .= "</tbody></table>";
        $html .= "<h4 style='text-align:right'>Tổng cộng: " . number_format($total, 0, ',', '.') . " VND</h4>";

        $mpdf = new Mpdf();
        $mpdf->WriteHTML($html);
        $mpdf->Output("hoadon-{$id}.pdf", \Mpdf\Output\Destination::INLINE); // hoặc DOWNLOAD
    }
}
