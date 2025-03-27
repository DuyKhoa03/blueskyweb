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
    
        // Bắt đầu tạo HTML cho hóa đơn
        $html = '
        <!DOCTYPE html>
        <html lang="vi">
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: DejaVu Sans, sans-serif; font-size: 12px; line-height: 1.4; }
                .container { width: 100%; max-width: 800px; margin: 0 auto; }
                .header { text-align: center; margin-bottom: 20px; }
                .header img { max-width: 150px; }
                .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                .info-table td { padding: 5px; }
                .info-table .label { font-weight: bold; width: 120px; }
                .product-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                .product-table th, .product-table td { border: 1px solid #000; padding: 8px; text-align: center; }
                .product-table th { background-color: #f2f2f2; }
                .total { text-align: right; font-size: 14px; font-weight: bold; }
                .footer { text-align: center; margin-top: 20px; font-size: 10px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <img src="path/to/your/logo.png" alt="Logo"> <!-- Thay bằng đường dẫn logo thực tế -->
                    <h2>HÓA ĐƠN MUA HÀNG</h2>
                    <p>Mã đơn hàng: #' . htmlspecialchars($id) . ' | Ngày in: ' . date('d/m/Y H:i') . '</p>
                </div>
    
                <table class="info-table">
                    <tr>
                        <td class="label">Khách hàng:</td>
                        <td>' . htmlspecialchars($order['fullname']) . ' (' . htmlspecialchars($order['email']) . ')</td>
                    </tr>
                    <tr>
                        <td class="label">Số điện thoại:</td>
                        <td>' . htmlspecialchars($order['user_phone']) . '</td>
                    </tr>
                    <tr>
                        <td class="label">Địa chỉ:</td>
                        <td>' . htmlspecialchars($order['address']) . '</td>
                    </tr>
                    <tr>
                        <td class="label">Ngày đặt hàng:</td>
                        <td>' . date('d/m/Y H:i', strtotime($order['created_at'])) . '</td>
                    </tr>
                    <tr>
                        <td class="label">Trạng thái:</td>
                        <td>' . htmlspecialchars($order['status']) . '</td>
                    </tr>
                </table>
    
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Tên sản phẩm</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>';
    
        $total = 0;
        foreach ($orderDetails as $key => $item) {
            $lineTotal = $item['price'] * $item['quantity'];
            $total += $lineTotal;
            $html .= '
                        <tr>
                            <td>' . ($key + 1) . '</td>
                            <td>' . htmlspecialchars($item['product_name']) . '</td>
                            <td>' . number_format($item['price'], 0, ',', '.') . ' VND</td>
                            <td>' . $item['quantity'] . '</td>
                            <td>' . number_format($lineTotal, 0, ',', '.') . ' VND</td>
                        </tr>';
        }
    
        $html .= '
                    </tbody>
                </table>
    
                <div class="total">
                    Tổng cộng: ' . number_format($total, 0, ',', '.') . ' VND
                </div>
    
                <div class="footer">
                    Cảm ơn quý khách đã mua sắm tại cửa hàng chúng tôi!<br>
                    Địa chỉ: [123 Trần Não, Bình Thạnh] | Hotline: [(+84) 123 456 789] | Website: [http://localhost/blueskyweb/]
                </div>
            </div>
        </body>
        </html>';
    
        // Tạo PDF với mPDF
        $mpdf = new \Mpdf\Mpdf([
            'default_font' => 'DejaVu Sans', // Hỗ trợ tiếng Việt
            'format' => 'A4', // Kích thước giấy
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 15,
            'margin_right' => 15,
        ]);
        $mpdf->WriteHTML($html);
        $mpdf->Output("hoadon-{$id}.pdf", \Mpdf\Output\Destination::INLINE); // hoặc DOWNLOAD
    }
}
