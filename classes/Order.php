<?php
class Order
{
    private $conn;
    private $table_name = "orders";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getTotalOrders()
    {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getUserOrders($user_id)
    {
        $query = "SELECT * FROM " . $this->table_name . "
                WHERE buyer_id = ? OR seller_id = ?
                ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->bindParam(2, $user_id);
        $stmt->execute();

        return $stmt;
    }

    public function getRecentActivities()
    {
        $query = "SELECT o.*, u.username, p.name as product_name
                FROM " . $this->table_name . " o
                LEFT JOIN users u ON o.buyer_id = u.id
                LEFT JOIN order_items oi ON o.id = oi.order_id
                LEFT JOIN products p ON oi.product_id = p.id
                ORDER BY o.created_at DESC
                LIMIT 10";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function updateStatus($order_id, $new_status)
    {
        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $new_status = htmlspecialchars(strip_tags($new_status));
        $order_id = htmlspecialchars(strip_tags($order_id));

        $stmt->bindParam(":status", $new_status);
        $stmt->bindParam(":id", $order_id);

        return $stmt->execute();
    }

    public function readPaginated($page, $records_per_page)
    {
        $offset = ($page - 1) * $records_per_page;

        $query = "SELECT o.*, u.username as buyer_name, s.username as seller_name
              FROM " . $this->table_name . " o
              LEFT JOIN users u ON o.buyer_id = u.id
              LEFT JOIN users s ON o.seller_id = s.id
              ORDER BY o.created_at DESC
              LIMIT :offset, :records_per_page";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam(":records_per_page", $records_per_page, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function getTotalPages($records_per_page)
    {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ceil($row['total'] / $records_per_page);
    }

    public function getOrderItems($order_id)
    {
        $query = "SELECT oi.*, p.name as product_name
              FROM order_items oi
              LEFT JOIN products p ON oi.product_id = p.id
              WHERE oi.order_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $order_id);
        $stmt->execute();

        return $stmt;
    }

    public function getTotalOrdersBySeller($seller_id)
    {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE seller_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $seller_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getTotalRevenueBySeller($seller_id)
    {
        $query = "SELECT COALESCE(SUM(total_amount), 0) as total FROM " . $this->table_name . " WHERE seller_id = ? AND status = 'completed'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $seller_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getMonthlySalesBySeller($seller_id)
    {
        $query = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COALESCE(SUM(total_amount), 0) as total
              FROM " . $this->table_name . "
              WHERE seller_id = ? AND status = 'completed'
              GROUP BY month
              ORDER BY month DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $seller_id);
        $stmt->execute();

        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['month']] = $row['total'];
        }
        return $result;
    }

    public function getSalesLabels()
    {
        $labels = [];
        for ($i = 6; $i >= 0; $i--) {
            $labels[] = date('d M', strtotime("-$i days"));
        }
        return $labels;
    }

    public function getSalesData()
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $query = "SELECT COALESCE(SUM(total_amount), 0) as total 
                    FROM " . $this->table_name . "
                    WHERE DATE(created_at) = ?";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $date);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $data[] = $row['total'];
        }
        return $data;
    }

    // Methods for Seller Dashboard
    public function getSellerTotalOrders($seller_id)
    {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE seller_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $seller_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getSellerTotalRevenue($seller_id)
    {
        $query = "SELECT COALESCE(SUM(total_amount), 0) as total 
                FROM " . $this->table_name . " 
                WHERE seller_id = ? AND status = 'completed'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $seller_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getSellerRecentOrders($seller_id)
    {
        $query = "SELECT o.*, u.username as customer_name,
                GROUP_CONCAT(p.name SEPARATOR ', ') as products
                FROM " . $this->table_name . " o
                LEFT JOIN users u ON o.buyer_id = u.id
                LEFT JOIN order_items oi ON o.id = oi.order_id
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE o.seller_id = ?
                GROUP BY o.id
                ORDER BY o.created_at DESC
                LIMIT 10";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $seller_id);
        $stmt->execute();
        return $stmt;
    }

    public function getSellerSalesLabels($seller_id)
    {
        $labels = [];
        for ($i = 6; $i >= 0; $i--) {
            $labels[] = date('d M', strtotime("-$i days"));
        }
        return $labels;
    }

    public function getSellerSalesData($seller_id)
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $query = "SELECT COALESCE(SUM(total_amount), 0) as total 
                    FROM " . $this->table_name . "
                    WHERE seller_id = ? AND DATE(created_at) = ?";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $seller_id);
            $stmt->bindParam(2, $date);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $data[] = $row['total'];
        }
        return $data;
    }

    public function getTotalRevenue()
    {
        $query = "SELECT COALESCE(SUM(total_amount), 0) as total 
                FROM " . $this->table_name . "
                WHERE status = 'completed'";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getRecentOrders($limit = 5)
    {
        $query = "SELECT o.*, u.username as customer_name
                FROM " . $this->table_name . " o
                LEFT JOIN users u ON o.buyer_id = u.id
                ORDER BY o.created_at DESC
                LIMIT ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    public function getMonthlySales()
    {
        $query = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
                SUM(total_amount) as total
                FROM " . $this->table_name . "
                WHERE status = 'completed'
                GROUP BY month
                ORDER BY month DESC
                LIMIT 12";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $result = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['month']] = $row['total'];
        }
        return $result;
    }

    public function getCategorySales()
    {
        $query = "SELECT c.name, COUNT(o.id) as total
                FROM " . $this->table_name . " o
                JOIN order_items oi ON o.id = oi.order_id
                JOIN products p ON oi.product_id = p.id
                JOIN categories c ON p.category_id = c.id
                WHERE o.status = 'completed'
                GROUP BY c.id
                ORDER BY total DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $result = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['name']] = $row['total'];
        }
        return $result;
    }

    public function getMonthlyOrderCount($month)
    {
        $query = "SELECT COUNT(*) as total 
                FROM " . $this->table_name . " 
                WHERE DATE_FORMAT(created_at, '%Y-%m') = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $month);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    // Tambahkan juga fungsi untuk seller
    public function getSellerMonthlyOrderCount($seller_id, $month)
    {
        $query = "SELECT COUNT(*) as total 
                FROM " . $this->table_name . " 
                WHERE seller_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $seller_id);
        $stmt->bindParam(2, $month);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getSellerMonthlyRevenue($seller_id, $month)
    {
        $query = "SELECT COALESCE(SUM(total_amount), 0) as total 
                FROM " . $this->table_name . " 
                WHERE seller_id = ? 
                AND DATE_FORMAT(created_at, '%Y-%m') = ? 
                AND status = 'completed'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $seller_id);
        $stmt->bindParam(2, $month);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getSellerTopProducts($seller_id, $limit = 5)
    {
        $query = "SELECT p.name, COUNT(oi.product_id) as total_sold, 
                SUM(oi.quantity * oi.price) as total_revenue
                FROM " . $this->table_name . " o
                JOIN order_items oi ON o.id = oi.order_id
                JOIN products p ON oi.product_id = p.id
                WHERE o.seller_id = ? AND o.status = 'completed'
                GROUP BY p.id
                ORDER BY total_sold DESC
                LIMIT ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $seller_id);
        $stmt->bindParam(2, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function getSellerOrdersByStatus($seller_id)
    {
        $query = "SELECT status, COUNT(*) as total
                FROM " . $this->table_name . "
                WHERE seller_id = ?
                GROUP BY status";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $seller_id);
        $stmt->execute();

        return $stmt;
    }
}
