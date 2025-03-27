<?php
require_once 'app/config/database.php';
require_once 'app/utils/JWTHandler.php';

class StatisticsApiController
{
    private $db;
    private $jwtHandler;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->jwtHandler = new JWTHandler();
    }

    private function authenticate()
    {
        $headers = apache_request_headers();
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            $arr = explode(" ", $authHeader);
            $jwt = $arr[1] ?? null;
            if ($jwt) {
                $decoded = $this->jwtHandler->decode($jwt);
                if ($decoded && $decoded['role'] === 'admin') {
                    return $decoded;
                }
            }
        }
        return null;
    }

    public function revenue()
    {
        header('Content-Type: application/json');
        if (!$this->authenticate()) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized']);
            return;
        }

        $type = $_GET['type'] ?? 'day'; // day | month | year
        $from = $_GET['from'] ?? null;
        $to = $_GET['to'] ?? null;

        $dateFormat = match ($type) {
            'month' => '%Y-%m',
            'year' => '%Y',
            default => '%Y-%m-%d'
        };

        // Tổng doanh thu và số đơn đã hoàn tất
        $sql = "SELECT 
                    DATE_FORMAT(created_at, '$dateFormat') as time_key,
                    COUNT(*) as order_count,
                    SUM(total_amount) as total_revenue
                FROM orders 
                WHERE status = 'completed'";

        $params = [];

        if ($from && $to) {
            $sql .= " AND created_at BETWEEN :from AND :to";
            $params['from'] = $from . " 00:00:00";
            $params['to'] = $to . " 23:59:59";
        }

        $sql .= " GROUP BY time_key ORDER BY time_key DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $revenueStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Doanh thu theo từng sản phẩm
        $stmt2 = $this->db->prepare("
            SELECT 
                p.id as product_id, 
                p.name, 
                SUM(od.quantity) as quantity_sold, 
                SUM(od.quantity * od.price) as total_revenue
            FROM order_details od
            JOIN product p ON od.product_id = p.id
            JOIN orders o ON o.id = od.order_id
            WHERE o.status = 'completed'
            GROUP BY p.id, p.name
            ORDER BY total_revenue DESC
        ");
        $stmt2->execute();
        $productRevenue = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'revenue_stats' => $revenueStats,
            'revenue_by_product' => $productRevenue
        ]);
    }
}
