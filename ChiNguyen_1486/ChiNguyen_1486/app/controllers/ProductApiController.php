<?php
require_once(BASE_PATH . '/app/config/database.php');
require_once(BASE_PATH . '/app/models/ProductModel.php');
// Nạp AuthMiddleware phục vụ kiểm tra token quyền
require_once(BASE_PATH . '/app/helpers/AuthMiddleware.php');

class ProductApiController
{
    private $productModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->productModel = new ProductModel($this->db);

        // Chặn lọc tập trung bằng AuthMiddleware tại hàm khởi tạo (constructor)
        $method = $_SERVER['REQUEST_METHOD'];
        $url = $_GET['url'] ?? '';

        // Bỏ qua xác thực cho các api login
        if (strpos($url, 'product/login') === false) {
            if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
                AuthMiddleware::hasRole('admin');
            } else {
                AuthMiddleware::isAuthenticated();
            }
        }
    }

    // GET /api/product
    // Tiếp nhận lọc theo khoảng giá, phân trang & trả về cấu trúc dữ liệu phân đoạn
    public function index()
    {
        header('Content-Type: application/json; charset=utf-8');

        $category_id = $_GET['category_id'] ?? null;
        $keyword = $_GET['keyword'] ?? null;
        $sort_by_price = $_GET['sort_by_price'] ?? null;
        $min_price = $_GET['min_price'] ?? null;
        $max_price = $_GET['max_price'] ?? null;

        // Tiếp nhận tham số phân trang
        $page = isset($_GET['page']) ? intval($_GET['page']) : null;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : null;
        $offset = null;

        if ($page !== null && $limit !== null) {
            if ($page < 1) $page = 1;
            if ($limit < 1) $limit = 10;
            $offset = ($page - 1) * $limit;
        }

        // Lấy danh sách sản phẩm theo bộ lọc nâng cao
        $products = $this->productModel->getProductsAdvanced($category_id, $keyword, $sort_by_price, $min_price, $max_price, $limit, $offset);
        
        // Đếm tổng số sản phẩm để tính tổng trang
        $totalItems = $this->productModel->countProductsAdvanced($category_id, $keyword, $min_price, $max_price);
        $totalPages = ($limit !== null) ? ceil($totalItems / $limit) : 1;

        $outputProducts = [];
        foreach ($products as $p) {
            $imagePath = !empty($p->image) ? $p->image : 'uploads/default.png';
            $outputProducts[] = [
                'id' => $p->id,
                'name' => $p->name,
                'description' => $p->description,
                'price' => floatval($p->price),
                'category_id' => isset($p->category_id) ? intval($p->category_id) : null,
                'category_name' => $p->category_name ?? 'Chưa phân loại',
                'image' => $imagePath
            ];
        }

        // Nếu có tham số phân trang, trả về kèm metadata phân trang
        if ($page !== null && $limit !== null) {
            echo json_encode([
                "status" => true,
                "page" => $page,
                "limit" => $limit,
                "total_items" => $totalItems,
                "total_pages" => $totalPages,
                "data" => $outputProducts
            ], JSON_UNESCAPED_UNICODE);
        } else {
            // Trả về trực tiếp mảng để đảm bảo khả năng tương thích ngược của mã cũ
            echo json_encode($outputProducts, JSON_UNESCAPED_UNICODE);
        }
    }

    // GET /api/product/{id}
    public function show($id)
    {
        header('Content-Type: application/json; charset=utf-8');

        $product = $this->productModel->getProductById($id);
        if (!$product) {
            http_response_code(404);
            echo json_encode(["message" => "Sản phẩm không tồn tại."], JSON_UNESCAPED_UNICODE);
            return;
        }

        $imagePath = !empty($product->image) ? $product->image : 'uploads/default.png';
        $output = [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => floatval($product->price),
            'category_id' => isset($product->category_id) ? intval($product->category_id) : null,
            'category_name' => $product->category_name ?? 'Chưa phân loại',
            'image' => $imagePath
        ];

        echo json_encode($output, JSON_UNESCAPED_UNICODE);
    }

    // POST /api/product (Thêm mới sản phẩm, hỗ trợ upload ảnh qua API)
    public function add()
    {
        AuthMiddleware::hasRole('admin');
        $this->store();
    }

    public function store()
    {
        AuthMiddleware::hasRole('admin');
        header('Content-Type: application/json; charset=utf-8');

        // Đọc dữ liệu JSON hoặc form POST
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $name = trim($input['name'] ?? '');
        $description = trim($input['description'] ?? '');
        $price = $input['price'] ?? 0;
        $category_id = $input['category_id'] ?? null;

        if (empty($name)) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "Tên sản phẩm không được để trống."], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!is_numeric($price) || floatval($price) <= 0) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "Giá sản phẩm phải là số và lớn hơn 0."], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Xử lý Upload Ảnh qua API
        $image = 'uploads/default.png';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'png', 'jpeg'])) {
                $targetDir = BASE_PATH . '/uploads/';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                $fileName = uniqid() . '.' . $ext;
                $targetFile = $targetDir . $fileName;
                if (move_uploaded_file($file['tmp_name'], $targetFile) || copy($file['tmp_name'], $targetFile)) {
                    $image = 'uploads/' . $fileName;
                }
            } else {
                http_response_code(400);
                echo json_encode(["status" => false, "message" => "Định dạng file không hợp lệ. Chỉ chấp nhận JPG, PNG, JPEG."], JSON_UNESCAPED_UNICODE);
                return;
            }
        }

        $result = $this->productModel->addProduct($name, $description, $price, $category_id, $image);

        if (is_array($result)) {
            http_response_code(400);
            echo json_encode(["errors" => $result], JSON_UNESCAPED_UNICODE);
        } elseif ($result) {
            http_response_code(201);
            echo json_encode(["status" => true, "message" => "Thêm sản phẩm thành công.", "image_url" => $image], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(["status" => false, "message" => "Không thể thêm sản phẩm."], JSON_UNESCAPED_UNICODE);
        }
    }

    // PUT /api/product/{id} (Cập nhật sản phẩm, hỗ trợ upload ảnh qua API)
    public function update($id)
    {
        AuthMiddleware::hasRole('admin');
        header('Content-Type: application/json; charset=utf-8');

        // Phân tích dữ liệu PUT request (bao gồm file upload và các trường)
        $input = $this->parsePutRequest();

        $name = trim($input['name'] ?? '');
        $description = trim($input['description'] ?? '');
        $price = $input['price'] ?? 0;
        $category_id = $input['category_id'] ?? null;

        if (empty($name)) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "Tên sản phẩm không được để trống."], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!is_numeric($price) || floatval($price) <= 0) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "Giá sản phẩm phải là số và lớn hơn 0."], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Lấy thông tin ảnh cũ
        $existing = $this->productModel->getProductById($id);
        $image = $existing ? $existing->image : 'uploads/default.png';

        // Xử lý Upload Ảnh qua API nếu có gửi tệp mới
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'png', 'jpeg'])) {
                $targetDir = BASE_PATH . '/uploads/';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                $fileName = uniqid() . '.' . $ext;
                $targetFile = $targetDir . $fileName;
                if (move_uploaded_file($file['tmp_name'], $targetFile) || copy($file['tmp_name'], $targetFile)) {
                    $image = 'uploads/' . $fileName;
                }
            } else {
                http_response_code(400);
                echo json_encode(["status" => false, "message" => "Định dạng file không hợp lệ. Chỉ chấp nhận JPG, PNG, JPEG."], JSON_UNESCAPED_UNICODE);
                return;
            }
        }

        $success = $this->productModel->updateProduct($id, $name, $description, $price, $category_id, $image);

        if ($success) {
            echo json_encode(["status" => true, "message" => "Cập nhật sản phẩm thành công.", "image_url" => $image], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(["status" => false, "message" => "Không thể cập nhật sản phẩm."], JSON_UNESCAPED_UNICODE);
        }
    }

    // DELETE /api/product/{id}
    public function delete($id)
    {
        AuthMiddleware::hasRole('admin');
        header('Content-Type: application/json; charset=utf-8');

        $success = $this->productModel->deleteProduct($id);

        if ($success) {
            echo json_encode(["message" => "Xóa sản phẩm thành công."], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Không thể xóa sản phẩm."], JSON_UNESCAPED_UNICODE);
        }
    }

    // DELETE /api/product/{id} (Alias)
    public function destroy($id)
    {
        AuthMiddleware::hasRole('admin');
        return $this->delete($id);
    }

    // POST /api/product/login
    public function apiLogin()
    {
        header('Content-Type: application/json; charset=utf-8');

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
        } else {
            $username = trim($input['username'] ?? '');
            $password = $input['password'] ?? '';
        }

        if (empty($username) || empty($password)) {
            http_response_code(400);
            echo json_encode([
                "status" => false, 
                "message" => "Vui lòng nhập đầy đủ tên đăng nhập (hoặc email) và mật khẩu."
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        require_once(BASE_PATH . '/app/models/AccountModel.php');
        $accountModel = new AccountModel($this->db);

        $account = $accountModel->getAccountByUsername($username);
        if (!$account) {
            $account = $accountModel->getAccountByEmail($username);
        }

        if ($account && password_verify($password, $account->password)) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_id'] = $account->id;
            $_SESSION['username'] = $account->username;
            $_SESSION['fullname'] = $account->fullname;
            $_SESSION['email'] = $account->email;
            $_SESSION['role'] = $account->role;
            $_SESSION['user_role'] = $account->role;
            $_SESSION['avatar'] = $account->avatar;

            require_once(BASE_PATH . '/app/helpers/JWTHelper.php');
            $token = JWTHelper::generateToken($account);
            $_SESSION['token'] = $token;

            http_response_code(200);
            echo json_encode([
                "status" => true,
                "message" => "Login successful",
                "token" => $token
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(401);
            echo json_encode([
                "status" => false, 
                "message" => "Tên đăng nhập hoặc mật khẩu không chính xác."
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // Helper giải mã Multipart/form-data cho PUT request
    private function parsePutRequest()
    {
        $input = file_get_contents('php://input');
        $contentType = $_SERVER['CONTENT_TYPE'] ?? ($_SERVER['HTTP_CONTENT_TYPE'] ?? '');
        
        if (preg_match('/boundary=(.*)$/', $contentType, $matches)) {
            $boundary = $matches[1];
            $blocks = preg_split("/-+$boundary/", $input);
            array_pop($blocks); // remove last empty block
            
            $data = [];
            foreach ($blocks as $id => $block) {
                if (empty($block)) continue;
                
                if (strpos($block, 'application/octet-stream') !== false || strpos($block, 'filename=') !== false) {
                    preg_match('/name=\"([^\"]*)\"; filename=\"([^\"]*)\"/', $block, $matchesName);
                    preg_match('/Content-Type: ([^\s]*)/', $block, $matchesType);
                    
                    $name = $matchesName[1] ?? '';
                    $filename = $matchesName[2] ?? '';
                    $type = $matchesType[1] ?? '';
                    
                    $pos = strpos($block, "\r\n\r\n");
                    if ($pos !== false) {
                        $content = substr($block, $pos + 4);
                        $content = substr($content, 0, -2);
                        
                        $tmpPath = tempnam(sys_get_temp_dir(), 'php_put_upload');
                        file_put_contents($tmpPath, $content);
                        
                        $_FILES[$name] = [
                            'name' => $filename,
                            'type' => $type,
                            'tmp_name' => $tmpPath,
                            'error' => UPLOAD_ERR_OK,
                            'size' => strlen($content)
                        ];
                    }
                } else {
                    preg_match('/name=\"([^\"]*)\"/', $block, $matches);
                    $name = $matches[1] ?? '';
                    $pos = strpos($block, "\r\n\r\n");
                    if ($pos !== false) {
                        $val = substr($block, $pos + 4);
                        $val = substr($val, 0, -2);
                        $data[$name] = $val;
                    }
                }
            }
            return $data;
        }
        
        $json = json_decode($input, true);
        if ($json) return $json;
        
        parse_str($input, $data);
        return $data;
    }
}
