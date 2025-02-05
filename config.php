<?php 

try {
//  $conexao = new PDO('mysql:host=localhost;dbname=site_help_desk','root','');
 $conexao = new PDO('mysql:host=db;dbname=site_help_desk','root','password');
 $conexao->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die('ERROR: '.$e->Message());
}

?>