<?php
class Settings
{
    private $conn;
    private $table_name = "settings";

    public $site_name;
    public $site_description;
    public $contact_email;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getSettings()
    {
        $query = "SELECT * FROM " . $this->table_name . " LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $row;
        }
        // Return default settings if none exist
        return [
            'site_name' => '',
            'site_description' => '',
            'contact_email' => ''
        ];
    }

    public function update()
    {
        // Check if settings exist
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row['total'] > 0) {
            // Update existing settings
            $query = "UPDATE " . $this->table_name . "
                    SET site_name = :site_name,
                        site_description = :site_description,
                        contact_email = :contact_email";

            $stmt = $this->conn->prepare($query);

            $this->site_name = htmlspecialchars(strip_tags($this->site_name));
            $this->site_description = htmlspecialchars(strip_tags($this->site_description));
            $this->contact_email = htmlspecialchars(strip_tags($this->contact_email));

            $stmt->bindParam(":site_name", $this->site_name);
            $stmt->bindParam(":site_description", $this->site_description);
            $stmt->bindParam(":contact_email", $this->contact_email);

            if ($stmt->execute()) {
                return true;
            }
        } else {
            // Insert new settings
            $query = "INSERT INTO " . $this->table_name . "
                    SET site_name = :site_name,
                        site_description = :site_description,
                        contact_email = :contact_email";

            $stmt = $this->conn->prepare($query);

            $this->site_name = htmlspecialchars(strip_tags($this->site_name));
            $this->site_description = htmlspecialchars(strip_tags($this->site_description));
            $this->contact_email = htmlspecialchars(strip_tags($this->contact_email));

            $stmt->bindParam(":site_name", $this->site_name);
            $stmt->bindParam(":site_description", $this->site_description);
            $stmt->bindParam(":contact_email", $this->contact_email);

            if ($stmt->execute()) {
                return true;
            }
        }
        return false;
    }
}
