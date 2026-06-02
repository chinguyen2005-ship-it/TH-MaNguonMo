<?php
require_once(BASE_PATH . '/app/config/database.php');
require_once(BASE_PATH . '/app/models/ProductModel.php');
require_once(BASE_PATH . '/app/models/CategoryModel.php');
// Require SessionHelper để thực hiện kiểm tra quyền truy cập
require_once(BASE_PATH . '/app/helpers/SessionHelper.php');

class ProductController
{
    private $productModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->productModel = new ProductModel($this->db);

        // Đảm bảo Session luôn được bật để chạy giỏ hàng cá nhân
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // 1. Danh sách sản phẩm (Dùng index làm trang mặc định) - USER & ADMIN CÓ QUYỀN XEM
    public function index()
    {
       $category_id = $_GET['category_id'] ?? null;

    // Truyền tham số lọc vào Model để lấy danh sách sản phẩm tương ứng
    $products = $this->productModel->getProducts($category_id);

    // Lấy toàn bộ danh mục để hiển thị lên thẻ chọn <select> lọc
    $categories = (new CategoryModel($this->db))->getCategories();

    include BASE_PATH . '/app/views/product/list.php';
    }

    // 2. Xem chi tiết - USER & ADMIN CÓ QUYỀN XEM
    public function show($id)
    {
        $product = $this->productModel->getProductById($id);
        if (!$product) {
            die("Sản phẩm không tồn tại.");
        }
        include BASE_PATH . '/app/views/product/show.php';
    }

    // 3. Form thêm mới - CHỈ ADMIN ĐƯỢC PHÉP TRUY CẬP
    public function add()
    {
        // KIỂM TRA QUYỀN: Nếu không phải Admin, chặn truy cập và chuyển hướng về danh sách
        if (!SessionHelper::isAdmin()) {
            header('Location: /Product');
            exit();
        }

        // Chỉ lấy danh sách danh mục để đổ vào thẻ chọn <select> trong form thêm mới
        $categories = (new CategoryModel($this->db))->getCategories();

        // Nạp giao diện form thêm mới sản phẩm trống
        include BASE_PATH . '/app/views/product/add.php';
    }

    // 4. Form chỉnh sửa sản phẩm - CHỈ ADMIN ĐƯỢF PHÉP TRUY CẬP
    public function edit($id)
    {
        // KIỂM TRA QUYỀN: Bảo vệ endpoint edit sản phẩm khỏi các tài khoản thông thường
        if (!SessionHelper::isAdmin()) {
            header('Location: /Product');
            exit();
        }

        if (empty($id)) {
            die("Thiếu ID sản phẩm cần chỉnh sửa.");
        }

        // Lấy thông tin sản phẩm hiện tại theo ID từ Database để đổ vào form sửa
        $product = $this->productModel->getProductById($id);
        
        // Lấy danh sách danh mục để người dùng có thể chọn lại danh mục nếu muốn
        $categories = (new CategoryModel($this->db))->getCategories();

        // Nếu tìm thấy sản phẩm thì nạp view edit, ngược lại báo lỗi
        if ($product) {
            include BASE_PATH . '/app/views/product/edit.php';
        } else {
            echo "Không tìm thấy sản phẩm cần chỉnh sửa trên hệ thống.";
        }
    }

