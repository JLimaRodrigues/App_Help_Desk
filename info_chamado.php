<?php

require_once 'validador_acesso.php';
require 'config.php';

if(!isset($_GET['chamado'])){
    header('Location: consultar_chamados.php?chamado=erro');
}

$chamado = $db->select("ch.*, ca.descricao as categoria, atri.*")
            ->from("chamado ch")
            ->join("categoria ca", "ch.categoria_id = ca.id_categoria")
            ->join("atribuido_para atri", "ch.id_chamado = atri.chamado_id", "LEFT")
            ->where("ch.id_chamado = '{$_GET['chamado']}'")
            ->execute()[0];

$mensagens = $db->select("m.*, u.nome as usuario_nome, u.nivel, n.nivel as nivel_descricao")
            ->from("mensagens_chamado m")
            ->join("usuario u", "m.usuario_id = u.id")
            ->join("nivel n", "u.nivel = n.cod_ni")
            ->where("m.chamado_id = '{$_GET['chamado']}'")
            ->orderBy("m.created_at", "ASC")
            ->execute();

?>
<!DOCTYPE html>
<html>
<head>
    <title>App Help Desk</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        /* Estilos CSS para o layout do chat */
        .chat-container {
            display: flex;
            flex-direction: column;
            height: 400px; /* Altura fixa para o chat */
            overflow-y: scroll; /* Barra de rolagem se necessário */
        }
        .message {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 5px;
        }
        .user {
            background-color: #e0f2f7; /* Azul claro para mensagens do usuário */
            align-self: flex-end; /* Alinha mensagens do usuário à direita */
        }
        .technician {
            background-color: #c8e6c9; /* Verde claro para mensagens do técnico */
            align-self: flex-start; /* Alinha mensagens do técnico à esquerda */
        }
        .message-info {
            font-size: 12px;
            color: #757575;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <a class="navbar-brand" href="#">
            <img src="logo.png" width="30" height="30" class="d-inline-block align-top" alt="">
            App Help Desk
        </a>
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="logoff.php">SAIR</a>
            </li>
        </ul>
    </nav>

    <div class="row m-2">
        <div class="col-2">
            <a href="consultar_chamados.php" class="btn btn-lg btn-warning btn-block" type="button">Voltar</a>
        </div>
        </div>
    </div>

    <div class="container my-3">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        Resumo do Chamado
                    </div>
                    <div class="card-body chat-container" id="chat-container">
                    <?php foreach ($mensagens as $msg){
                            if ($msg['usuario_id'] == $_SESSION['id']) {
                                $classe = "user";
                                $remetente = "Eu (" . $msg['nivel_descricao'] . ")";
                            } else {
                                if ($_SESSION['nivel'] != 1) {
                                    $classe = "technician";
                                } else {
                                    $classe = "user";
                                }
                                $remetente = $msg['usuario_nome'] . " (" . $msg['nivel_descricao'] . ")";
                            }
                        ?>
                        <div class="message <?= $classe ?>">
                            <div class="message-info">
                                <strong><?= htmlspecialchars($remetente) ?> - <?= $msg['created_at'] ?></strong>
                            </div>
                            <p><?= nl2br(htmlspecialchars($msg['mensagem'])) ?></p>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="card-footer">
                        <form id="message-form">
                            <div class="input-group">
                                <input type="text" class="form-control" id="message-input" placeholder="Digite sua mensagem">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">Enviar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        Informações do Chamado
                    </div>
                    <div class="card-body">
                        <p><strong>Título:</strong> <?= $chamado['titulo'] ?></p>
                        <p><strong>Criado por:</strong> Você</p>
                        <p><strong>Data de criação:</strong> <?= $chamado['created_at'] ?></p>
                        <div class="form-group">
                            <label for="technicians">Atribuir Técnicos:</label>
                            <select class="form-control" id="technicians" multiple>
                                </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script>
    // Conecta ao WebSocket (certifique-se de que o IP/porta estejam corretos)
    var conn = new WebSocket('ws://localhost:8082');

    conn.onopen = function(e) {
        console.log("Conexão WebSocket estabelecida!");

        conn.send(JSON.stringify({
        tipo: 'registro', // Tipo de mensagem
        chamado: <?= $_GET['chamado']; ?>, // ID do chamado
        usuario: <?= $_SESSION['id']; ?> // ID do usuário
    }));
    };

    conn.onmessage = function(e) {
        var data = JSON.parse(e.data);

        // Verifica se a mensagem é para o chamado atual
        if (data.chamado_id == <?= $_GET['chamado']; ?>) {
            var mensagemElem = document.createElement("div");
            var remetente = "";
            var classe = "";

            var idUsuario = <?= $_SESSION['id']; ?>;

            if (data.usuario_id && data.usuario_id == idUsuario) {
                classe = "user";
                remetente = "Eu";
            } else {
                classe = "technician";
                remetente = data.usuario_nome || "Outros";
            }

            // Cria o markup da mensagem com os dados recebidos
            mensagemElem.className = "message " + classe;
            mensagemElem.innerHTML = "<div class='message-info'><strong>" + remetente + " (" + data.nivel + ") - " + data.data + "</strong></div>" +
                                    "<p>" + data.mensagem + "</p>";
            document.getElementById("chat-container").appendChild(mensagemElem);
        }
    };

    // Envia a mensagem com o ID do chamado
    document.getElementById('message-form').addEventListener('submit', function(event) {
        event.preventDefault();
        var messageInput = document.getElementById('message-input');
        var mensagem = messageInput.value.trim();

        if (mensagem !== "") {
            var usuario = <?= $_SESSION['id']; ?>;
            var chamado = <?= $_GET['chamado']; ?>;

            var data = JSON.stringify({ usuario, chamado, mensagem });
            conn.send(data);
            messageInput.value = "";
        }
    });
</script>
</body>
</html>