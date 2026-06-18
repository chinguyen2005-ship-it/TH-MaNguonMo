<?php
require_once(BASE_PATH . '/app/config/database.php');
require_once(BASE_PATH . '/app/models/CategoryModel.php');
// BẮT BUỘC: Require SessionHelper để thực hiện kiểm tra phân quyền tài khoản
require_once(BASE_PATH . '/app/helpers/SessionHelper.php');

class CategoryController
{
    private $categoryModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->categoryModel = new CategoryModel($this->db);

        // Đảm bảo Session luôn được kích hoạt để kiểm tra quyền trạng thái người dùng
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Hàm mặc định khi gọi /Category - CẢ USER VÀ ADMIN ĐỀU CÓ QUYỀN XEM DANH SÁCH
    public function index()
    {
        if (!SessionHelper::isAdmin()) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['access_error'] = "Không đủ quyền truy cập.";
            header('Location: /Product');
            exit();
        }
        $categories = $this->categoryModel->getCategories();
        include BASE_PATH . '/app/views/Category/list.php';
    }

    // Hiển thị form thêm danh mục - CHỈ ADMIN ĐƯỢC TRUY CẬP
    public function add()
    {
        // KIỂM TRA QUYỀN: Nếu không phải Admin thì chặn lại và đẩy về trang danh sách
        if (!SessionHelper::isAdmin()) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['access_error'] = "Không đủ quyền truy cập.";
            header('Location: /Product');
            exit();
        }

        // ĐÃ SỬA: Đồng bộ đường dẫn tuyệt đối dùng BASE_PATH thay vì __DIR__ tương đối lỗi
        include BASE_PATH . '/app/views/Category/add.php';
    }

    // Xử lý lưu danh mục mới - CHỈ ADMIN ĐƯỢC THỰC THI
    public function save()
    {
        // KIỂM TRA QUYỀN: Chặn các request gửi ẩn trái phép từ bên ngoài nếu không phải Admin
        if (!SessionHelper::isAdmin()) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['access_error'] = "Không đủ quyền truy cập.";
            header('Location: /Product');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            
            if (!empty($name)) {
                $this->categoryModel->addCategory($name, $description);
                header('Location: /Category'); // Đồng bộ chuyển hướng thẳng về mạch /Category gốc
                exit();
            }
        }
        header('Location: /Category/add');
        exit();
    }

    // 1. Hàm hiển thị Form sửa danh mục khi click vào nút "Sửa" - CHỈ ADMIN ĐƯỢC TRUY CẬP
    public function edit($id)
    {
        // KIỂM TRA QUYỀN: Chặn tài khoản thường cố tình gõ link URL /Category/edit/ID
        if (!SessionHelper::isAdmin()) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['access_error'] = "Không đủ quyền truy cập.";
            header('Location: /Product');
            exit();
        }

        // Lấy thông tin danh mục hiện tại từ Database thông qua Model
        $category = $this->categoryModel->getCategoryById($id);

        // Nếu tìm thấy danh mục thì nạp giao diện form sửa, ngược lại báo lỗi
        if ($category) {
            include BASE_PATH . '/app/views/Category/edit.php';
        } else {
            echo "Danh mục không tồn tại trên hệ thống.";
        }
    }

    // 2. Hàm xử lý nhận dữ liệu POST từ Form edit gửi lên để cập nhật vào DB - CHỈ ADMIN ĐƯỢC THỰC THI
    public function update()
    {
        // KIỂM TRA QUYỀN: Đảm bảo tính toàn vẹn dữ liệu, chỉ Admin mới được cập nhật database danh mục
        if (!SessionHelper::isAdmin()) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['access_error'] = "Không đủ quyền truy cập.";
            header('Location: /Product');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $description = $_POST['description'];

            $result = $this->categoryModel->updateCategory($id, $name, $description);

            if ($result) {
                // Cập nhật thành công thì quay về trang danh sách danh mục
                header('Location: /Category');
                exit();
            } else {
                echo "Có lỗi xảy ra khi cập nhật danh mục.";
            }
        }
    }

    // 3. Hàm xử lý xóa danh mục - CHỈ ADMIN ĐƯỢC THỰC THI
    public function delete($id)
    {
        // KIỂM TRA QUYỀN: Ngăn chặn tuyệt đối hành vi gõ link xóa trực tiếp từ tài khoản user thường
        if (!SessionHelper::isAdmin()) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['access_error'] = "Không đủ quyền truy cập.";
            header('Location: /Product');
            exit();
        }

        // Gọi hàm xóa trong model
        $this->categoryModel->deleteCategory($id);
        
        // Xóa xong chuyển hướng về trang danh sách danh mục
        header('Location: /Category');
        exit();
    }
}
?>