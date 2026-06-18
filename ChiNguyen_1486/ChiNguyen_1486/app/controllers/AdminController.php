<?php
require_once(BASE_PATH . '/app/config/database.php');
require_once(BASE_PATH . '/app/models/AdminModel.php');
require_once(BASE_PATH . '/app/helpers/SessionHelper.php');

class AdminController {
    private $adminModel;
    private $db;

    public function __construct() {
        // Bảo vệ tất cả các Action quản trị: Chỉ cho phép Admin truy cập
        if (!SessionHelper::isAdmin()) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['access_error'] = "Không đủ quyền truy cập.";
            header('Location: /Product');
            exit();
        }

        $this->db = (new Database())->getConnection();
        $this->adminModel = new AdminModel($this->db);
    }

    // 1. Trang tổng quan Dashboard
    public function index() {
        $stats = $this->adminModel->getDashboardStats();
        $topProducts = $this->adminModel->getTopSellingProducts();
        
        $pageTitle = 'Dashboard Quản trị';
        include_once BASE_PATH . '/app/views/admin/dashboard.php';
    }

    // 2. Danh sách đơn hàng toàn hệ thống
    public function orders() {
        $orders = $this->adminModel->getAllOrders();
        
        $pageTitle = 'Quản lý đơn hàng';
        include_once BASE_PATH . '/app/views/admin/orders.php';
    }

    // 3. Chi tiết đơn hàng
    public function orderDetail($id) {
        $id = intval($id);
        if ($id <= 0) {
            die("Mã đơn hàng không hợp lệ.");
        }

        $order = $this->adminModel->getOrderById($id);
        if (!$order) {
            die("Đơn hàng không tồn tại trên hệ thống.");
        }

        $items = $this->adminModel->getOrderDetails($id);
        
        $pageTitle = 'Chi tiết đơn hàng #' . $id;
        include_once BASE_PATH . '/app/views/admin/order_detail.php';
    }

    // 4. Cập nhật trạng thái đơn hàng
    public function updateOrderStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $orderId = intval($_POST['order_id'] ?? 0);
            $status = trim($_POST['status'] ?? '');

            if ($orderId <= 0 || empty($status)) {
                die("Dữ liệu đầu vào không hợp lệ.");
            }

            $success = $this->adminModel->updateOrderStatus($orderId, $status);
            
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            if ($success) {
                $_SESSION['admin_order_success'] = "Cập nhật trạng thái đơn hàng #{$orderId} thành công!";
            } else {
                $_SESSION['admin_order_error'] = "Cập nhật thất bại. Trạng thái không hợp lệ.";
            }

            header('Location: /Admin/orderDetail/' . $orderId);
            exit();
        }
    }
}
