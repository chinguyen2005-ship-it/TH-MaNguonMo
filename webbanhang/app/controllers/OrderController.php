<?php
require_once(BASE_PATH . '/app/config/database.php');
require_once(BASE_PATH . '/app/models/OrderModel.php');

class OrderController
{
    private $orderModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->orderModel = new OrderModel($this->db);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Hiển thị trang checkout.
     * Nếu người dùng chọn sản phẩm từ giỏ hàng, những sản phẩm đó sẽ được lưu vào selected_cart.
     */
    public function checkout()
    {
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            $_SESSION['checkout_error'] = 'Giỏ hàng trống. Vui lòng thêm sản phẩm trước khi thanh toán.';
            header('Location: /Product/cart');
            exit();
        }

        $cart = $_SESSION['selected_cart'] ?? $_SESSION['cart'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $selectedIds = $_POST['selected'] ?? [];
            if (empty($selectedIds) || !is_array($selectedIds)) {
                $_SESSION['checkout_error'] = 'Vui lòng chọn ít nhất một sản phẩm để thanh toán.';
                header('Location: /Product/cart');
                exit();
            }

            $selectedCart = [];
            foreach ($selectedIds as $productId) {
                $productId = intval($productId);
                if (isset($_SESSION['cart'][$productId])) {
                    $selectedCart[$productId] = $_SESSION['cart'][$productId];
                }
            }

            if (empty($selectedCart)) {
                $_SESSION['checkout_error'] = 'Không có sản phẩm hợp lệ trong lựa chọn thanh toán.';
                header('Location: /Product/cart');
                exit();
            }

            $_SESSION['selected_cart'] = $selectedCart;
            $cart = $selectedCart;
        }

        include BASE_PATH . '/app/views/product/checkout.php';
    }

    /**
     * Xử lý dữ liệu khi người dùng nhấn Xác nhận đặt hàng.
     */
    public function processCheckout()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Order/checkout');
            exit();
        }

        $name = htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8');
        $phone = htmlspecialchars(trim($_POST['phone'] ?? ''), ENT_QUOTES, 'UTF-8');
        $address = htmlspecialchars(trim($_POST['address'] ?? ''), ENT_QUOTES, 'UTF-8');
        $paymentMethod = htmlspecialchars(trim($_POST['payment_method'] ?? 'cod'), ENT_QUOTES, 'UTF-8');
        $note = htmlspecialchars(trim($_POST['note'] ?? ''), ENT_QUOTES, 'UTF-8');

        if (empty($name) || empty($phone) || empty($address)) {
            $_SESSION['checkout_error'] = 'Vui lòng điền đầy đủ Họ tên, Số điện thoại và Địa chỉ giao hàng.';
            header('Location: /Order/checkout');
            exit();
        }

        $cart = $_SESSION['selected_cart'] ?? $_SESSION['cart'] ?? [];
        if (empty($cart)) {
            $_SESSION['checkout_error'] = 'Giỏ hàng rỗng. Vui lòng chọn sản phẩm trước khi đặt hàng.';
            header('Location: /Product/cart');
            exit();
        }

        try {
            $orderId = $this->orderModel->createOrder($name, $phone, $address, $paymentMethod, $note, $cart);
            unset($_SESSION['cart'], $_SESSION['selected_cart']);
            header('Location: /Order/thankyou?order_id=' . $orderId);
            exit();
        } catch (Exception $e) {
            $_SESSION['checkout_error'] = 'Không thể tạo đơn hàng: ' . $e->getMessage();
            header('Location: /Product/cart');
            exit();
        }
    }

    /**
     * Trang cảm ơn sau khi tạo đơn hàng thành công.
     */
    public function thankyou()
    {
        include BASE_PATH . '/app/views/order/thankyou.php';
    }

    /**
     * Hiển thị trang đơn hàng của người dùng.
     */
    public function my_orders()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['username'])) {
            header('Location: /Account/login');
            exit();
        }

        $pageTitle = 'Đơn hàng đã đặt';
        include BASE_PATH . '/app/views/order/my_orders.php';
    }
}
