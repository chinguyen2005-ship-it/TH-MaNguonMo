<?php
require_once(BASE_PATH . '/app/config/database.php');
require_once(BASE_PATH . '/app/models/CategoryModel.php');
require_once(BASE_PATH . '/app/helpers/AuthMiddleware.php');

class CategoryApiController
{
    private $categoryModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->categoryModel = new CategoryModel($this->db);
    }

    // GET /api/category
    public function index()
    {
        header('Content-Type: application/json; charset=utf-8');
        $categories = $this->categoryModel->getCategories();
        echo json_encode($categories, JSON_UNESCAPED_UNICODE);
    }

    // GET /api/category/{id}
    public function show($id)
    {
        header('Content-Type: application/json; charset=utf-8');
        $category = $this->categoryModel->getCategoryById($id);
        if (!$category) {
            http_response_code(404);
            echo json_encode(["status" => false, "message" => "Danh mục không tồn tại."], JSON_UNESCAPED_UNICODE);
            return;
        }
        echo json_encode($category, JSON_UNESCAPED_UNICODE);
    }

    // POST /api/category
    public function add()
    {
        AuthMiddleware::hasRole('admin');
        header('Content-Type: application/json; charset=utf-8');

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "Dữ liệu JSON đầu vào không hợp lệ."], JSON_UNESCAPED_UNICODE);
            return;
        }

        $name = trim($input['name'] ?? '');
        $description = trim($input['description'] ?? '');

        if (empty($name)) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "Tên danh mục không được để trống."], JSON_UNESCAPED_UNICODE);
            return;
        }

        $success = $this->categoryModel->addCategory($name, $description);
        if ($success) {
            http_response_code(201);
            echo json_encode(["status" => true, "message" => "Thêm danh mục thành công."], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(["status" => false, "message" => "Không thể thêm danh mục."], JSON_UNESCAPED_UNICODE);
        }
    }

    // POST /api/category (Store alias)
    public function store()
    {
        AuthMiddleware::hasRole('admin');
        return $this->add();
    }

    // PUT /api/category/{id}
    public function update($id)
    {
        AuthMiddleware::hasRole('admin');
        header('Content-Type: application/json; charset=utf-8');

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "Dữ liệu JSON đầu vào không hợp lệ."], JSON_UNESCAPED_UNICODE);
            return;
        }

        $name = trim($input['name'] ?? '');
        $description = trim($input['description'] ?? '');

        if (empty($name)) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "Tên danh mục không được để trống."], JSON_UNESCAPED_UNICODE);
            return;
        }

        $success = $this->categoryModel->updateCategory($id, $name, $description);
        if ($success) {
            echo json_encode(["status" => true, "message" => "Cập nhật danh mục thành công."], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(["status" => false, "message" => "Không thể cập nhật danh mục."], JSON_UNESCAPED_UNICODE);
        }
    }

    // DELETE /api/category/{id}
    public function delete($id)
    {
        AuthMiddleware::hasRole('admin');
        header('Content-Type: application/json; charset=utf-8');

        // KHÔNG cho phép xóa nếu vẫn còn sản phẩm thuộc danh mục đó
        $productCount = $this->categoryModel->countProductsByCategory($id);
        if ($productCount > 0) {
            http_response_code(400);
            echo json_encode([
                "status" => false, 
                "message" => "Không thể xóa danh mục này vì vẫn còn {$productCount} sản phẩm đang thuộc danh mục."
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $success = $this->categoryModel->deleteCategory($id);
        if ($success) {
            echo json_encode(["status" => true, "message" => "Xóa danh mục thành công."], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(["status" => false, "message" => "Không thể xóa danh mục."], JSON_UNESCAPED_UNICODE);
        }
    }

    // DELETE /api/category/{id} (Alias)
    public function destroy($id)
    {
        AuthMiddleware::hasRole('admin');
        return $this->delete($id);
    }
}
