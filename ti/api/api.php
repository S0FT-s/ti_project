<?php
// Define o cabeçalho da resposta HTTP da API, indicando que o conteúdo retornado é texto/HTML 
// e que utiliza a codificação UTF-8 para evitar problemas com caracteres especiais
header('Content-Type: text/html; charset=utf-8');

// Verifica se o pedido feito à API utilizou o método HTTP POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Imprime uma mensagem simples de depuração (debug) para confirmar que o script identificou o POST
    echo "recebi um post";
    
    // Imprime no ecrã a estrutura e o conteúdo do array $_POST para facilitar a identificação dos dados recebidos
    print_r($_POST);

    // Valida de segurança: verifica se os três parâmetros obrigatórios foram enviados no pedido
    if (isset($_POST['nome']) && isset($_POST['valor']) && isset($_POST['hora'])) {
        
        // Constrói o caminho para a pasta específica do dispositivo (ex: files/temperatura)
        $dir = "files/" . $_POST['nome'];

        // Verifica se a pasta desse dispositivo realmente existe no servidor
        if (!is_dir($dir)) {
            // Se não existir, altera o código de estado HTTP da resposta para 400 (Bad Request)
            http_response_code(400);
            // Interrompe imediatamente a execução do script e devolve uma mensagem de erro
            die("Erro: diretório '$dir' não existe.");
        }

        // Verifica se os três ficheiros essenciais existem dentro da pasta antes de tentar escrever neles
        if(!file_exists($dir . '/valor.txt') || !file_exists($dir . '/hora.txt') || !file_exists($dir . '/log.txt') ){
            // Retorna erro 400 e interrompe a execução caso algum ficheiro esteja em falta
            http_response_code(400);
            die("Erro: ficheiro não existe.");
        }

        // Grava o novo valor recebido no ficheiro 'valor.txt', substituindo totalmente o conteúdo anterior
        file_put_contents("$dir/valor.txt", $_POST['valor']);
        
        // Grava a nova hora recebida no ficheiro 'hora.txt', substituindo também o conteúdo anterior
        file_put_contents("$dir/hora.txt", $_POST['hora']);

        // Formata a linha de registo para o histórico, juntando a hora, um ponto e vírgula, o valor, e uma quebra de linha (PHP_EOL)
        $valor_log = $_POST['hora'] . ";" . $_POST['valor'] . PHP_EOL;
        
        // Adiciona a nova linha de registo ao ficheiro 'log.txt' sem apagar os dados anteriores (através da diretiva FILE_APPEND)
        file_put_contents("$dir/log.txt", $valor_log, FILE_APPEND);
        
    } else {
        // Bloco executado se o pedido POST não trouxer o nome, o valor ou a hora
        http_response_code(400);
        echo "Faltam parâmetros no POST";
    }
    
// Caso o pedido não seja POST, verifica se foi utilizado o método HTTP GET (usado para ler/solicitar dados)
} else if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    
    // Verifica se o parâmetro 'nome' foi fornecido no URL (ex: api.php?nome=temperatura)
    if (isset($_GET['nome'])) {
        
        // Constrói o caminho para a pasta do dispositivo solicitado
        $dir = "files/" . $_GET['nome'];

        // Confirma se a pasta solicitada existe
        if (!is_dir($dir)) {
            http_response_code(400);
            die("Erro: diretório '$dir' não existe.");
        }

        // Lê o conteúdo atual do ficheiro 'valor.txt' e devolve-o diretamente como resposta ao pedido GET
        echo file_get_contents("$dir/valor.txt");
        
    } else {
        // Se o utilizador acedeu via GET mas não indicou qual o sensor/atuador que queria ler
        http_response_code(400);
        echo "Faltam parâmetros no GET";
    }
    
// Se o pedido não foi feito nem por POST nem por GET (por exemplo, PUT ou DELETE)
} else {
    // Altera o código de estado HTTP para 403 (Forbidden) indicando que a ação não é permitida
    http_response_code(403);
    echo "Método não permitido";
}
?>