<?php

require_once __DIR__ . '/config.php';

set_time_limit(0);

$host = '0.0.0.0';
$port = 8082;
$null = NULL;

// Cria o socket principal
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($socket, $host, $port);
socket_listen($socket);

$clients = [$socket];

echo "Servidor WebSocket iniciado em ws://{$host}:{$port}\n";

while (true) {
    // Cria um array com todos os sockets para passar para o socket_select
    $changed = $clients;
    socket_select($changed, $null, $null, 0, 10);

    // Se for o socket principal, significa nova conexão
    if (in_array($socket, $changed)) {
        $newSocket = socket_accept($socket);
        $clients[] = $newSocket;

        // Lê o cabeçalho da requisição do handshake
        $header = socket_read($newSocket, 1024);
        if ($header) {
            perform_handshake($header, $newSocket, $host, $port);
            echo "Nova conexão estabelecida.\n";
        }
        // Remove o socket principal da lista de sockets prontos
        $key = array_search($socket, $changed);
        unset($changed[$key]);
    }

    foreach ($changed as $client) {
        $data = @socket_read($client, 1024);
        if ($data === false) {
            $index = array_search($client, $clients);
            unset($clients[$index]);
            socket_close($client);
            echo "Um cliente se desconectou.\n";
            continue;
        }
        
        $data = trim($data);
        if (!empty($data)) {

            $decodedMessage = unmask($data);
            echo "Mensagem recebida: {$decodedMessage}\n";

            $mensagemRecebida = json_decode($decodedMessage, true);
            if ($mensagemRecebida && isset($mensagemRecebida['usuario'], $mensagemRecebida['chamado'], $mensagemRecebida['mensagem'])) {
                // Insere no banco de dados
                $db->insert("mensagens_chamado", [
                    "chamado_id" => $mensagemRecebida['chamado'],
                    "usuario_id" => $mensagemRecebida['usuario'],
                    "mensagem"   => $mensagemRecebida['mensagem']
                ]);
        
                $ultimoId = $db->getLastInsertId(); 

                $resultado = $db->select("m.*, u.id as usuario_id, u.nome as usuario_nome, n.nivel as nivel_descricao")
                                ->from("mensagens_chamado m")
                                ->join("usuario u", "m.usuario_id = u.id")
                                ->join("nivel n", "u.nivel = n.cod_ni")
                                ->where("m.id_messagem = '{$ultimoId}'")
                                ->execute();
        
                if (!empty($resultado)) {
                    $msgData = $resultado[0];
                    $textoResposta = $msgData['usuario_nome'] . " (" . $msgData['nivel_descricao'] . ") - " . $msgData['created_at'] . " : " . $msgData['mensagem'];
                    
                    $respostaJson = json_encode([
                        "usuario_id"   => $msgData['usuario_id'],
                        "usuario_nome" => $msgData['usuario_nome'],
                        "nivel"        => $msgData['nivel_descricao'],
                        "data"         => $msgData['created_at'],
                        "mensagem"     => $msgData['mensagem']
                    ]);
                } else {
                    $respostaJson = json_encode(["mensagem" => $mensagemRecebida['mensagem']]);
                }
            } else {
                $respostaJson = json_encode(["mensagem" => $decodedMessage]);
            }
        
            $response = mask($respostaJson);
        
            foreach ($clients as $sendClient) {
                if ($sendClient != $socket) {
                    socket_write($sendClient, $response, strlen($response));
                }
            }
        }
    }
}

// Função para realizar o handshake do WebSocket
function perform_handshake($header, $client, $host, $port) {
    if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $header, $matches)) {
        $secWebSocketKey = trim($matches[1]);
        $secWebSocketAccept = base64_encode(pack('H*', sha1($secWebSocketKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        $upgrade = "HTTP/1.1 101 Switching Protocols\r\n" .
                   "Upgrade: websocket\r\n" .
                   "Connection: Upgrade\r\n" .
                   "Sec-WebSocket-Accept: $secWebSocketAccept\r\n\r\n";
        socket_write($client, $upgrade, strlen($upgrade));
    }
}

// Função para remover a máscara da mensagem recebida
function unmask($payload) {
    $length = ord($payload[1]) & 127;
    if ($length === 126) {
        $masks = substr($payload, 4, 4);
        $data = substr($payload, 8);
    } elseif ($length === 127) {
        $masks = substr($payload, 10, 4);
        $data = substr($payload, 14);
    } else {
        $masks = substr($payload, 2, 4);
        $data = substr($payload, 6);
    }
    $text = "";
    for ($i = 0; $i < strlen($data); ++$i) {
        $text .= $data[$i] ^ $masks[$i % 4];
    }
    return $text;
}

// Função para aplicar máscara a uma mensagem antes de enviar
function mask($text) {
    $b1 = 0x81; // 10000001
    $length = strlen($text);

    if ($length <= 125) {
        $header = pack('CC', $b1, $length);
    } elseif ($length > 125 && $length < 65536) {
        $header = pack('CCn', $b1, 126, $length);
    } else {
        $header = pack('CCNN', $b1, 127, $length);
    }
    return $header . $text;
}
