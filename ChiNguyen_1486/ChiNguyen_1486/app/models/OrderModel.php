<?php
class OrderModel
{
    private $conn;
    private $orderTable = 'orders';
    private $orderDetailsTable = 'order_details';
    private $productTable = 'product';

    public function __construct($db)
    {
        $this->conn = $db;
        $this->initializeDatabase();
    }

    private function initializeDatabase()
    {
        try {
            $this->conn->exec("ALTER TABLE " . $this->orderTable . " ADD COLUMN user_id INT DEFAULT NULL");
        } catch (Exception $e) {
            // Cột có thể đã tồn tại
        }

        try {
            $this->conn->exec("ALTER TABLE " . $this->orderTable . " ADD COLUMN payment_status VARCHAR(50) DEFAULT 'Chưa thanh toán'");
        } catch (Exception $e) {
            // Cột có thể đã tồn tại
        }
    }

    /**
     * Truy vấn giá gốc của sản phẩm từ bảng product.
     * Điều này đảm bảo chúng ta không tin tưởng giá được gửi từ frontend.
     */
    public function getProductPrice($product_id)
    {
        $query = "SELECT price FROM " . $this->productTable . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $price = $stmt->fetchColumn();

        return ($price !== false) ? $price : false;
    }

    /**
     * Tạo đơn hàng mới và lưu chi tiết vào orders + order_details.
     * Dùng transaction để đảm bảo toàn vẹn dữ liệu.
     */
    public function createOrder($name, $phone, $address, $paymentMethod, $note, $cart, $userId = null)
    {
        $this->conn->beginTransaction();

        try {
            $query = "INSERT INTO " . $this->orderTable . " (name, phone, address, status, payment_method, payment_status, note, user_id) " .
                     "VALUES (:name, :phone, :address, 'pending', :payment_method, 'Chưa thanh toán', :note, :user_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':payment_method', $paymentMethod);
            $stmt->bindParam(':note', $note);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $orderId = $this->conn->lastInsertId();

            $detailQuery = "INSERT INTO " . $this->orderDetailsTable . " (order_id, product_id, quantity, price) " .
                           "VALUES (:order_id, :product_id, :quantity, :price)";
            $detailStmt = $this->conn->prepare($detailQuery);

            foreach ($cart as $product_id => $item) {
                $price = $this->getProductPrice($product_id);
                if ($price === false) {
                    throw new Exception("Không tìm thấy sản phẩm với ID {$product_id}.");
                }

                $detailStmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
                $detailStmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
                $detailStmt->bindValue(':quantity', $item['quantity'], PDO::PARAM_INT);
                $detailStmt->bindValue(':price', $price);
                $detailStmt->execute();
            }

            $this->conn->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function getOrdersByUserId($userId)
    {
        $query = "SELECT o.*, COALESCE(SUM(od.quantity * od.price), 0) AS total_amount
                  FROM " . $this->orderTable . " o
                  LEFT JOIN " . $this->orderDetailsTable . " od ON o.id = od.order_id
                  WHERE o.user_id = :user_id
                  GROUP BY o.id
                  ORDER BY o.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getOrderById($id)
    {
        $query = "SELECT * FROM " . $this->orderTable . " WHERE id = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function updatePaymentStatus($id, $paymentMethod, $paymentStatus, $orderStatus)
    {
        $query = "UPDATE " . $this->orderTable . " 
                  SET payment_method = :payment_method, 
                      payment_status = :payment_status, 
                      status = :status 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':payment_method', $paymentMethod);
        $stmt->bindParam(':payment_status', $paymentStatus);
        $stmt->bindParam(':status', $orderStatus);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
