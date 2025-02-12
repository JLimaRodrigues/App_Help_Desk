<?php 
require_once 'validador_acesso.php'; 
require_once "config.php";

$categorias = $db->select("*")
                 ->from("categoria")
                 ->execute();

?>
<html>
  <head>
    <meta charset="utf-8" />
    <title>App Help Desk</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <style>
      .card-abrir-chamado {
        padding: 30px 0 0 0;
        width: 100%;
        margin: 0 auto;
      }
    </style>
  </head>

  <body>

    <nav class="navbar navbar-dark bg-dark">
      <a class="navbar-brand" href="home.php">
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

        <div class="card-abrir-chamado">
          <div class="card">
            <div class="card-header">
              Abertura de chamado
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col">
                <?php if(isset($_GET['cadastro']) && ($_GET['cadastro'] == 'sucess')){ ?>
                      <div class="text-success">
                        Chamado Cadastrado com Sucesso.
                      </div>
                   <?php  } ?>
                  <form method="post"  action="registra_chamado.php">
                    <div class="form-group">
                      <label for="titulo">Título</label>
                      <input id="titulo" name="titulo" type="text" class="form-control" 
                      placeholder="Título" title="Digite no mínimo 10 caracteres" minlength="10" maxlegth="60" required>
                    </div>
                    
                    <div class="form-group">
                      <label for="categoria">Categoria</label>
                      <select id="categoria" class="form-control" title="selecione uma categoria" name="categoria" required>

                        <?php foreach($categorias as $categoria) {?>
                          <option value="<?= $categoria['id_categoria'] ?>"><?= $categoria['descricao'] ?></option>
                        <?php } ?>

                      </select>
                    </div>
                    
                    <div class="form-group">
                      <label for="descricao">Descrição</label>
                      <textarea id="descricao" name="descricao" class="form-control" rows="3" 
                      title="Digite no mínimo 20 caracteres" minlength="20" maxlegth="200" required></textarea>
                    </div>

                    <div class="row mt-5">
                      <div class="col-6">
                        <a href="home.php" class="btn btn-lg btn-warning btn-block" type="button">Voltar</a>
                      </div>

                      <div class="col-6">
                        <button class="btn btn-lg btn-info btn-block" type="submit">Abrir</button>
                      </div>
                    </div>
                  </form>

                </div>
              </div>
            </div>
          </div>
        </div>
    </div>
  </body>
</html>