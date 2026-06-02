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
    public function createOrder($name, $phone, $address, $paymentMethod, $note, $cart)
    {
        $this->conn->beginTransaction();

        try {
            $query = "INSERT INTO " . $this->orderTable . " (name, phone, address, status, payment_method, note) " .
                     "VALUES (:name, :phone, :address, 'pending', :payment_method, :note)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':payment_method', $paymentMethod);
            $stmt->bindParam(':note', $note);
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
}
