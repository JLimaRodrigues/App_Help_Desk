<?php
require_once 'validador_acesso.php';
require 'config.php';

$por_pagina = 10;
$pagina = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;
$inicio = ($pagina - 1) * $por_pagina;

$search = isset($_POST['search']) ? trim($_POST['search']) : '';

$db = Conexao::getInstance();

// Consulta para contar o total de usuários antes da paginação
$queryTotal = $db->select("COUNT(*) AS total")
    ->from("usuario us")
    ->join("nivel ni", "us.nivel = ni.cod_ni");

if (!empty($search)) {
    $queryTotal->where("us.nome LIKE :search OR us.login LIKE :search OR us.email LIKE :search OR DATE(us.created_at) LIKE :search", [
        'search' => "%$search%"
    ]);
}

$total_usuarios = $queryTotal->execute()[0]['total'];
$total_paginas = ceil($total_usuarios / $por_pagina);

// Consulta paginada
$query = $db->select("us.id, us.nome, us.login, us.email, us.created_at, ni.nivel")
    ->from("usuario us")
    ->join("nivel ni", "us.nivel = ni.cod_ni");

if (!empty($search)) {
    $query->where("us.nome LIKE :search OR us.login LIKE :search OR us.email LIKE :search OR DATE(us.created_at) LIKE :search", [
        'search' => "%$search%"
    ]);
}

$usuarios = $query->orderBy("us.id", "DESC")->limit($por_pagina)->offset($inicio)->execute();

// Retorno JSON
echo json_encode([
    'usuarios' => $usuarios,
    'total_usuarios' => $total_usuarios,
    'pagina_atual' => $pagina,
    'total_paginas' => $total_paginas
]);

?>
