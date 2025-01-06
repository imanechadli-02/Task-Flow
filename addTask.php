<?php
class Database
{
    private $host = "localhost";
    private $username = "root";
    private $password = "12345chadli";
    private $dbname = "taskflow_db";
    protected $conn;

    public function __construct()
    {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->dbname);

        if ($this->conn->connect_error) {
            die("Erreur de connexion : " . $this->conn->connect_error);
        }
    }

    public function getConnection()
    {
        return $this->conn;
    }

    public function closeConnection()
    {
        $this->conn->close();
    }
}

class Task
{
    private $title;
    private $description;
    private $status;
    private $type;
    private $assignedTo;

    public function __construct($title, $description, $status, $type, $assignedTo)
    {
        $this->setTitle($title);
        $this->setDescription($description);
        $this->setStatus($status);
        $this->setType($type);
        $this->setAssignedTo($assignedTo);
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        if (empty($title)) {
            throw new Exception("Le titre est obligatoire.");
        }
        $this->title = htmlspecialchars($title);
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        if (empty($description)) {
            throw new Exception("La description est obligatoire.");
        }
        $this->description = htmlspecialchars($description);
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $validStatuses = ["in_progress", "done", "todo"];
        if (!in_array($status, $validStatuses)) {
            throw new Exception("Statut invalide.");
        }
        $this->status = $status;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $validTypes = ["Bug", "Feature", "Simple"];
        if (!in_array($type, $validTypes)) {
            throw new Exception("Type invalide.");
        }
        $this->type = $type;
    }

    public function getAssignedTo()
    {
        return $this->assignedTo;
    }

    public function setAssignedTo($assignedTo)
    {
        $this->assignedTo = $assignedTo;
    }

    public function save(Database $db)
    {
        $conn = $db->getConnection();
        $stmt = $conn->prepare("INSERT INTO tasks (title, description, status, type, assigned_to) VALUES (?, ?, ?, ?, ?)");

        if ($stmt === false) {
            throw new Exception('Erreur de préparation de la requête : ' . $conn->error);
        }

        $stmt->bind_param("ssssi", $this->title, $this->description, $this->status, $this->type, $this->assignedTo);
        if (!$stmt->execute()) {
            throw new Exception("Erreur lors de l'insertion : " . $stmt->error);
        }

        $stmt->close();
    }
}

class UserManager extends Database
{
    public function getUsers()
    {
        $result = $this->conn->query("SELECT id, username FROM users");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}

$message = "";
try {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $status = $_POST['status'];
        $type = $_POST['type'];
        $userId = intval($_POST['user_id']);

        if ($userId === 0) {
            $userId = null; 
        }

        $db = new Database();
        $task = new Task($title, $description, $status, $type, $userId);
        $task->save($db);
        $db->closeConnection();

        $message = "<p style='color: green;'>Tâche ajoutée avec succès !</p>";
    }
} catch (Exception $e) {
    $message = "<p style='color: red;'>Erreur : " . $e->getMessage() . "</p>";
}

$userManager = new UserManager();
$users = $userManager->getUsers();
$userManager->closeConnection();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow - Ajouter une tâche</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f9;
        }

        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        input[type="text"],
        select,
        button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        button {
            background-color: #007bff;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            border: none;
        }

        button:hover {
            background-color: #0056b3;
        }

        .message {
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h1>Ajouter une Tâche</h1>
        <?php if (!empty($message)) : ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="text" name="title" placeholder="Titre de la tâche" required>
            <input type="text" name="description" placeholder="Description de la tâche" required>
            <select name="status" required>
                <option value="">Sélectionnez un statut</option>
                <option value="in_progress">En cours</option>
                <option value="done">Terminée</option>
                <option value="todo">À faire</option>
            </select>
            <select name="type" required>
                <option value="">Sélectionnez un type</option>
                <option value="Bug">Bug</option>
                <option value="Feature">Fonctionnalité</option>
                <option value="Simple">Simple</option>
            </select>
            <select name="user_id" required>
                <option value="0">N'assigner à personne</option>
                <?php foreach ($users as $user) : ?>
                    <option value="<?php echo $user['id']; ?>"><?php echo $user['username']; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Ajouter</button>
        </form>
    </div>
</body>

</html>
