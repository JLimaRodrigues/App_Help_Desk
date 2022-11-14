<?php 
session_start();

require 'config.php';

$usuario_autenticado = false;
$usuario_id = null;
$nivel = null;

$login = $_POST['login'];
$senha = $_POST['senha'];

//USUARIOS DO SISTEMA
$consulta = $conexao->prepare('SELECT * FROM usuario WHERE login=:login');
$consulta->bindValue('login', $login);
$consulta->execute();
$dados = $consulta->fetchAll();

foreach($dados as $dado){//PEGA SENHA
    if(password_verify($senha, $dado['senha'])){
        $usuario_id = $dado['id'];
        $nivel = $dado['nivel'];
        $usuario_autenticado = true; 
        break;
    } 
}

if($usuario_autenticado){//USUARIO AUTENTICADO
    $_SESSION['autenticado'] = 'SIM';
    $_SESSION['id'] = $usuario_id;
    $_SESSION['nivel'] = $nivel;
    header('Location: home.php');
} else {//NÃO AUTENTICADO
    $_SESSION['autenticado'] = 'NAO';
    header('Location: index.php?login=erro');
}



?>