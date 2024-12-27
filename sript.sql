-- Création de la base de données
CREATE DATABASE TaskFlow;
USE TaskFlow;

-- Table des utilisateurs
CREATE TABLE Users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE
);

-- Table des types de tâches
CREATE TABLE Task_Types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(50) NOT NULL UNIQUE
);

-- Table des statuts des tâches
CREATE TABLE Task_Status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    status_name VARCHAR(50) NOT NULL UNIQUE
);

-- Table des tâches
CREATE TABLE Tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    assigned_to INT, -- Clé étrangère vers la table Users
    type_id INT NOT NULL, -- Clé étrangère vers la table Task_Types
    status_id INT NOT NULL, -- Clé étrangère vers la table Task_Status
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES Users(id) ON DELETE SET NULL,
    FOREIGN KEY (type_id) REFERENCES Task_Types(id) ON DELETE CASCADE,
    FOREIGN KEY (status_id) REFERENCES Task_Status(id) ON DELETE CASCADE
);

-- Insertion des types de tâches
INSERT INTO Task_Types (type_name) VALUES ('Simple Task'), ('Bug'), ('Feature');

-- Insertion des statuts des tâches
INSERT INTO Task_Status (status_name) VALUES ('To Do'), ('In Progress'), ('Done');

DESCRIBE TABLE Tasks;

SELECT * from Users;
