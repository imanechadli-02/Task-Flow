-- Active: 1733742662724@@127.0.0.1@3306@add_db
CREATE DATABASE Taskflow_db;
USE Taskflow_db;
-- Création de la table users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Création de la table tasks
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    type ENUM('simple', 'bug', 'feature') NOT NULL DEFAULT 'simple',
    status ENUM('todo', 'in_progress', 'done') NOT NULL DEFAULT 'todo',
    assigned_to INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
);
