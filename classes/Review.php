<?php
class Review
{
    private $conn;
    private $table_name = "reviews";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getProductReviews($product_id)
    {
        $query = "SELECT r.*, u.username 
                FROM " . $this->table_name . " r
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.product_id = ?
                ORDER BY r.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $product_id);
        $stmt->execute();

        return $stmt;
    }

    public function getAverageRating($product_id)
    {
        $query = "SELECT AVG(rating) as avg_rating 
                FROM " . $this->table_name . "
                WHERE product_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $product_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['avg_rating'] ? round($row['avg_rating'], 1) : 0;
    }
}
