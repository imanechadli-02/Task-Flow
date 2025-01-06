<?php
class Database
{
    private $host = "localhost";
    private $username = "root";
    private $password = "12345chadli"; 
    private $dbname = "taskflow_db";
    private $conn;

   
    public function __construct()
    {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->dbname);

        if ($this->conn->connect_error) {
            die("Erreur de connexion : " . $this->conn->connect_error);
        }
    }

    public function insertUser(User $user)
    {
        $stmt = $this->conn->prepare("INSERT INTO users (username, email) VALUES (?, ?)");
        if ($stmt === false) {
            return $this->conn->error;
        }

        $username = $user->getUsername();
        $email = $user->getEmail();
        $stmt->bind_param("ss", $username, $email);

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

class User
{
    private $username;
    private $email;

    public function setUsername($username)
    {
        if (!empty($username)) {
            $this->username = htmlspecialchars($username);
        } else {
            throw new Exception("Le nom d'utilisateur ne peut pas être vide.");
        }
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setEmail($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->email = htmlspecialchars($email);
        } else {
            throw new Exception("Adresse e-mail invalide.");
        }
    }

    public function getEmail()
    {
        return $this->email;
    }
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $user = new User();
        $user->setUsername($_POST['username']);
        $user->setEmail($_POST['email']);

        $db = new Database(); 
        $result = $db->insertUser($user);

        if ($result === true) {
            header("Location: afficherTasks.php");
            exit; 
        } else {
            $message = "<p style='color: red;'>Erreur : $result</p>";
        }
        

        $db->closeConnection(); 
    } catch (Exception $e) {
        $message = "<p style='color: red;'>" . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow - Enregistrement</title>
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
        input[type="text"], input[type="email"], button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            background-color: #28a745;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            border: none;
        }
        button:hover {
            background-color: #218838;
        }
        .message {
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>TaskFlow</h1>
        <?php if (!empty($message)) : ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Entrez votre nom" required>
            <input type="email" name="email" placeholder="Entrez votre email" required>
            <button type="submit">S'enregistrer</button>
        </form>
    </div>
</body>
</html>
