<?php 
require_once 'validador_acesso.php';
require 'config.php';

if(isset($_SESSION['id'])){
    $titulo = $_POST['titulo'];
    $categoria = $_POST['categoria'];
    $descricao_chamado = $_POST['descricao'];
    $id_usuario = $_SESSION['id'];
} else {
    header('Location: logoff.php');
}

//USUARIOS DO SISTEMA
$consulta = $conexao->prepare('INSERT INTO chamado(titulo, categoria, descricao_chamado, id_usuario) 
                                VALUES (:titulo, :categoria, :descricao_chamado, :id_usuario)');
$consulta->bindValue('titulo', $titulo);
$consulta->bindValue('categoria', $categoria);
$consulta->bindValue('descricao_chamado', $descricao_chamado);
$consulta->bindValue('id_usuario', $id_usuario);
$consulta->execute();
$dados = $consulta->fetchAll();

header('Location: abrir_chamado.php?cadastro=sucess');
?>