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

// Xử lý chuỗi URL
$url = $_GET['url'] ?? '';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

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