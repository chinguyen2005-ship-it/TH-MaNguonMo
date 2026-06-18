<?php
require_once(BASE_PATH . '/app/config/database.php');
require_once(BASE_PATH . '/app/models/OrderModel.php');
require_once(BASE_PATH . '/app/models/AdminModel.php');
require_once(BASE_PATH . '/app/helpers/AuthMiddleware.php');

class OrderApiController
{
    private $orderModel;
    private $adminModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->orderModel = new OrderModel($this->db);
        $this->adminModel = new AdminModel($this->db);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // POST /api/order (Đặt hàng từ giỏ hàng)
    public function add()
    {
        header('Content-Type: application/json; charset=utf-8');

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "Dữ liệu JSON đầu vào không hợp lệ."], JSON_UNESCAPED_UNICODE);
            return;
        }

        $name = htmlspecialchars(trim($input['name'] ?? ''), ENT_QUOTES, 'UTF-8');
        $phone = htmlspecialchars(trim($input['phone'] ?? ''), ENT_QUOTES, 'UTF-8');
        $address = htmlspecialchars(trim($input['address'] ?? ''), ENT_QUOTES, 'UTF-8');
        $paymentMethod = htmlspecialchars(trim($input['payment_method'] ?? 'cod'), ENT_QUOTES, 'UTF-8');
        $note = htmlspecialchars(trim($input['note'] ?? ''), ENT_QUOTES, 'UTF-8');

        // Validation thông tin giao nhận
        if (empty($name) || empty($phone) || empty($address)) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "Vui lòng nhập đầy đủ Họ tên, Số điện thoại và Địa chỉ giao hàng."], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Validation giỏ hàng
        $cart = $_SESSION['selected_cart'] ?? $_SESSION['cart'] ?? [];
        if (empty($cart)) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "Giỏ hàng trống. Không thể tiến hành đặt hàng."], JSON_UNESCAPED_UNICODE);
            return;
        }

        $userId = null;
        try {
            $headers = getallheaders();
            $authHeader = $headers['Authorization'] ?? ($headers['authorization'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? ''));
            if (!empty($authHeader)) {
                $userData = AuthMiddleware::isAuthenticated();
                $userId = $userData['id'] ?? null;
            }
        } catch (Exception $e) {
            // Ignore token parsing issues for guests if session/guest checkout is allowed
        }

        if (!$userId && isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
        }

        try {
            $orderId = $this->orderModel->createOrder($name, $phone, $address, $paymentMethod, $note, $cart, $userId);
            
            // Đặt hàng thành công phải làm trống giỏ hàng
            unset($_SESSION['cart'], $_SESSION['selected_cart']);

            echo json_encode([
                "status" => true,
                "message" => "Đặt hàng thành công.",
                "order_id" => $orderId
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => false, "message" => "Lỗi tạo đơn hàng: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    // PUT /api/order/{id} (Admin cập nhật trạng thái đơn hàng)
    // Hoặc POST /api/order/update-status/{id}
    public function updateStatus($id)
    {
        AuthMiddleware::checkAdmin();
        header('Content-Type: application/json; charset=utf-8');

        $id = intval($id);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "Mã đơn hàng không hợp lệ."], JSON_UNESCAPED_UNICODE);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['status'])) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "Thiếu trạng thái cập nhật."], JSON_UNESCAPED_UNICODE);
            return;
        }

        $status = trim($input['status']);
        // Mô phỏng hỗ trợ trạng thái completed, pending, processing, canceled
        if (!in_array($status, ['pending', 'processing', 'completed', 'canceled'])) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "Trạng thái đơn hàng không hợp lệ."], JSON_UNESCAPED_UNICODE);
            return;
        }

        $success = $this->adminModel->updateOrderStatus($id, $status);
        if ($success) {
            echo json_encode(["status" => true, "message" => "Cập nhật trạng thái đơn hàng thành công."], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(["status" => false, "message" => "Cập nhật trạng thái đơn hàng thất bại."], JSON_UNESCAPED_UNICODE);
        }
    }

    // Hàm ánh xạ cho PUT /api/order/{id}
    public function update($id)
    {
        return $this->updateStatus($id);
    }

    // GET /api/order (Lấy danh sách đơn hàng)
    public function index()
    {
        $userData = AuthMiddleware::isAuthenticated();
        $userId = $userData['id'] ?? null;
        $role = $userData['role'] ?? 'user';

        header('Content-Type: application/json; charset=utf-8');

        if ($role === 'admin') {
            // Admin lấy toàn bộ đơn hàng
            $orders = $this->adminModel->getAllOrders();
        } else {
            // User thường chỉ lấy đơn hàng của chính mình
            $orders = $this->orderModel->getOrdersByUserId($userId);
        }

        echo json_encode($orders, JSON_UNESCAPED_UNICODE);
    }

    // GET /api/order/{id} (Chi tiết đơn hàng kèm IDOR protection)
    public function show($id)
    {
        $userData = AuthMiddleware::isAuthenticated();
        $userId = $userData['id'] ?? null;
        $role = $userData['role'] ?? 'user';

        header('Content-Type: application/json; charset=utf-8');

        $id = intval($id);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "Mã đơn hàng không hợp lệ."], JSON_UNESCAPED_UNICODE);
            return;
        }

        $order = $this->adminModel->getOrderById($id);
        if (!$order) {
            http_response_code(404);
            echo json_encode(["status" => false, "message" => "Đơn hàng không tồn tại."], JSON_UNESCAPED_UNICODE);
            return;
        }

        // BẢO MẬT CHỐNG LỖI HỔNG IDOR: Nếu là user thường, kiểm tra xem đơn hàng có phải của chính mình không
        if ($role !== 'admin' && intval($order->user_id ?? 0) !== intval($userId)) {
            http_response_code(403);
            echo json_encode([
                "status" => false,
                "message" => "Forbidden. Bạn không có quyền xem đơn hàng của người khác."
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $details = $this->adminModel->getOrderDetails($id);

        echo json_encode([
            "status" => true,
            "order" => $order,
            "details" => $details
        ], JSON_UNESCAPED_UNICODE);
    }
}
