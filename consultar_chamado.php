<?php 
require_once 'validador_acesso.php';
require 'config.php';

$session_id = $_SESSION['id'];
$nivel = $_SESSION['nivel'];

$resultado = '';

//USUARIOS DO SISTEMA
$consulta = $conexao->prepare('SELECT ch.id_usuario, ch.titulo, ch.descricao_chamado, ca.descricao, us.nome_usuario FROM chamado ch
INNER JOIN categoria ca ON ca.id = ch.categoria
INNER JOIN usuario us ON us.id = ch.id_usuario ORDER BY ch.id');
$consulta->execute();
$dados = $consulta->fetchAll();

foreach($dados as $dado){
  if($nivel == 1 || $session_id==$dado['id_usuario']){//SÓ VAMOS EXIBIR O CHAMADO CRIADO PELO USUÁRIO
                                                      $resultado .= '<div class="card mb-3 bg-light">
                                                                      <div class="card-body">
                                                                        <h5 class="card-title">'.$dado['titulo'].'<b> feito por '.($session_id==$dado['id_usuario'] ? 'mim':$dado['nome_usuario']).'<b></h5>
                                                                        <h6 class="card-subtitle mb-2 text-muted">'.$dado['descricao'].'</h6>
                                                                        <p class="card-text">'.$dado['descricao_chamado'].'</p>

                                                                      </div>
                                                                    </div>';
                                                    }
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
              
            <?= $resultado; ?>

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
  </body>
</html>