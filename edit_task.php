<?php
// edit_task.php
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

    // Récupérer une tâche par ID
    public function getTaskById($taskId)
    {
        $stmt = $this->conn->prepare("SELECT tasks.*, users.username AS assigned_name FROM tasks 
                                      LEFT JOIN users ON tasks.assigned_to = users.id WHERE tasks.id = ?");
        $stmt->bind_param("i", $taskId);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    // Mettre à jour une tâche
    public function updateTask($taskId, $title, $status, $type, $assignedTo, $description)
    {
        $stmt = $this->conn->prepare("UPDATE tasks SET title = ?, status = ?, type = ?, assigned_to = ?, description = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $title, $status, $type, $assignedTo, $description, $taskId);

        if ($stmt->execute()) {
            return true;
        } else {
            return $stmt->error;
        }
    }

    public function closeConnection()
    {
        $this->conn->close();
    }
}

// Vérifiez si l'ID de la tâche est passé dans l'URL
if (isset($_GET['id'])) {
    $taskId = intval($_GET['id']); // Récupérer l'ID de la tâche à partir de l'URL

    // Connexion à la base de données et récupération des détails de la tâche
    $db = new Database();
    $task = $db->getTaskById($taskId);  // Méthode à créer dans la classe Database pour récupérer une tâche par ID
    $users = $db->getUsers(); // Récupérer tous les utilisateurs

    if (!$task) {
        die("Tâche non trouvée");
    }

    // Si le formulaire est soumis, on met à jour la tâche
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = htmlspecialchars($_POST['title']);
        $description = htmlspecialchars($_POST['description']);
        $status = htmlspecialchars($_POST['status']);
        $type = htmlspecialchars($_POST['type']);
        $assignedTo = isset($_POST['assigned_to']) ? intval($_POST['assigned_to']) : NULL;

        if (!empty($title) && !empty($description) && !empty($status) && !empty($type) && $assignedTo !== NULL) {
            $result = $db->updateTask($taskId, $title, $status, $type, $assignedTo, $description);

            if ($result === true) {
                // Message de succès avec alerte JavaScript
                echo "<script>
                        alert('Tâche mise à jour avec succès !');
                        window.location.href = 'affichertasks.php'; // Remplacez 'tasks_list.php' par votre page d'affichage des tâches
                      </script>";
            } else {
                echo "<p style='color: red;'>Erreur : $result</p>";
            }
        } else {
            echo "<p style='color: red;'>Tous les champs sont requis.</p>";
        }
    }

    $db->closeConnection();
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
        body {
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
            <input type="text" name="title" value="<?php echo $task['title']; ?>" placeholder="Titre de la tâche" required>
            <textarea name="description" placeholder="Description de la tâche" required><?php echo $task['description']; ?></textarea>
            <select name="status" required>
                <option value="todo" <?php echo $task['status'] === 'todo' ? 'selected' : ''; ?>>En attente</option>
                <option value="in_progress" <?php echo $task['status'] === 'in_progress' ? 'selected' : ''; ?>>En cours</option>
                <option value="done" <?php echo $task['status'] === 'done' ? 'selected' : ''; ?>>Terminé</option>
            </select>
            <select name="type" required>
                <option value="bug" <?php echo $task['type'] === 'bug' ? 'selected' : ''; ?>>Bug</option>
                <option value="feature" <?php echo $task['type'] === 'feature' ? 'selected' : ''; ?>>Feature</option>
                <option value="simple" <?php echo $task['type'] === 'simple' ? 'selected' : ''; ?>>Simple</option>
            </select>
            <select name="assigned_to" required>
                <option value="">Assigné à</option>
                <?php foreach ($users as $user) : ?>
                    <option value="<?php echo $user['id']; ?>" <?php echo $task['assigned_to'] == $user['id'] ? 'selected' : ''; ?>><?php echo $user['username']; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Mettre à jour</button>
        </form>
    </div>
</body>

</html>
