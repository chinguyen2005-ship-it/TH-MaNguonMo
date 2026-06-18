<?php
class ProductModel
{
    private $conn;
    private $table_name = "product";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Đ- CHỨNG: Đã gộp thành 1 hàm getProducts duy nhất hỗ trợ cả xem tất cả và lọc danh mục
    public function getProducts($category_id = null)
    {
        $query = "SELECT p.id, p.name, p.description, p.price, p.image, c.name as category_name
                  FROM " . $this->table_name . " p
                  LEFT JOIN category c ON p.category_id = c.id";
        
        // Nếu người dùng chọn một danh mục cụ thể, thêm điều kiện lọc WHERE
        if ($category_id !== null && $category_id !== '') {
            $query .= " WHERE p.category_id = :category_id";
        }
        
        $query .= " ORDER BY p.id DESC";
        
        $stmt = $this->conn->prepare($query);
        
        if ($category_id !== null && $category_id !== '') {
            $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // NÂNG CẤP: Dùng JOIN để lấy được tên danh mục cho trang Chi tiết
    public function getProductById($id)
    {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN category c ON p.category_id = c.id 
                  WHERE p.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function addProduct($name, $description, $price, $category_id, $image)
    {
        $errors = [];
        if (empty($name)) $errors['name'] = 'Tên sản phẩm không được để trống';
        if (empty($description)) $errors['description'] = 'Mô tả không được để trống';
        if (!is_numeric($price) || $price < 0) $errors['price'] = 'Giá sản phẩm không hợp lệ';
        
        if (count($errors) > 0) return $errors;

        $query = "INSERT INTO " . $this->table_name . " (name, description, price, category_id, image) 
                  VALUES (:name, :description, :price, :category_id, :image)";
        
        $stmt = $this->conn->prepare($query);

        // Làm sạch dữ liệu
        $name = htmlspecialchars(strip_tags($name));
        $description = htmlspecialchars(strip_tags($description));
        $image = htmlspecialchars(strip_tags($image));

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':image', $image);

        return $stmt->execute();
    }

    public function updateProduct($id, $name, $description, $price, $category_id, $image)
    {
        $query = "UPDATE " . $this->table_name . " SET name=:name, description=:description, price=:price, category_id=:category_id, image=:image WHERE id=:id";
        $stmt = $this->conn->prepare($query);

        $name = htmlspecialchars(strip_tags($name));
        $description = htmlspecialchars(strip_tags($description));
        $image = htmlspecialchars(strip_tags($image));

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':image', $image);

        return $stmt->execute();
    }

    public function deleteProduct($id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Thêm hàm này vào ProductModel.php để tìm kiếm sản phẩm trong database
public function searchProducts($keyword)
{
    $query = "SELECT p.id, p.name, p.description, p.price, p.image, c.name as category_name
              FROM " . $this->table_name . " p
              LEFT JOIN category c ON p.category_id = c.id
              WHERE p.name LIKE :keyword OR p.description LIKE :keyword
              ORDER BY p.id DESC";
              
    $stmt = $this->conn->prepare($query);
    
    // Thêm các ký tự % để tìm kiếm mở rộng (chứa từ khóa là được)
    $searchKeyword = "%" . $keyword . "%";
    $stmt->bindParam(':keyword', $searchKeyword, PDO::PARAM_STR);
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_OBJ);
}

public function getProductsAdvanced($category_id = null, $keyword = null, $sort_by_price = null, $min_price = null, $max_price = null, $limit = null, $offset = null)
{
    $query = "SELECT p.id, p.name, p.description, p.price, p.image, c.name as category_name
              FROM " . $this->table_name . " p
              LEFT JOIN category c ON p.category_id = c.id";
    
    $conditions = [];
    $params = [];
    
    if ($category_id !== null && $category_id !== '') {
        $conditions[] = "p.category_id = :category_id";
        $params[':category_id'] = $category_id;
    }
    
    if ($keyword !== null && $keyword !== '') {
        $conditions[] = "(p.name LIKE :keyword OR p.description LIKE :keyword)";
        $params[':keyword'] = "%" . $keyword . "%";
    }

    if ($min_price !== null && $min_price !== '') {
        $conditions[] = "p.price >= :min_price";
        $params[':min_price'] = $min_price;
    }

    if ($max_price !== null && $max_price !== '') {
        $conditions[] = "p.price <= :max_price";
        $params[':max_price'] = $max_price;
    }
    
    if (count($conditions) > 0) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }
    
    if ($sort_by_price === 'asc') {
        $query .= " ORDER BY p.price ASC";
    } elseif ($sort_by_price === 'desc') {
        $query .= " ORDER BY p.price DESC";
    } else {
        $query .= " ORDER BY p.id DESC";
    }

    if ($limit !== null && $offset !== null) {
        $query .= " LIMIT :limit OFFSET :offset";
    }
    
    $stmt = $this->conn->prepare($query);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }

    if ($limit !== null && $offset !== null) {
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_OBJ);
}

public function countProductsAdvanced($category_id = null, $keyword = null, $min_price = null, $max_price = null)
{
    $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " p";
    
    $conditions = [];
    $params = [];
    
    if ($category_id !== null && $category_id !== '') {
        $conditions[] = "p.category_id = :category_id";
        $params[':category_id'] = $category_id;
    }
    
    if ($keyword !== null && $keyword !== '') {
        $conditions[] = "(p.name LIKE :keyword OR p.description LIKE :keyword)";
        $params[':keyword'] = "%" . $keyword . "%";
    }

    if ($min_price !== null && $min_price !== '') {
        $conditions[] = "p.price >= :min_price";
        $params[':min_price'] = $min_price;
    }

    if ($max_price !== null && $max_price !== '') {
        $conditions[] = "p.price <= :max_price";
        $params[':max_price'] = $max_price;
    }
    
    if (count($conditions) > 0) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $stmt = $this->conn->prepare($query);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_OBJ);
    return $row ? (int)$row->total : 0;
}
}
?>