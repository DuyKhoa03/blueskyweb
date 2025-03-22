<?php
session_start();
require_once 'app/models/ProductModel.php';
require_once 'app/helpers/SessionHelper.php';
require_once 'app/controllers/CartApiController.php';
require_once 'app/controllers/OrderApiController.php';
require_once 'app/controllers/UserApiController.php';
require_once 'app/controllers/CheckoutApiController.php';
require_once 'app/controllers/ProductApiController.php';
require_once 'app/controllers/CategoryApiController.php';
// Start session 
// Kiểm tra token từ cookie và khôi phục session nếu cần
$jwtHandler = new JWTHandler();
if (!isset($_SESSION['jwtToken']) && isset($_COOKIE['jwtToken'])) {
    $token = $_COOKIE['jwtToken'];
    try {
        $tokenData = $jwtHandler->decode($token);
        if ($tokenData) {
            // Token hợp lệ, khôi phục session
            $_SESSION['jwtToken'] = $token;
        } else {
            // Token không hợp lệ, xóa cookie
            setcookie('jwtToken', '', time() - 3600, '/');
        }
    } catch (Exception $e) {
        // Token không hợp lệ hoặc hết hạn, xóa cookie
        setcookie('jwtToken', '', time() - 3600, '/');
    }
}

$url = $_GET['url'] ?? '';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// Kiểm tra phần đầu tiên của URL để xác định controller 
$controllerName = isset($url[0]) && $url[0] != '' ? ucfirst($url[0]) . 'Controller' :
    'DefaultController';

// Kiểm tra phần thứ hai của URL để xác định action 
$action = isset($url[1]) && $url[1] != '' ? $url[1] : 'index';
// Định tuyến cho trang admin
if ($controllerName === 'AdminController') {
    // Kiểm tra role của người dùng
    $token = $_SESSION['jwtToken'] ?? $_COOKIE['jwtToken'] ?? null;
    if ($token) {
        $tokenData = $jwtHandler->decode($token);
        $role = $tokenData['role'] ?? null;
        if ($role !== 'admin') {
            // Nếu không phải admin, chuyển hướng về trang chính
            header('Location: /blueskyweb/Product');
            exit();
        }
    } else {
        // Nếu không có token, chuyển hướng về trang đăng nhập
        header('Location: /blueskyweb/account/login');
        exit();
    }

    require_once 'app/controllers/admin/AdminController.php';
    $controller = new AdminController();

    // Xử lý các action của admin
// Xử lý các action của admin
switch ($action) {
    case 'index':
        $controller->index();
        break;

    case 'users':
        if (isset($url[2]) && $url[2] === 'edit' && isset($url[3])) {
            $controller->editUser($url[3]);
        } else {
            $controller->users();
        }
        break;

    case 'products':
        if (isset($url[2]) && $url[2] === 'add') {
            $controller->addProduct();

        } elseif (isset($url[2]) && $url[2] === 'import') {
            // 👉 Gọi trực tiếp view import Excel (không dùng controller)
            include 'app/views/admin/products/import.php';

        } elseif (isset($url[3]) && $url[3] === 'edit' && isset($url[4])) {
            $controller->editProduct($url[4]);

        } else {
            $controller->products();
        }
        break;

    case 'categories':
        if (isset($url[2]) && $url[2] === 'add') {
            $controller->addCategory();
        } elseif (isset($url[2]) && $url[2] === 'edit' && isset($url[3])) {
            $controller->editCategory($url[3]);
        } else {
            $controller->categories();
        }
        break;

    case 'orders':
        $controller->orders();
        break;

    default:
        die('Action not found');
}
exit();

}
// Định tuyến các yêu cầu API 
if ($controllerName === 'ApiController' && isset($url[1])) {
    $apiControllerName = ucfirst($url[1]) . 'ApiController';
    if (file_exists('app/controllers/' . $apiControllerName . '.php')) {
        require_once 'app/controllers/' . $apiControllerName . '.php';
        $controller = new $apiControllerName();

        $method = $_SERVER['REQUEST_METHOD'];
        $id = $url[2] ?? null;

        switch ($method) {
            case 'GET':
                if ($id) {
                    $action = 'show';
                } else {
                    $action = 'index';
                }
                break;
                case 'POST':
                    if (isset($_POST['_method']) && $_POST['_method'] === 'PUT') {
                        if ($id) {
                            $action = 'update';
                        } else {
                            http_response_code(400);
                            echo json_encode(['message' => 'Lỗi: Thiếu ID sản phẩm để cập nhật']);
                            exit;
                        }
                    } else {
                        $action = 'store';
                    }
                    break;
                
                    case 'PUT':
                        if ($id) {
                            $action = 'update';
                        } else {
                            http_response_code(400);
                            echo json_encode(['message' => 'Lỗi: Thiếu ID để cập nhật']);
                            exit;
                        }
                        break;
                    
            case 'DELETE':
                if ($id) {
                    $action = 'destroy';
                }
                break;
            default:
                http_response_code(405);
                echo json_encode(['message' => 'Method Not Allowed']);
                exit;
        }

        if (method_exists($controller, $action)) {
            if ($id) {
                call_user_func_array([$controller, $action], [$id]);
            } else {
                call_user_func_array([$controller, $action], []);
            }
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Action not found']);
        }
        exit;
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Controller not found']);
        exit;
    }
}

// Tạo đối tượng controller tương ứng cho các yêu cầu không phải API 
if (file_exists('app/controllers/' . $controllerName . '.php')) {
    require_once 'app/controllers/' . $controllerName . '.php';
    $controller = new $controllerName();
} else {
    die('Controller not found');
}

// Kiểm tra và gọi action 
if (method_exists($controller, $action)) {
    call_user_func_array([$controller, $action], array_slice($url, 2));
} else {
    die('Action not found');
}
