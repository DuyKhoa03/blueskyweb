<?php
require_once 'app/config/database.php';
require_once 'app/utils/JWTHandler.php';

class AdminController
{
    private $db;
    private $jwtHandler;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->jwtHandler = new JWTHandler();
    }

    // Trang chính của admin
    public function index()
    {
        include_once 'app/views/admin/index.php';
    }

    // Quản lý người dùng
    public function users()
    {
        include_once 'app/views/admin/users/index.php';
    }

    public function editUser($userId)
    {
        // Lưu userId để sử dụng trong view
        include_once 'app/views/admin/users/edit.php';
    }

    // Quản lý sản phẩm
    public function products()
    {
        include_once 'app/views/admin/products/index.php';
    }

    public function addProduct()
    {
        include_once 'app/views/admin/products/add.php';
    }
    public function importProduct()
    {
        include_once 'app/views/admin/products/import.php';
    }
    public function editProduct($editId)
    {
        include_once 'app/views/admin/products/edit.php';
    }

    // Quản lý danh mục
    public function categories()
    {
        include_once 'app/views/admin/categories/index.php';
    }

    public function addCategory()
    {
        include_once 'app/views/admin/categories/add.php';
    }

    public function editCategory($id)
    {
        // Gọi API để lấy thông tin danh mục
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://localhost/blueskyweb/api/category/$id");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $category = json_decode($response);

        if (!$category) {
            die("Danh mục không tồn tại!");
        }

        include_once 'app/views/admin/categories/edit.php';
    }

    // Quản lý đơn hàng
    public function orders()
    {
        include_once 'app/views/admin/orders/index.php';
    }
}