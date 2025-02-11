<?php 

require_once "./config.php";

try {

    $db->createTable("categoria", [
        "id_categoria" => "INT AUTO_INCREMENT PRIMARY KEY",
        "descricao" => "VARCHAR(144) NOT NULL",
        "created_at" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
    ]);

    $db->createTable("nivel", [
        "id_nivel" => "INT AUTO_INCREMENT PRIMARY KEY",
        "nivel" => "VARCHAR(60) NOT NULL",
        "cod_ni" => "INT NOT NULL",
        "descricao" => "TEXT",
        "created_at" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
    ]);

     $db->insert("categoria", ["descricao" => "Hardware"]);
     $db->insert("categoria", ["descricao" => "Redes"]);
     $db->insert("categoria", ["descricao" => "Software"]);

     $db->insert("nivel", ["nivel" => "Usuário Comum", "cod_ni" => 1]);
     $db->insert("nivel", ["nivel" => "Gestor", "cod_ni" => 2]);
     $db->insert("nivel", ["nivel" => "Administrador", "cod_ni" => 3]);
 
     $db->createTable("usuario", [
        "id" => "INT AUTO_INCREMENT PRIMARY KEY",
        "nome" => "VARCHAR(144) NOT NULL",
        "login" => "VARCHAR(60) NOT NULL",
        "senha" => "VARCHAR(255) NOT NULL",
        "nivel" => "INT NOT NULL",
        "email" => "VARCHAR(255) UNIQUE NOT NULL",
        "created_at" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
        "foreign_key" => [
            "table" => "nivel",
            "column" => "cod_ni"
        ]
    ]);

    $db->createTable("chamado", [
        "id_chamado" => "INT AUTO_INCREMENT PRIMARY KEY",
        "titulo" => "VARCHAR(60) NOT NULL",
        "descricao_chamado" => "TEXT NOT NULL",
        "categoria" => "INT NOT NULL",
        "created_at" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
        "categoria" => [
            "type" => "INT NOT NULL",
            "foreign_key" => [
                "table" => "categoria",
                "column" => "id_categoria"
            ]
        ]
    ]);

    $db->insert("usuario", [
        "nome" => "Administrador",
        "login" => "admin",
        "senha" => password_hash("123456", PASSWORD_BCRYPT, ['cost' => 12]),
        "nivel" => 3,
        "email" => "admin.teste@example.com"
    ]);

    echo "Tabelas criadas e dados inseridos com sucesso!";

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
    echo "<pre>";
    print_r($db->getLog());
    echo "</pre>";
}