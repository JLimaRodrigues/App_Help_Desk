<?php 
require_once 'validador_acesso.php';
require 'config.php';

$session_id = $_SESSION['id'];
$nivel = $_SESSION['nivel'];

//CHAMADOS DO SISTEMA
if($nivel == 1){ // Usuário comum só pode ver os dele

  $chamados = $db->select("ch.usuario_id, ch.id_chamado, ch.titulo, ch.descricao_chamado, ca.descricao as categoria, us.nome")
    ->from("chamado ch")
    ->join("categoria ca", "ca.id_categoria = ch.categoria_id")
    ->join("usuario us", "us.id = ch.usuario_id")
    ->where("ch.usuario_id = :usuario_id", ["usuario_id" => $session_id])
    ->orderBy("ch.created_at", "DESC")
    ->execute();
// echo "<pre>"; print_r($chamados); echo "</pre>"; exit;
} else if($nivel == 2){ //Técnico - pode ver os deles e os atribuidos para ele

  $chamados = $db->select("DISTINCT ch.id_chamado, ch.titulo, ca.descricao as categoria, us.nome")
  ->from("chamado ch")
  ->join("categoria ca", "ca.id_categoria = ch.categoria_id")
  ->join("usuario us", "us.id = ch.usuario_id")
  ->join("atribuido_para atri", "atri.chamado_id = ch.id_chamado")
  ->where("atri.tecnico_id = :tecnico_id", ["tecnico_id" => $session_id])
  ->orderBy("ch.created_at", "DESC")
  ->limit(20)
  ->execute();

} else {// Gestor ou Administrador - podem ver todos
  $chamados = $db->select("ch.usuario_id, ch.id_chamado, ch.titulo, ch.descricao_chamado, ca.descricao as categoria, us.nome")
  ->from("chamado ch")
  ->join("categoria ca", "ca.id_categoria = ch.categoria_id")
  ->join("usuario us", "us.id = ch.usuario_id")
  ->orderBy("ch.usuario_id")
  ->execute();
}


?>
<html>
  <head>
    <meta charset="utf-8" />
    <title>App Help Desk</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <style>
        .card-consultar-chamado {
            padding: 30px 0 0 0;
            width: 100%;
            margin: 0 auto;
        }
        .card-clickable {
            cursor: pointer; /* Indica que o card é clicável */
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

    <div class="container">    
      <div class="row">

        <div class="card-consultar-chamado">
          <div class="card">
            <div class="card-header">
              Consulta de chamado
            </div>
            
            <div class="card-body">

            <?php if(isset($_GET['chamado']) && ($_GET['chamado'] == 'erro')){ ?>
                <div class="alert alert-danger text-center m-3">
                  Chamado Inválido.
                </div>
              <?php  } ?>

            <a href="abrir_chamado.php" class="btn btn-lg btn-success btn-block" type="button">Abrir chamado</a>

            <?php foreach ($chamados as $chamado) { ?> 
              
                <div class="card my-3 bg-light card-clickable" data-ticket-id="<?= $chamado['id_chamado'] ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= $chamado['titulo'] ?> <b> feito por <?php echo $session_id == $chamado['usuario_id'] ? 'mim': $chamado['nome'] ?><b></h5>
                        <h6 class="card-subtitle mb-2 text-muted"><?= $chamado['categoria'] ?></h6>
                        <p class="card-text"><?= $chamado['descricao_chamado'] ?></p>
                    </div>
                </div>
              
            <?php } ?>

              <div class="row mt-5">
                <div class="col-6">
                    <a href="home.php" class="btn btn-lg btn-warning btn-block" type="button">Voltar</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.card-clickable').click(function() {
                var ticketId = $(this).data('ticket-id');
                window.location.href = 'info_chamado.php?chamado=' + ticketId;
            });
        });
    </script>

  </body>
</html>