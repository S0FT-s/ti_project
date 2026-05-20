<?php
header('Content-Type: text/html; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    echo "recebi um post";
    print_r($_POST);

    if (isset($_POST['nome']) && isset($_POST['valor']) && isset($_POST['hora'])) {
        $dir = "files/" . $_POST['nome'];

        // Verifica se o diretório existe
        if (!is_dir($dir)) {
            http_response_code(400);
            die("Erro: diretório '$dir' não existe.");
        }

        if(!file_exists($dir . '/valor.txt') || !file_exists($dir . '/hora.txt') || !file_exists($dir . '/log.txt') ){
            http_response_code(400);
            die("Erro: ficheiro não existe.");
        }

        file_put_contents("$dir/valor.txt", $_POST['valor']);
        file_put_contents("$dir/hora.txt", $_POST['hora']);

        $valor_log = $_POST['hora'] . ";" . $_POST['valor'] . PHP_EOL;
        file_put_contents("$dir/log.txt", $valor_log, FILE_APPEND);
    } else {
        http_response_code(400);
        echo "Faltam parâmetros no POST";
    }
} else if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['nome'])) {
        $dir = "files/" . $_GET['nome'];

        // Verifica se o diretório existe
        if (!is_dir($dir)) {
            http_response_code(400);
            die("Erro: diretório '$dir' não existe.");
        }

        echo file_get_contents("$dir/valor.txt");
    } else {
        http_response_code(400);
        echo "Faltam parâmetros no GET";
    }
} else {
    http_response_code(403);
    echo "Método não permitido";
}
?>