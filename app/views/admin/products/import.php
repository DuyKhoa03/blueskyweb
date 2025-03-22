<?php
include 'app/views/shares/header.php';

// Load thư viện PhpSpreadsheet
require_once __DIR__ . '/../../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (session_status() == PHP_SESSION_NONE) session_start();
$token = $_SESSION['jwtToken'] ?? null;

// Xử lý khi submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file']['tmp_name'];

    try {
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $successCount = 0;
        $failCount = 0;

        // Bỏ qua dòng tiêu đề
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $name = $row[0] ?? '';
            $description = $row[1] ?? '';
            $price = $row[2] ?? 0;
            $category_id = $row[3] ?? null;

            // Chuẩn bị dữ liệu
            $data = [
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'category_id' => $category_id,
                'image' => '' // Không xử lý ảnh khi import Excel
            ];

            // Gọi API để thêm sản phẩm
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => 'http://localhost/blueskyweb/api/product',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $token,
                    'Content-Type: multipart/form-data'
                ]
            ]);
            
            $response = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($status === 201 || $status === 200) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        echo "<div class='alert alert-success mt-4'>Đã import: {$successCount} sản phẩm thành công. {$failCount} thất bại.</div>";

    } catch (Exception $e) {
        echo "<div class='alert alert-danger mt-4'>Lỗi khi đọc file Excel: " . $e->getMessage() . "</div>";
    }
}
?>

<div class="container mt-5">
    <h2>Import Sản phẩm từ Excel</h2>
    <form action="" method="POST" enctype="multipart/form-data" class="mb-4" style="max-width: 500px;">
        <div class="form-group mb-3">
            <label>Chọn file Excel (.xlsx)</label>
            <input type="file" name="excel_file" accept=".xlsx" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Import</button>
        <a href="/blueskyweb/admin/products" class="btn btn-secondary ms-2">Quay lại</a>
    </form>

    <div class="card">
        <div class="card-header bg-light fw-bold">Mẫu file Excel</div>
        <div class="card-body">
            <p>File Excel nên có định dạng như sau:</p>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tên sản phẩm</th>
                        <th>Mô tả</th>
                        <th>Giá</th>
                        <th>ID Danh mục</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Laptop Dell 123</td>
                        <td>Cấu hình mạnh</td>
                        <td>15000000</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>Áo Thun Trơn</td>
                        <td>Chất liệu cotton</td>
                        <td>99000</td>
                        <td>2</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
