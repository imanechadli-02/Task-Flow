<?php
class Database
{
    private $host = "localhost";
    private $username = "root";
    private $password = "12345chadli"; // Modifiez selon votre configuration
    private $dbname = "taskflow_db";
    private $conn;

    public function __construct()
    {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->dbname);

        if ($this->conn->connect_error) {
            die("Erreur de connexion : " . $this->conn->connect_error);
        }
    }

    public function insertTask($title, $status, $type, $assignedTo, $description)
    {
        $stmt = $this->conn->prepare("INSERT INTO tasks (title, status, type, assigned_to, description, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssis", $title, $status, $type, $assignedTo, $description);

        if ($stmt->execute()) {
            return true;
        } else {
            return $stmt->error;
        }
    }

    public function getUsers()
    {
        $result = $this->conn->query("SELECT id, username FROM users");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getTasksWithUsers()
    {
        $sql = "SELECT tasks.*, users.username AS assigned_name FROM tasks
                LEFT JOIN users ON tasks.assigned_to = users.id";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function closeConnection()
    {
        $this->conn->close();
    }
}


?>