    // 5. Xử lý lưu sản phẩm mới - CHỈ ADMIN ĐƯỢC PHÉP THỰC THI
    public function save()
    {
        // KIỂM TRA QUYỀN: Chặn gửi request ẩn từ phần mềm bên ngoài (như Postman) khi không có quyền Admin
        if (!SessionHelper::isAdmin()) {
            header('Location: /Product');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Product');
            return;
        }

        try {
            $data = [
                'name'        => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
                'price'       => $_POST['price'] ?? 0,
                'category_id' => $_POST['category_id'] ?? null
            ];

            $image = "";
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $image = $this->uploadImage($_FILES['image']);
            }

            $result = $this->productModel->addProduct($data['name'], $data['description'], $data['price'], $data['category_id'], $image);

            if (is_array($result)) {
                $errors = $result;
                $categories = (new CategoryModel($this->db))->getCategories();
                include BASE_PATH . '/app/views/product/add.php';
            } else {
                header('Location: /Product');
            }
        } catch (Exception $e) {
            die("Lỗi hệ thống: " . $e->getMessage());
        }
    }

    // 6. Xử lý lưu kết quả cập nhật - CHỈ ADMIN ĐƯỢC PHÉP THỰC THI
    public function update()
    {
        // KIỂM TRA QUYỀN: Đảm bảo chỉ tài khoản Admin mới được ghi đè dữ liệu lên Database
        if (!SessionHelper::isAdmin()) {
            header('Location: /Product');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        try {
            $id          = $_POST['id'];
            $image       = $_POST['existing_image'] ?? '';

            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $image = $this->uploadImage($_FILES['image']);
            }

            $success = $this->productModel->updateProduct(
                $id, $_POST['name'], $_POST['description'], 
                $_POST['price'], $_POST['category_id'], $image
            );

            $success ? header('Location: /Product') : die("Lỗi cập nhật dữ liệu.");
        } catch (Exception $e) {
            die("Lỗi: " . $e->getMessage());
        }
    }

    // 7. Xử lý xóa sản phẩm - CHỈ ADMIN ĐƯỢC PHÉP THỰC THI
    public function delete($id)
    {
        // KIỂM TRA QUYỀN: Chặn hành động xóa trái phép từ người dùng thông thường
        if (!SessionHelper::isAdmin()) {
            header('Location: /Product');
            exit();
        }

        $this->productModel->deleteProduct($id);
        header('Location: /Product');
    }

    // Helper: Upload file hình ảnh an toàn
    private function uploadImage($file)
    {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
            throw new Exception("Định dạng file không hỗ trợ.");
        }

        $target_file = $target_dir . uniqid() . '.' . $imageFileType;
        if (!move_uploaded_file($file["tmp_name"], $target_file)) {
            throw new Exception("Không thể di chuyển file upload.");
        }
        return $target_file;
    }

    // =========================================================================
    // PHẦN BỔ SUNG & ĐỒNG BỘ CHUẨN THEO YÊU CẦU BÀI 3 (GIỎ HÀNG & ĐẶT HÀNG)
    // =========================================================================

    // 8. Thêm sản phẩm vào giỏ hàng - USER & ADMIN ĐỀU CÓ QUYỀN THỰC HIỆN
    public function addToCart($id)
    {
        $product = $this->productModel->getProductById($id);
        if (!$product) {
            echo "Không tìm thấy sản phẩm.";
            return;
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity']++;
        } else {
            $_SESSION['cart'][$id] = [
                'name'     => $product->name,
                'price'    => $product->price,
                'quantity' => 1,
                'image'    => $product->image
            ];
        }

        header('Location: /Product/cart');
        exit();
    }

    // 9. Trang hiển thị danh sách giỏ hàng
    public function cart()
    {
        $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
        include BASE_PATH . '/app/views/product/cart.php';
    }

    // 10. Trang điền thông tin đặt hàng (Checkout)
    public function checkout()
    {
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            $_SESSION['checkout_error'] = 'Giỏ hàng trống. Vui lòng thêm sản phẩm trước khi thanh toán.';
            header('Location: /Product/cart');
            exit();
        }

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
        } else {
            $cart = $_SESSION['selected_cart'] ?? ($_SESSION['cart'] ?? []);
        }

        if (empty($cart)) {
            $_SESSION['checkout_error'] = 'Giỏ hàng trống hoặc chưa có sản phẩm được chọn.';
            header('Location: /Product/cart');
            exit();
        }

        header('Location: /Order/checkout');
        exit();
    }

    // 11. Chuyển hướng route xử lý checkout cũ sang OrderController mới
    public function processCheckout()
    {
        header('Location: /Order/processCheckout');
        exit();
    }

    // Thêm vào khu vực quản lý giỏ hàng trong ProductController.php
    public function removeFromCart($id)
{
    // Kiểm tra nếu sản phẩm tồn tại trong giỏ hàng thì loại bỏ khỏi Session
    if (isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
    }
    
    // Xóa xong điều hướng quay trở lại trang giỏ hàng
    header('Location: /Product/cart');
    exit();
}

    // 12. Trang thông báo xác nhận hoàn thành đơn hàng
    public function orderConfirmation()
    {
        include BASE_PATH . '/app/views/product/orderConfirmation.php';
    }

    // Thêm hàm này vào ProductController.php để sửa lỗi Action 'search' không tồn tại
public function search()
{
    // Lấy từ khóa tìm kiếm từ URL, nếu không có mặc định là chuỗi rỗng
    $keyword = $_GET['keyword'] ?? '';

    // Gọi model để lấy danh sách sản phẩm khớp với từ khóa
    $products = $this->productModel->searchProducts($keyword);

    // Lấy danh sách danh mục để giữ cho bộ lọc danh mục ở trang danh sách không bị lỗi
    $categories = (new CategoryModel($this->db))->getCategories();

    // Tái sử dụng view list.php để hiển thị kết quả tìm kiếm một cách đồng bộ
    include BASE_PATH . '/app/views/product/list.php';
}
}