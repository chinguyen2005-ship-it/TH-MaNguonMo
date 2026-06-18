<?php
class AdminModel {
    private $conn;
    private $orderTable = 'orders';
    private $orderDetailsTable = 'order_details';
    private $productTable = 'product';

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. Thống kê Dashboard
    public function getDashboardStats() {
        $stats = [];

        // Tổng doanh thu (các đơn completed)
        $query = "SELECT SUM(od.quantity * od.price) AS total_revenue 
                  FROM " . $this->orderTable . " o 
                  JOIN " . $this->orderDetailsTable . " od ON o.id = od.order_id 
                  WHERE o.status = 'completed'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_revenue'] = $stmt->fetchColumn() ?: 0;

        // Tổng đơn hàng chờ duyệt (pending)
        $query = "SELECT COUNT(id) AS pending_orders FROM " . $this->orderTable . " WHERE status = 'pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['pending_orders'] = $stmt->fetchColumn() ?: 0;

        // Tổng số sản phẩm
        $query = "SELECT COUNT(id) FROM " . $this->productTable;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_products'] = $stmt->fetchColumn() ?: 0;

        // Tổng số thành viên
        $query = "SELECT COUNT(id) FROM account";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_users'] = $stmt->fetchColumn() ?: 0;

        return $stats;
    }

    // 2. Top 5 sản phẩm bán chạy nhất
    public function getTopSellingProducts() {
        $query = "SELECT p.id, p.name, p.image, p.price, SUM(od.quantity) AS total_sold, SUM(od.quantity * od.price) AS total_revenue
                  FROM " . $this->orderDetailsTable . " od
                  JOIN " . $this->productTable . " p ON od.product_id = p.id
                  JOIN " . $this->orderTable . " o ON od.order_id = o.id
                  WHERE o.status = 'completed'
                  GROUP BY p.id, p.name, p.image, p.price
                  ORDER BY total_sold DESC
                  LIMIT 5";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // 3. Lấy danh sách tất cả đơn hàng
    public function getAllOrders() {
        $query = "SELECT o.*, COALESCE(SUM(od.quantity * od.price), 0) AS total_amount
                  FROM " . $this->orderTable . " o
                  LEFT JOIN " . $this->orderDetailsTable . " od ON o.id = od.order_id
                  GROUP BY o.id
                  ORDER BY o.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // 4. Lấy thông tin chi tiết một đơn hàng
    public function getOrderById($id) {
        $query = "SELECT * FROM " . $this->orderTable . " WHERE id = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // 5. Lấy danh sách sản phẩm trong đơn hàng
    public function getOrderDetails($orderId) {
        $query = "SELECT od.*, p.name AS product_name, p.image AS product_image
                  FROM " . $this->orderDetailsTable . " od
                  JOIN " . $this->productTable . " p ON od.product_id = p.id
                  WHERE od.order_id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // 6. Cập nhật trạng thái đơn hàng
    public function updateOrderStatus($orderId, $status) {
        if (!in_array($status, ['pending', 'processing', 'completed', 'canceled'])) {
            return false;
        }
        $query = "UPDATE " . $this->orderTable . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $orderId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
