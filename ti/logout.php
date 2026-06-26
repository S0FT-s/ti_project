<?php
//Inicia a sessão
session_start();   

//Limpa a sessão
session_unset();   

//Destroi a sessão
session_destroy();  

//Redireciona para o "index.php"
header( "refresh:0;url=index.php" );
?>