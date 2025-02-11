<?php 

require_once "./config.php";

// Criar tabela
$db->createTable("usuario", [
    "id" => "INT AUTO_INCREMENT PRIMARY KEY",
    "nome" => "VARCHAR(144) NOT NULL",
    "login" => "VARCHAR(60) NOT NULL",
    "senha" => "VARCHAR(255) NOT NULL",
    "nivel" => "INT(2) NOT NULL",
    "email" => "VARCHAR(255) UNIQUE NOT NULL",
    "created_at" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
]);

// Inserir dados
$db->insert("usuario", [
    "nome" => "Administrador", 
    "login" => "admin",
    // "senha" => "123456",
    "senha" => password_hash("123456", PASSWORD_BCRYPT, ['cost' => 12]), //o objetivo é aumentar o custo padrão de BCRYPT para 12, documentação https://www.php.net/manual/pt_BR/function.password-hash.php
    "nivel" => "1",
    "email" => "admin.teste@example.com"
]);