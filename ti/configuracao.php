<?php
//verifica se fez login
session_start();
if(!isset($_SESSION['username'])){
    header("refresh:5;url=index.php");
    exit("Acesso Restrito");
}

//verifica se é gestor ou admin
$user = $_SESSION['username'];
if($user !== 'admin'){
    // Bloqueia a execução da página e mostra uma mensagem de erro
    die("Erro 403: Acesso Negado. Esta página é exclusiva para a Gestão.");
}


?>