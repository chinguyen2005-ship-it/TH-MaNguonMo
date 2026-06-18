<?php
require_once(BASE_PATH . '/app/config/database.php');
require_once(BASE_PATH . '/app/models/OrderModel.php');
require_once(BASE_PATH . '/app/helpers/AuthMiddleware.php');

class PaymentApiController
{
    private $orderModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->orderModel = new OrderModel($this->db);
    }

    /**
     * POST /api/payment/create-payment
     * POST /api/payment
     * Xử lý tạo thanh toán cho đơn hàng
     */
    public function createPayment()
    {
        header('Content-Type: application/json; charset=utf-8');

        // Bước 1 (Xác thực quyền): Đảm bảo người dùng đã đăng nhập bằng cách giải mã Token JWT qua AuthMiddleware
        // Hàm isAuthenticated() sẽ tự động gọi JWTHelper::decodeToken() và trả về thông tin user
        $userData = AuthMiddleware::isAuthenticated();

        // Nhận dữ liệu đầu vào JSON
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $order_id = isset($input['order_id']) ? intval($input['order_id']) : 0;
        $payment_method = isset($input['payment_method']) ? strtoupper(trim($input['payment_method'])) : '';

        if ($order_id <= 0 || empty($payment_method)) {
            http_response_code(400);
            echo json_encode([
                "status" => false, 
                "message" => "Lỗi! Thiếu mã đơn hàng (order_id) hoặc phương thức thanh toán (payment_method)."
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Bước 2 (Kiểm tra đơn hàng): Tìm kiếm đơn hàng theo order_id
        $order = $this->orderModel->getOrderById($order_id);
        if (!$order) {
            http_response_code(404);
            echo json_encode([
                "status" => false, 
                "message" => "Đơn hàng không tồn tại."
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Bước 3 (Chặn thanh toán trùng - Tiêu chí cốt lõi):
        // Nếu cột payment_status của đơn hàng đó đã là 'Đã thanh toán', lập tức ngắt chương trình
        if (isset($order->payment_status) && $order->payment_status === 'Đã thanh toán') {
            http_response_code(400);
            echo json_encode([
                "status" => false,
                "message" => "Lỗi! Không cho phép thanh toán lại đơn hàng đã thanh toán."
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Bước 4 (Xử lý theo phương thức):
        if ($payment_method === 'COD') {
            // COD: Thanh toán khi nhận hàng
            $payment_status = 'Chưa thanh toán';
            $order_status = 'processing'; // Cập nhật trạng thái đơn là 'Đang xử lý'
        } elseif ($payment_method === 'BANKING') {
            // BANKING: Mô phỏng chuyển khoản ví điện tử thành công
            $payment_status = 'Đã thanh toán';
            $order_status = 'processing'; // Cập nhật trạng thái đơn là 'Đang xử lý'
        } else {
            http_response_code(400);
            echo json_encode([
                "status" => false,
                "message" => "Lỗi! Phương thức thanh toán không hợp lệ (Chỉ hỗ trợ COD hoặc BANKING)."
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Bước 5 (Phản hồi): Lưu thay đổi vào database
        $success = $this->orderModel->updatePaymentStatus($order_id, $payment_method, $payment_status, $order_status);

        if ($success) {
            http_response_code(200);
            echo json_encode([
                "status" => true,
                "message" => "Xử lý thông tin thanh toán đơn hàng thành công.",
                "data" => [
                    "order_id" => $order_id,
                    "payment_method" => $payment_method,
                    "payment_status" => $payment_status,
                    "order_status" => $order_status
                ]
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => false,
                "message" => "Lỗi! Không thể cập nhật trạng thái thanh toán của đơn hàng vào database."
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // POST /api/payment (Bí danh nhận POST trực tiếp từ định tuyến chính)
    public function add()
    {
        return $this->createPayment();
    }
}
