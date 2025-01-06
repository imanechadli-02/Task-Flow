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

    public function closeConnection()
    {
        $this->conn->close();
    }
}


class Task extends Database
{
    private $id;
    private $title;
    private $status;
    private $type;
    private $assignedTo;
    private $description;

   

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setAssignedTo($assignedTo)
    {
        $this->assignedTo = $assignedTo;
    }

    public function getAssignedTo()
    {
        return $this->assignedTo;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function fetchTaskById($id)
    {
        $this->setId($id);
        $stmt = $this->conn->prepare("SELECT tasks.*, users.username AS assigned_name FROM tasks 
                                      LEFT JOIN users ON tasks.assigned_to = users.id WHERE tasks.id = ?");
        $stmt->bind_param("i", $this->id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function updateTask()
    {
        $stmt = $this->conn->prepare("UPDATE tasks SET title = ?, status = ?, type = ?, assigned_to = ?, description = ? WHERE id = ?");
        $stmt->bind_param("sssisi", $this->title, $this->status, $this->type, $this->assignedTo, $this->description, $this->id);
        return $stmt->execute();
    }

    public function deleteTask()
    {
        $stmt = $this->conn->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->bind_param("i", $this->id);
        return $stmt->execute();
    }
}

class User extends Database
{
    public function fetchAllUsers()
    {
        $result = $this->conn->query("SELECT id, username FROM users");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}

if (isset($_GET['id'])) {
    $taskId = intval($_GET['id']);
    $taskManager = new Task();
    $userManager = new User();

    $taskData = $taskManager->fetchTaskById($taskId);
    $users = $userManager->fetchAllUsers();

    if (!$taskData) {
        die("Tâche non trouvée");
    }

    $taskManager->setId($taskData['id']);
    $taskManager->setTitle($taskData['title']);
    $taskManager->setStatus($taskData['status']);
    $taskManager->setType($taskData['type']);
    $taskManager->setAssignedTo($taskData['assigned_to']);
    $taskManager->setDescription($taskData['description']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_task'])) {
        $taskManager->setTitle($_POST['title']);
        $taskManager->setDescription($_POST['description']);
        $taskManager->setStatus($_POST['status']);
        $taskManager->setType($_POST['type']);
        $taskManager->setAssignedTo($_POST['assigned_to']);

        if ($taskManager->updateTask()) {
            echo "<script>
                    alert('Tâche mise à jour avec succès !');
                    window.location.href = 'affichertasks.php';
                  </script>";
        } else {
            echo "<p style='color: red;'>Erreur lors de la mise à jour de la tâche.</p>";
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_task'])) {
        if ($taskManager->deleteTask()) {
            echo "<script>
                    alert('Tâche supprimée avec succès !');
                    window.location.href = 'affichertasks.php';
                  </script>";
        } else {
            echo "<p style='color: red;'>Erreur lors de la suppression de la tâche.</p>";
        }
    }
} else {
    die("ID de tâche manquant.");
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Éditer la Tâche</title>
    <style>
        <style>body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input,
        textarea,
        select,
        button {
            font-size: 16px;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            outline: none;
        }

        input:focus,
        textarea:focus,
        select:focus,
        button:focus {
            border-color: #0056b3;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        select {
            cursor: pointer;
        }

        button {
            background-color: #0056b3;
            color: white;
            border: none;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #003d7a;
        }

        p {
            text-align: center;
            font-weight: bold;
        }

        .error {
            color: red;
        }

        .success {
            color: green;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Éditer la Tâche</h1>

        <form method="POST" action="">
            <input type="text" name="title" value="<?php echo $taskManager->getTitle(); ?>" placeholder="Titre de la tâche" required>
            <textarea name="description" placeholder="Description de la tâche" required><?php echo $taskManager->getDescription(); ?></textarea>
            <select name="status" required>
                <option value="todo" <?php echo $taskManager->getStatus() === 'todo' ? 'selected' : ''; ?>>En attente</option>
                <option value="in_progress" <?php echo $taskManager->getStatus() === 'in_progress' ? 'selected' : ''; ?>>En cours</option>
                <option value="done" <?php echo $taskManager->getStatus() === 'done' ? 'selected' : ''; ?>>Terminé</option>
            </select>
            <select name="type" required>
                <option value="bug" <?php echo $taskManager->getType() === 'bug' ? 'selected' : ''; ?>>Bug</option>
                <option value="feature" <?php echo $taskManager->getType() === 'feature' ? 'selected' : ''; ?>>Feature</option>
                <option value="simple" <?php echo $taskManager->getType() === 'simple' ? 'selected' : ''; ?>>Simple</option>
            </select>
            <select name="assigned_to" required>
                <option value="">Assigné à</option>
                <?php foreach ($users as $user) : ?>
                    <option value="<?php echo $user['id']; ?>" <?php echo $taskManager->getAssignedTo() == $user['id'] ? 'selected' : ''; ?>><?php echo $user['username']; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="update_task">Mettre à jour</button>
            <button type="submit" name="delete_task" style="background-color: red;">Supprimer</button>
        </form>
    </div>
</body>

</html>
