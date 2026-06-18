<?php
require_once(BASE_PATH . '/app/config/database.php');
require_once(BASE_PATH . '/app/models/ProductModel.php');
require_once(BASE_PATH . '/app/helpers/AuthMiddleware.php');

class CartApiController
{
    private $productModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->productModel = new ProductModel($this->db);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Tính tổng tiền giỏ hàng helper
    private function calculateTotal()
    {
        $total = 0;
        if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $item) {
                $total += floatval($item['price']) * intval($item['quantity']);
            }
        }
        return $total;
    }

    // GET /api/cart
    public function index()
    {
        header('Content-Type: application/json; charset=utf-8');
        $cart = $_SESSION['cart'] ?? [];
        echo json_encode([
            "status" => true,
            "cart" => $cart,
            "total_price" => $this->calculateTotal()
        ], JSON_UNESCAPED_UNICODE);
    }

    // POST /api/cart (Thêm sản phẩm)
    public function add()
    {
        header('Content-Type: application/json; charset=utf-8');
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "Dữ liệu JSON đầu vào không hợp lệ."], JSON_UNESCAPED_UNICODE);
            return;
        }

        $productId = intval($input['product_id'] ?? 0);
        $quantity = intval($input['quantity'] ?? 1);

        if ($productId <= 0) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "ID sản phẩm không hợp lệ."], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($quantity <= 0) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "Số lượng phải lớn hơn 0."], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Validate sản phẩm phải tồn tại
        $product = $this->productModel->getProductById($productId);
        if (!$product) {
            http_response_code(404);
            echo json_encode(["status" => false, "message" => "Sản phẩm không tồn tại."], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = [
                'name' => $product->name,
                'price' => floatval($product->price),
                'quantity' => $quantity,
                'image' => $product->image
            ];
        }

        echo json_encode([
            "status" => true,
            "message" => "Thêm sản phẩm vào giỏ hàng thành công.",
            "cart" => $_SESSION['cart'],
            "total_price" => $this->calculateTotal()
        ], JSON_UNESCAPED_UNICODE);
    }

    // PUT /api/cart/{id} (Cập nhật số lượng)
    public function update($id)
    {
        header('Content-Type: application/json; charset=utf-8');
        $id = intval($id);

        if (!isset($_SESSION['cart'][$id])) {
            http_response_code(404);
            echo json_encode(["status" => false, "message" => "Sản phẩm không có trong giỏ hàng."], JSON_UNESCAPED_UNICODE);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['quantity'])) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "Thiếu số lượng cần cập nhật."], JSON_UNESCAPED_UNICODE);
            return;
        }

        $quantity = intval($input['quantity']);
        if ($quantity <= 0) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "Số lượng phải lớn hơn 0."], JSON_UNESCAPED_UNICODE);
            return;
        }

        $_SESSION['cart'][$id]['quantity'] = $quantity;

        echo json_encode([
            "status" => true,
            "message" => "Cập nhật giỏ hàng thành công.",
            "cart" => $_SESSION['cart'],
            "total_price" => $this->calculateTotal()
        ], JSON_UNESCAPED_UNICODE);
    }

    // DELETE /api/cart/{id} (Xóa sản phẩm)
    public function delete($id = null)
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($id !== null) {
            $id = intval($id);
            if (isset($_SESSION['cart'][$id])) {
                unset($_SESSION['cart'][$id]);
            }
            echo json_encode([
                "status" => true,
                "message" => "Đã xóa sản phẩm khỏi giỏ hàng.",
                "cart" => $_SESSION['cart'],
                "total_price" => $this->calculateTotal()
            ], JSON_UNESCAPED_UNICODE);
        } else {
            // Clear all cart
            $_SESSION['cart'] = [];
            echo json_encode([
                "status" => true,
                "message" => "Đã làm rỗng giỏ hàng.",
                "cart" => [],
                "total_price" => 0
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
