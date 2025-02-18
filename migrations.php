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
        "cod_ni" => "INT NOT NULL UNIQUE",
        "descricao" => "TEXT",
        "created_at" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
    ]);

     $db->insert("categoria", ["descricao" => "Hardware"]);
     $db->insert("categoria", ["descricao" => "Criação Usuário"]);
     $db->insert("categoria", ["descricao" => "Impressora"]);
     $db->insert("categoria", ["descricao" => "Redes"]);
     $db->insert("categoria", ["descricao" => "Software"]);

     $db->insert("nivel", ["nivel" => "Usuário Comum", "cod_ni" => 1]);
     $db->insert("nivel", ["nivel" => "Técnico", "cod_ni" => 2]);
     $db->insert("nivel", ["nivel" => "Gestor", "cod_ni" => 3]);
     $db->insert("nivel", ["nivel" => "Administrador", "cod_ni" => 4]);
 
     $db->createTable("usuario", [
        "id"         => "INT AUTO_INCREMENT PRIMARY KEY",
        "nome"       => "VARCHAR(144) NOT NULL",
        "login"      => "VARCHAR(60) NOT NULL",
        "senha"      => "VARCHAR(255) NOT NULL",
        "nivel"      => "INT NOT NULL",
        "email"      => "VARCHAR(255) UNIQUE NOT NULL",
        "created_at" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
    ], [
        [
            "column"    => "nivel",
            "table"     => "nivel",
            "reference" => "cod_ni"
        ]
    ]);

    $db->createTable("chamado", [
        "id_chamado"        => "INT AUTO_INCREMENT PRIMARY KEY",
        "titulo"            => "VARCHAR(60) NOT NULL",
        "descricao_chamado" => "TEXT NOT NULL",
        "categoria_id"      => "INT NOT NULL",
        "usuario_id"        => "INT NOT NULL",
        "created_at"        => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
    ], [
        [
            "column"    => "categoria_id",
            "table"     => "categoria",
            "reference" => "id_categoria"
        ],
        [
            "column"    => "usuario_id",
            "table"     => "usuario",
            "reference" => "id"
        ]
    ]);

    $db->createTable("atribuido_para", [
        "id_atribuicao"   => "INT AUTO_INCREMENT PRIMARY KEY",
        "chamado_id"      => "INT NOT NULL",
        "tecnico_id"      => "INT NOT NULL",
        "atribuido_por"   => "INT NOT NULL",
        "data_atribuicao" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
    ], [
        [
            "column"    => "chamado_id",
            "table"     => "chamado",
            "reference" => "id_chamado"
        ],
        [
            "column"    => "tecnico_id",
            "table"     => "usuario",
            "reference" => "id"
        ],
        [
            "column"    => "atribuido_por",
            "table"     => "usuario",
            "reference" => "id"
        ]
    ]);

    $db->createTable("mensagens_chamado", [
        "id_messagem" => "INT AUTO_INCREMENT PRIMARY KEY",
        "chamado_id"  => "INT NOT NULL",
        "usuario_id"  => "INT NOT NULL",
        "mensagem"    => "TEXT NOT NULL",
        "created_at"  => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
    ], [
        [
            "column"    => "chamado_id",
            "table"     => "chamado",
            "reference" => "id_chamado"
        ],
        [
            "column"    => "usuario_id",
            "table"     => "usuario",
            "reference" => "id"
        ]
    ]);

    $db->insert("usuario", [
        "nome" => "Administrador 1",
        "login" => "admin",
        "senha" => password_hash("123456", PASSWORD_BCRYPT, ['cost' => 12]),
        "nivel" => 4,
        "email" => "admin.teste1@example.com"
    ]);

    $db->insert("usuario", [
        "nome" => "Administrador 2",
        "login" => "admin.2",
        "senha" => password_hash("123456", PASSWORD_BCRYPT, ['cost' => 12]),
        "nivel" => 4,
        "email" => "admin.teste2@example.com"
    ]);

    $db->insert("usuario", [
        "nome" => "Usuário Comum 1",
        "login" => "teste",
        "senha" => password_hash("123456", PASSWORD_BCRYPT, ['cost' => 12]),
        "nivel" => 1,
        "email" => "teste@example.com"
    ]);

    $db->insert("usuario", [
        "nome" => "Usuário Comum 2",
        "login" => "teste.2",
        "senha" => password_hash("123456", PASSWORD_BCRYPT, ['cost' => 12]),
        "nivel" => 1,
        "email" => "teste2@example.com"
    ]);

    echo "Tabelas criadas e dados inseridos com sucesso!";

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
    echo "<pre>";
    print_r($db->getLog());
    echo "</pre>";
}