<?php
// Classe de base pour gérer la base de données
class Database
{
    private $host = "localhost";
    private $username = "root";
    private $password = "12345chadli";
    private $dbname = "taskflow_db";
    protected $conn; // Connexion protégée pour être utilisée dans les classes enfants

    public function __construct()
    {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->dbname);

        if ($this->conn->connect_error) {
            die("Erreur de connexion : " . $this->conn->connect_error);
        }
    }

    public function closeConnection()
    {
        $this->conn->close();
    }
}

// Classe User pour gérer les utilisateurs
class User extends Database
{
    private $id;
    private $username;

    public function setId($id)
    {
        $this->id = intval($id);
    }

    public function getId()
    {
        return $this->id;
    }

    public function setUsername($username)
    {
        $this->username = htmlspecialchars($username);
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getAllUsers()
    {
        $result = $this->conn->query("SELECT id, username FROM users");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}

// Classe Task pour gérer les tâches
class Task extends Database
{
    private $title;
    private $status;
    private $type;
    private $assignedTo;
    private $description;

    public function setTitle($title)
    {
        $this->title = htmlspecialchars($title);
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setStatus($status)
    {
        $this->status = htmlspecialchars($status);
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setType($type)
    {
        $this->type = htmlspecialchars($type);
    }

    public function getType()
    {
        return $this->type;
    }

    public function setAssignedTo($assignedTo)
    {
        $this->assignedTo = intval($assignedTo);
    }

    public function getAssignedTo()
    {
        return $this->assignedTo;
    }

    public function setDescription($description)
    {
        $this->description = htmlspecialchars($description);
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function insert()
    {
        $stmt = $this->conn->prepare("INSERT INTO tasks (title, status, type, assigned_to, description, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssis", $this->title, $this->status, $this->type, $this->assignedTo, $this->description);

        if ($stmt->execute()) {
            return true;
        } else {
            return $stmt->error;
        }
    }

    public function getAllTasksWithUsers()
    {
        $sql = "SELECT tasks.*, users.username AS assigned_name FROM tasks
                LEFT JOIN users ON tasks.assigned_to = users.id";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}

// Fonction pour mapper les valeurs du statut
function getStatusLabel($status)
{
    $statusLabels = [
        'todo' => 'En attente',
        'in_progress' => 'En cours',
        'done' => 'Terminé'
    ];

    return $statusLabels[$status] ?? $status;
}

// Gestion des requêtes
$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $task = new Task();
    $task->setTitle($_POST['title']);
    $task->setDescription($_POST['description']);
    $task->setStatus($_POST['status']);
    $task->setType($_POST['type']);
    $task->setAssignedTo($_POST['assigned_to']);

    if ($task->getTitle() && $task->getDescription() && $task->getStatus() && $task->getType() && $task->getAssignedTo()) {
        $result = $task->insert();

        $message = $result === true
            ? "<p style='color: green;'>Tâche ajoutée avec succès !</p>"
            : "<p style='color: red;'>Erreur : $result</p>";
    } else {
        $message = "<p style='color: red;'>Tous les champs sont requis.</p>";
    }

    $task->closeConnection();
}

// Charger les utilisateurs et les tâches
$user = new User();
$users = $user->getAllUsers();

$task = new Task();
$tasks = $task->getAllTasksWithUsers();
$task->closeConnection();

// Organiser les tâches par statut
$tasksByStatus = ["todo" => [], "in_progress" => [], "done" => []];
foreach ($tasks as $task) {
    $tasksByStatus[$task['status']][] = $task;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow - Tâches</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .columns {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .column {
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            flex: 1;
            margin: 0 10px;
        }

        .column h2 {
            text-align: center;
            color: #007bff;
        }

        .task {
            background: #f9f9f9;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .task strong {
            display: block;
            color: #333;
        }

        .task span {
            color: #555;
        }

        button {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .form-container {
            display: none;
            margin-top: 20px;
        }

        .message {
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Gestion des Tâches</h1>
        <?php if ($message) echo "<div>$message</div>"; ?>
        <a href="addtask.php"><button>Ajouter une nouvelle tâche</button></a>
        <div class="columns">
            <?php foreach ($tasksByStatus as $status => $tasks) : ?>
                <div class="column">
                    <h2><?php echo ucfirst($status); ?></h2>
                    <?php foreach ($tasks as $task) : ?>
                        <div class="task">
                            <strong>
                                <a href="edit_task.php?id=<?php echo $task['id']; ?>" style="text-decoration: none; color: #007bff;">
                                    <?php echo $task['title']; ?>
                                </a>
                            </strong>
                            <p><?php echo $task['description']; ?></p>
                            <span>Assigné à : <?php echo $task['assigned_name']; ?></span>
                            <br>
                            <!-- Ajouter le type de la tâche ici -->
                            <span>Type : <?php echo ucfirst($task['type']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
</body>

</html>
