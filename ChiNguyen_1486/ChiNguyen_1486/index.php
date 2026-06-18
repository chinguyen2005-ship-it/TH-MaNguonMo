<?php
// 1. KHỞI TẠO SESSION: Đặt ở dòng đầu tiên để toàn bộ website nhận diện được Giỏ hàng
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. ĐỊNH NGHĨA ĐƯỜNG DẪN GỐC TUYỆT ĐỐI
define('BASE_PATH', __DIR__);

// 3. ĐỒNG BỘ AUTOLOADER: Sử dụng BASE_PATH để tìm file chính xác tuyệt đối từ thư mục gốc
spl_autoload_register(function ($class) {
    $paths = [
        BASE_PATH . '/app/controllers/',
        BASE_PATH . '/app/models/',
        BASE_PATH . '/app/helpers/'
    ];
    foreach ($paths as $path) {
        if (file_exists($path . $class . '.php')) {
            require_once $path . $class . '.php';
            break;
        }
    }
});

// 3.5 TỰ ĐỘNG ĐĂNG NHẬP QUA COOKIE REMEMBER ME
if (!isset($_SESSION['username']) && isset($_COOKIE['remember_me'])) {
    $cookieValue = base64_decode($_COOKIE['remember_me']);
    $parts = explode(':', $cookieValue);
    if (count($parts) === 2) {
        $cookieUser = $parts[0];
        $cookieToken = $parts[1];
        
        $dbConn = (new Database())->getConnection();
        if ($dbConn) {
            $accModel = new AccountModel($dbConn);
            $account = $accModel->getUserByRememberToken($cookieUser, $cookieToken);
            if ($account) {
                $_SESSION['username'] = $account->username;
                $_SESSION['fullname'] = $account->fullname;
                $_SESSION['email'] = $account->email;
                $_SESSION['phone'] = $account->phone;
                $_SESSION['role'] = $account->role;
                $_SESSION['user_role'] = $account->role;
                $_SESSION['avatar'] = $account->avatar;
            } else {
                // Xóa cookie rác nếu không hợp lệ trên hệ thống
                setcookie('remember_me', '', time() - 3600, '/');
            }
        }
    }
}

// Xử lý chuỗi URL
$url = $_GET['url'] ?? '';
$url = rtrim($url, '/');
$url = explode('/', $url);

// Nếu phần tử đầu tiên trùng với tên thư mục dự án ChiNguyen_1486 thì loại bỏ
if (!empty($url[0]) && strcasecmp($url[0], 'ChiNguyen_1486') === 0) {
    array_shift($url);
}

// Lọc và định tuyến RESTful API động
if (!empty($url[0]) && strtolower($url[0]) === 'api') {
    if (isset($url[1])) {
        $resource = strtolower($url[1]);
        $controllerName = ucfirst($resource) . 'ApiController';
        
        $action = 'index';
        $params = [];
        $method = $_SERVER['REQUEST_METHOD'];
        
        if (isset($url[2])) {
            if (is_numeric($url[2])) {
                if ($method === 'GET') {
                    $action = 'show';
                } elseif ($method === 'PUT') {
                    $action = 'update';
                } elseif ($method === 'DELETE') {
                    $action = 'delete';
                }
                $params = [intval($url[2])];
            } else {
                $action = $url[2];
                // Chuẩn hóa kebab-case (ví dụ: update-status -> updateStatus)
                $action = str_replace(' ', '', lcfirst(ucwords(str_replace('-', ' ', $action))));
                if ($resource === 'product' && $action === 'login') {
                    $action = 'apiLogin';
                }
                
                if (isset($url[3]) && is_numeric($url[3])) {
                    $params = [intval($url[3])];
                } else {
                    $params = array_slice($url, 3);
                }
            }
        } else {
            if ($method === 'GET') {
                $action = 'index';
            } elseif ($method === 'POST') {
                $action = 'add';
            } elseif ($method === 'PUT') {
                $action = 'update';
            } elseif ($method === 'DELETE') {
                $action = 'delete';
            }
        }
        
        if (class_exists($controllerName)) {
            $controller = new $controllerName();
            if (method_exists($controller, $action)) {
                call_user_func_array([$controller, $action], $params);
                exit();
            } else {
                header("HTTP/1.1 405 Method Not Allowed");
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(["message" => "Action '$action' không tồn tại trong $controllerName."], JSON_UNESCAPED_UNICODE);
                exit();
            }
        } else {
            header("HTTP/1.1 404 Not Found");
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(["message" => "API endpoint không hợp lệ."], JSON_UNESCAPED_UNICODE);
            exit();
        }
    } else {
        header("HTTP/1.1 400 Bad Request");
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(["message" => "Thiếu tên API resource."], JSON_UNESCAPED_UNICODE);
        exit();
    }
}

// 4. Xác định Controller cần gọi
$controllerName = (!empty($url[0])) ? ucfirst($url[0]) . 'Controller' : 'ProductController';

// 5. Xác định Action (phương thức) cần chạy
$action = (!empty($url[1])) ? $url[1] : 'index';

// 6. Kiểm tra Class Controller có tồn tại thực tế trên hệ thống không
if (!class_exists($controllerName)) {
    die("Controller '$controllerName' không tồn tại.");
}

// 7. Khởi tạo đối tượng Controller
$controller = new $controllerName();

// 8. Kiểm tra phương thức xử lý có nằm bên trong Controller không
if (!method_exists($controller, $action)) {
    die("Action '$action' không tồn tại trong $controllerName.");
}

// 9. Thực thi Action và truyền các tham số (ví dụ: ID sản phẩm) đằng sau URL vào
call_user_func_array([$controller, $action], array_slice($url, 2));