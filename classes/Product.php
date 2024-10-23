<?php
class Product
{
    private $conn;
    private $table_name = "products";

    public $id;
    public $seller_id;
    public $category_id;
    public $name;
    public $description;
    public $price;
    public $stock;
    public $image;
    public $created_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    seller_id = :seller_id,
                    category_id = :category_id,
                    name = :name,
                    description = :description,
                    price = :price,
                    stock = :stock,
                    image = :image";

        $stmt = $this->conn->prepare($query);

        $this->seller_id = htmlspecialchars(strip_tags($this->seller_id));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->stock = htmlspecialchars(strip_tags($this->stock));
        $this->image = htmlspecialchars(strip_tags($this->image));

        $stmt->bindParam(":seller_id", $this->seller_id);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":stock", $this->stock);
        $stmt->bindParam(":image", $this->image);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Add new methods for filtered products
    public function readPaginatedFiltered($page = 1, $records_per_page = 8, $category_id = null, $search = '', $sort = 'newest')
    {
        $offset = ($page - 1) * $records_per_page;

        $query = "SELECT p.*, c.name as category_name, u.username as seller_name, u.phone as seller_phone
                FROM " . $this->table_name . " p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN users u ON p.seller_id = u.id
                WHERE 1=1";

        if ($category_id) {
            $query .= " AND p.category_id = :category_id";
        }

        if ($search) {
            $query .= " AND (p.name LIKE :search OR p.description LIKE :search)";
        }

        // Add sorting
        switch ($sort) {
            case 'price_low':
                $query .= " ORDER BY p.price ASC";
                break;
            case 'price_high':
                $query .= " ORDER BY p.price DESC";
                break;
            default:
                $query .= " ORDER BY p.created_at DESC";
        }

        $query .= " LIMIT :offset, :records_per_page";

        $stmt = $this->conn->prepare($query);

        if ($category_id) {
            $stmt->bindParam(":category_id", $category_id);
        }

        if ($search) {
            $search = "%{$search}%";
            $stmt->bindParam(":search", $search);
        }

        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam(":records_per_page", $records_per_page, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function getTotalPagesFiltered($records_per_page = 8, $category_id = null, $search = '')
    {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " p WHERE 1=1";

        if ($category_id) {
            $query .= " AND p.category_id = :category_id";
        }

        if ($search) {
            $query .= " AND (p.name LIKE :search OR p.description LIKE :search)";
        }

        $stmt = $this->conn->prepare($query);

        if ($category_id) {
            $stmt->bindParam(":category_id", $category_id);
        }

        if ($search) {
            $search = "%{$search}%";
            $stmt->bindParam(":search", $search);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ceil($row['total'] / $records_per_page);
    }

    public function read()
    {
        $query = "SELECT p.*, c.name as category_name, u.username as seller_name, u.phone as seller_phone
                FROM " . $this->table_name . " p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN users u ON p.seller_id = u.id
                ORDER BY p.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function readOne()
    {
        $query = "SELECT p.*, c.name as category_name, u.username as seller_name, u.phone as seller_phone
                FROM " . $this->table_name . " p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN users u ON p.seller_id = u.id
                WHERE p.id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getFeatured()
    {
        $query = "SELECT p.*, c.name as category_name, u.username as seller_name
                FROM " . $this->table_name . " p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN users u ON p.seller_id = u.id
                WHERE p.featured = 1
                ORDER BY p.created_at DESC
                LIMIT 4";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }
}
