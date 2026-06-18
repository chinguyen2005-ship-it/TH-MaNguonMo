<?php
class CategoryModel
{
    private $conn;
    private $table_name = "category"; // Lưu ý: bảng của bạn tên là "category" (số ít)

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Lấy danh sách danh mục
    public function getCategories()
    {
        $query = "SELECT id, name, description FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Thêm danh mục mới
    public function addCategory($name, $description = "")
    {
        $query = "INSERT INTO " . $this->table_name . " (name, description) VALUES (:name, :description)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            'name' => $name,
            'description' => $description
        ]);
    }

    // Lấy thông tin 1 danh mục (để dùng khi Sửa)
   public function getCategoryById($id)
{
    $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_OBJ); // Trả về dữ liệu dạng Object giống như sản phẩm
}

public function updateCategory($id, $name, $description)
{
    $query = "UPDATE " . $this->table_name . " SET name = :name, description = :description WHERE id = :id";
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':description', $description);

    return $stmt->execute();
}

public function countProductsByCategory($category_id)
{
    $query = "SELECT COUNT(*) FROM product WHERE category_id = :category_id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}

public function deleteCategory($id)
{
    $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id', $id);
    return $stmt->execute();
}
}
?>