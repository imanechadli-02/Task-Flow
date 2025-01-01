<?php
// Classe pour gérer la base de données
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

// Fonction pour mapper les valeurs du statut
function getStatusLabel($status)
{
    $statusLabels = [
        'todo' => 'En attente',
        'in_progress' => 'En cours',
        'done' => 'Terminé'
    ];

    return isset($statusLabels[$status]) ? $statusLabels[$status] : $status;
}

// Gestion des requêtes
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = htmlspecialchars($_POST['title']);
    $description = htmlspecialchars($_POST['description']);
    $status = htmlspecialchars($_POST['status']);
    $type = htmlspecialchars($_POST['type']);
    $assignedTo = isset($_POST['assigned_to']) ? intval($_POST['assigned_to']) : NULL;  // Default to NULL if not set

    if (!empty($title) && !empty($description) && !empty($status) && !empty($type) && $assignedTo !== NULL) {
        $db = new Database();
        $result = $db->insertTask($title, $status, $type, $assignedTo, $description);

        if ($result === true) {
            $message = "<p style='color: green;'>Tâche ajoutée avec succès !</p>";
        } else {
            $message = "<p style='color: red;'>Erreur : $result</p>";
        }

        $db->closeConnection();
    } else {
        $message = "<p style='color: red;'>Tous les champs sont requis.</p>";
    }
}

// Charger les utilisateurs et les tâches avec les noms
$db = new Database();
$users = $db->getUsers();
$tasks = $db->getTasksWithUsers();  // Récupérer les tâches avec les noms des utilisateurs
$db->closeConnection();

// Initialisation des tâches par statut
$tasksByStatus = [
    "todo" => [],
    "in_progress" => [],
    "done" => []
];

// Vérifier si des tâches existent et les organiser par statut
if ($tasks) {
    foreach ($tasks as $task) {
        $statusLabel = getStatusLabel($task['status']);
        $tasksByStatus[$task['status']][] = $task;
    }
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
    <script>
        function toggleForm() {
            const form = document.querySelector('.form-container');
            form.style.display = form.style.display === 'block' ? 'none' : 'block';
        }
    </script>
</head>

<body>
    <div class="container">
        <h1>Gestion des Tâches</h1>
        <button onclick="toggleForm()">Ajouter une nouvelle tâche</button>

        <div class="form-container">
            <?php if (!empty($message)) : ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <input type="text" name="title" placeholder="Titre de la tâche" required>
                <textarea name="description" placeholder="Description de la tâche" required></textarea>
                <select name="status" required>
                    <option value="">Sélectionnez un statut</option>
                    <option value="in_progress">En cours</option>
                    <option value="done">Terminé</option>
                    <option value="todo">En attente</option>
                </select>
                <select name="type" required>
                    <option value="">Sélectionnez un type</option>
                    <option value="bug">Bug</option>
                    <option value="feature">Feature</option>
                    <option value="simple">simple</option>
                </select>
                <select name="assigned_to" required>
                    <option value="">Assigné à</option>
                    <?php foreach ($users as $user) : ?>
                        <option value="<?php echo $user['id']; ?>"><?php echo $user['username']; ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Ajouter</button>
            </form>
        </div>

        <div class="columns">
            <?php foreach ($tasksByStatus as $status => $tasks) : ?>
                <?php if (!empty($tasks)) : ?>
                    <div class="column">
                        <h2><?php echo ucfirst($status); ?></h2> <!-- Le titre de la colonne affiche le statut -->
                        <?php foreach ($tasks as $task) : ?>
                            <div class="task">
                                <strong><?php echo $task['title']; ?></strong>
                                <p><em><?php echo $task['description']; ?></em></p>
                                <span>Assigné à : <?php echo $task['assigned_name']; ?></span><br>
                                <span>Type : <?php echo ucfirst($task['type']); ?></span><br> <!-- Affichage du type -->
                                <span>Date de création : <?php echo date('d/m/Y H:i', strtotime($task['created_at'])); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</body>

</html>
