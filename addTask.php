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

    public function insertTask($title, $description, $status, $type, $userId)
    {
        $stmt = $this->conn->prepare("INSERT INTO tasks (title, description, status, type, assigned_to) VALUES (?, ?, ?, ?, ?)");

        if ($stmt === false) {
            die('Erreur de préparation de la requête : ' . $this->conn->error);
        }

        $stmt->bind_param("ssssi", $title, $description, $status, $type, $userId);

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

    public function closeConnection()
    {
        $this->conn->close();
    }
}

// Gestion des requêtes
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = htmlspecialchars($_POST['title']);
    $description = htmlspecialchars($_POST['description']); // Ensure you capture the description
    $status = htmlspecialchars($_POST['status']);
    $type = htmlspecialchars($_POST['type']);
    $userId = intval($_POST['user_id']);

    if (!empty($title) && !empty($description) && !empty($status) && !empty($type) && $userId > 0) {
        $db = new Database();
        $result = $db->insertTask($title, $description, $status, $type, $userId);

        if ($result === true) {
            $message = "<p style='color: green;'>Tâche ajoutée avec succès !</p>";
        } else {
            $message = "<p style='color: red;'>Erreur : $result</p>";
        }

        $db->closeConnection();
    } else {
        $message = "<p style='color: red;'>Tous les champs sont requis.</p>";
    }
}

// Charger les utilisateurs pour le champ "Assigné à"
$db = new Database();
$users = $db->getUsers();
$db->closeConnection();
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
            <input type="text" name="description" placeholder="description de la tâche" required>
            <select name="status" required>
                <option value="">Sélectionnez un statut</option>
                <option value="in_progress">in_progress</option>
                <option value="done">done</option>
                <option value="todo">todo</option>
            </select>
            <select name="type" required>
                <option value="">Sélectionnez un type</option>
                <option value="Bug">Bug</option>
                <option value="Feature">Feature</option>
                <option value="simple">simple</option>
            </select>
            <select name="user_id" required>
                <option value="">Assigné à</option>
                <?php foreach ($users as $user) : ?>
                    <option value="<?php echo $user['id']; ?>"><?php echo $user['username']; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Ajouter</button>
        </form>
    </div>
</body>

</html>