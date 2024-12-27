<?php
require_once 'config.php';

// Classe pour récupérer les tâches
class Task
{
    private $conn;
    private $table_name = "Tasks";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Méthode pour récupérer les tâches groupées par statut
    public function readGroupedByStatus()
    {
        $query = "
            SELECT t.id, t.title, t.description, tt.type_name, ts.status_name, u.username, t.status_id
            FROM Tasks t
            LEFT JOIN TaskTypes tt ON t.type_id = tt.id
            LEFT JOIN TaskStatuses ts ON t.status_id = ts.id
            LEFT JOIN Users u ON t.assigned_to = u.id
            ORDER BY t.status_id
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Regrouper les tâches par statut
        $groupedTasks = [];
        foreach ($tasks as $task) {
            $groupedTasks[$task['status_name']][] = $task;
        }

        return $groupedTasks;
    }
}

// Initialiser la connexion à la base de données
$database = new Database();
$db = $database->connect();
$task = new Task($db);

// Récupérer les tâches groupées par statut
$groupedTasks = $task->readGroupedByStatus();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord des Tâches</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
        }

        .container {
            display: flex;
            width: 90%;
            max-width: 1200px;
            justify-content: space-between;
        }

        .column {
            width: 30%;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .column h2 {
            text-align: center;
            color: #333;
        }

        .task-card {
            background-color: #e9e9e9;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .task-card h3 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }

        .task-card p {
            margin: 5px 0;
            color: #666;
        }

        .task-card span {
            font-weight: bold;
            color: #888;
        }

        .task-card button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .task-card button:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Colonne "À faire" -->
        <div class="column">
            <h2>À Faire</h2>
            <?php foreach ($groupedTasks['To Do'] ?? [] as $task): ?>
                <div class="task-card">
                    <h3><?= htmlspecialchars($task['title']) ?></h3>
                    <p><strong>Description:</strong> <?= htmlspecialchars($task['description']) ?></p>
                    <p><strong>Assigné à:</strong> <?= htmlspecialchars($task['username']) ?></p>
                    <p><strong>Type:</strong> <?= htmlspecialchars($task['type_name']) ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($task['status_name']) ?></p>
                    <button>Marquer comme fait</button>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Colonne "En cours" -->
        <div class="column">
            <h2>En Cours</h2>
            <?php foreach ($groupedTasks['In Progress'] ?? [] as $task): ?>
                <div class="task-card">
                    <h3><?= htmlspecialchars($task['title']) ?></h3>
                    <p><strong>Description:</strong> <?= htmlspecialchars($task['description']) ?></p>
                    <p><strong>Assigné à:</strong> <?= htmlspecialchars($task['username']) ?></p>
                    <p><strong>Type:</strong> <?= htmlspecialchars($task['type_name']) ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($task['status_name']) ?></p>
                    <button>Marquer comme terminé</button>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Colonne "Terminé" -->
        <div class="column">
            <h2>Terminé</h2>
            <?php foreach ($groupedTasks['Done'] ?? [] as $task): ?>
                <div class="task-card">
                    <h3><?= htmlspecialchars($task['title']) ?></h3>
                    <p><strong>Description:</strong> <?= htmlspecialchars($task['description']) ?></p>
                    <p><strong>Assigné à:</strong> <?= htmlspecialchars($task['username']) ?></p>
                    <p><strong>Type:</strong> <?= htmlspecialchars($task['type_name']) ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($task['status_name']) ?></p>
                    <button disabled>Terminé</button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>

</html>