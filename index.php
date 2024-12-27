<?php

require_once 'config.php';

class User
{
    private $conn;
    private $table_name = "Users";

    public $id;
    public $username;
    public $email;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " (username, email) VALUES (:username, :email)";
        $stmt = $this->conn->prepare($query);

        // Clean and sanitize input data
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));

        // Bind parameters
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);

        // Execute the query
        return $stmt->execute();
    }
}

// Initialize database connection
$database = new Database();
$db = $database->connect();

$message = ""; // Initialize the message variable

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';

    if (!empty($username) && !empty($email)) {
        $user = new User($db);
        $user->username = $username;
        $user->email = $email;

        if ($user->create()) {
            $message = "<p style='color: green;'>Utilisateur ajouté avec succès !</p>";
        } else {
            $message = "<p style='color: red;'>Erreur : Impossible d'ajouter l'utilisateur. Vérifiez les doublons d'email.</p>";
        }
    } else {
        $message = "<p style='color: red;'>Veuillez remplir tous les champs.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un utilisateur</title>
    <style>
        /* Style de base */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #333;
        }

        /* Conteneur principal */
        .container {
            max-width: 500px;
            margin: 50px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        /* Titre */
        h1 {
            font-size: 24px;
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        /* Message de retour */
        .message {
            text-align: center;
            margin-bottom: 20px;
        }

        /* Formulaire */
        form {
            display: flex;
            flex-direction: column;
        }

        /* Labels */
        label {
            font-weight: bold;
            margin-bottom: 5px;
            color: #555;
        }

        /* Champs de formulaire */
        input[type="text"],
        input[type="email"] {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 15px;
            width: 100%;
            box-sizing: border-box;
        }

        /* Bouton */
        button {
            background-color: #007BFF;
            color: #fff;
            font-size: 16px;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <h1>Ajouter un utilisateur</h1>
    <?= $message ?>
    <form action="" method="POST">
        <label for="username">Nom d'utilisateur :</label>
        <input type="text" id="username" name="username" required>
        <br><br>
        <label for="email">Email :</label>
        <input type="email" id="email" name="email" required>
        <br><br>
        <button type="submit">Ajouter</button>
    </form>
</body>

</html>