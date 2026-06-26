<?php 
// Inicia a sessão para permitir o acesso aos dados do utilizador
session_start();

// Verifica se a variável de sessão 'username' não existe, o que significa que o utilizador não está logado
if(!isset($_SESSION['username'])){
    // Redireciona o utilizador de volta para a página de login após 5 segundos
    header("refresh:5;url=index.php");
    // Interrompe a execução do resto do código e mostra uma mensagem de erro
    exit("Acesso Restrito");
}

// Guarda o nome do utilizador logado numa variável
$user = $_SESSION['username'];

// Cria variáveis booleanas (verdadeiro/falso) para verificar facilmente o tipo de permissões do utilizador
$isAdmin = ($user === 'admin');
$isGestor = ($user === 'gestor');

// Variaveis

$valor_temperatura = file_get_contents("api/files/temperatura/valor.txt");
$hora_temperatura = file_get_contents("api/files/temperatura/hora.txt");
$log_temperatura = file_get_contents("api/files/temperatura/log.txt");
$nome_temperatura = file_get_contents("api/files/temperatura/nome.txt");

$valor_humidade = file_get_contents("api/files/humidade/valor.txt");
$hora_humidade = file_get_contents("api/files/humidade/hora.txt");
$log_humidade = file_get_contents("api/files/humidade/log.txt");
$nome_humidade = file_get_contents("api/files/humidade/nome.txt");

$valor_led = file_get_contents("api/files/led/valor.txt");
$hora_led = file_get_contents("api/files/led/hora.txt");
$log_led = file_get_contents("api/files/led/log.txt");
$nome_led = file_get_contents("api/files/led/nome.txt");

$valor_ventoinha = file_get_contents("api/files/ventoinha/valor.txt");
$hora_ventoinha = file_get_contents("api/files/ventoinha/hora.txt");
$log_ventoinha = file_get_contents("api/files/ventoinha/log.txt");
$nome_ventoinha = file_get_contents("api/files/ventoinha/nome.txt");

$valor_buzzer = file_get_contents("api/files/campainha/valor.txt");
$hora_buzzer = file_get_contents("api/files/campainha/hora.txt");
$log_buzzer = file_get_contents("api/files/campainha/log.txt");
$nome_buzzer = file_get_contents("api/files/campainha/nome.txt");

$valor_alarme = file_get_contents("api/files/alarme/valor.txt");
$hora_alarme = file_get_contents("api/files/alarme/hora.txt");
$log_alarme = file_get_contents("api/files/alarme/log.txt");
$nome_alarme = file_get_contents("api/files/alarme/nome.txt");
$armado_alarme = file_get_contents("api/files/gatilho_alarme/valor.txt");

// Verifica se a página recebeu um pedido do tipo POST e se os campos nome, valor e hora foram enviados
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome'], $_POST['valor'], $_POST['hora'])) {
    // Guarda os dados recebidos do formulário em variáveis
    $nome = $_POST['nome'];
    $valor = $_POST['valor'];
    $hora = $_POST['hora'];
    
    // Regra de segurança: garante que o utilizador apenas consegue alterar os estados do 'led' ou da 'ventoinha' através deste bloco
    if ($nome === 'led' || $nome === 'ventoinha') {
        // Define o caminho para a pasta do atuador específico
        $dir = "api/files/" . $nome;
        
        // Sobrescreve os ficheiros valor.txt e hora.txt com os novos dados
        file_put_contents("$dir/valor.txt", $valor);
        file_put_contents("$dir/hora.txt", $hora);
        
        // Formata a string de registo separando a hora e o valor com um ponto e vírgula, e adiciona uma quebra de linha no fim
        $log = $hora . ";" . $valor . PHP_EOL;
        // Adiciona a nova string ao ficheiro log.txt sem apagar o conteúdo que já lá estava (FILE_APPEND)
        file_put_contents("$dir/log.txt", $log, FILE_APPEND);
    }
}

// Define o fuso horário padrão do PHP para Portugal, garantindo que as horas registadas estão corretas
date_default_timezone_set('Europe/Lisbon');

?>


<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <meta http-equiv="refresh" content="5">
    
    <title>Plataforma IOT</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous">

    <link rel="stylesheet" href="dashboard.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
          <a class="navbar-brand" href="dashboard.php">Dashboard EI-TI</a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarScroll">
            <ul class="navbar-nav me-auto my-2 my-lg-0 navbar-nav-scroll" style="--bs-scroll-height: 100px;">
              <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="dashboard.php">Home</a>
              </li>
              <!-- Verifica se é admin ou getor para mostrar a pagina historico na navbar -->
              <?php if($isAdmin || $isGestor): ?>
                <li class="nav-item">
                    <a class="nav-link" href="historico.php">Historico</a>
                </li>
              <?php endif; ?>
              <!-- Verifica se é admin para mostrar a pagina de configurações na navbar -->
              <?php if($isAdmin):?>
                <li class="nav-item">
                    <a class="nav-link" href="configuracao.php">Configuração</a>
                </li>
              <?php endif; ?>
            </ul>
            
            <form action="logout.php" class="d-flex" method="POST">
              <button class="btn btn-outline-secondary" type="submit">Logout</button>
            </form>
          </div>
        </div>
    </nav>
    
    <div class="container d-flex justify-content-around align-items-center">
        <div id="title-header">
            <h1>Servidor IoT</h1>
            <!-- Escreve o nome do usario de sessão  -->
            <p class="h6">user: <?php echo $_SESSION['username']?></p>
        </div>

        <img width="300" src="images/estg.png" alt="Logo estg">
    </div>
    
    <div class="container text-center">
        <div class="row justify-content-center g-4">
            
            <div class="col-sm-3">
                <div class="card">
                    <div class="card-header sensor">
                        <p class="text-center">
                            <?php echo "<strong>$nome_temperatura: </strong> $valor_temperatura";  ?>
                        </p>
                    </div>

                    <div class="card-body">
                       <?php
                        // Verifica se a temperatura lida do ficheiro é maior que 32 para mudar a imagem  
                        if($valor_temperatura>32){
                            echo "<img src='images/temperature-high.png' alt='temperature-high'>";
                        }else{
                            echo "<img src='images/temperature-low.png' alt='temperature-low'>";
                        }
                        ?>
                    </div>

                    <div class="card-footer">
                        <p class="text-center">
                            <strong>Atualização: </strong><?php echo $hora_temperatura;  ?>
                            <!-- Coloca o nome lido do ficheiro em letras muidas para que o historico reconheça -->
                            <a href="historico.php?nome=<?php echo strtolower($nome_temperatura)?>">Historico</a>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-sm-3">
                <div class="card">
                    <div class="card-header sensor">
                        <p class="text-center">
                            <?php echo "<strong>$nome_humidade: </strong> $valor_humidade";  ?>
                        </p>
                    </div>

                    <div class="card-body">
                        <?php 
                        if($valor_humidade>40){
                            echo "<img src='images/humidity-high.png' alt='humidity-high'>";
                        }else{
                            echo "<img src='images/humidity-low.png' alt='humidit-low'>";
                        }
                        ?>
                    </div>

                    <div class="card-footer">
                        <p class="text-center">
                            <strong>Atualização: </strong><?php echo $hora_humidade;  ?>
                            <a href="historico.php?nome=<?php echo strtolower($nome_humidade)?>">Historico</a>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-sm-3">
                <div class="card">
                    <div class="card-header atuador">
                        <p class="text-center">
                            <?php
                                if($valor_led == 1){
                                    echo "<strong>$nome_led: </strong> LIGADO";
                                }else{
                                    echo "<strong>$nome_led: </strong> DESLIGADO";
                                } 
                                
                            ?>
                        </p>
                    </div>

                    <div class="card-body">
                        <?php 
                        if($valor_led==1){
                            echo "<img src='images/light-on.png' alt='led_on'>";
                        }else{
                            echo "<img src='images/light-off.png' alt='led_off'>";
                        }
                        ?>
                    </div>

                    <div class="card-footer">
                        <p class="text-center">
                            <strong>Atualização: </strong><?php echo $hora_led;  ?>
                            <a href="historico.php?nome=<?php echo strtolower($nome_led)?>">Historico</a>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-sm-3">
                <div class="card">
                    <div class="card-header atuador">
                        <p class="text-center">
                            <?php
                                if($valor_ventoinha == 1){
                                    echo "<strong>$nome_ventoinha: </strong> LIGADO";
                                }else{
                                    echo "<strong>$nome_ventoinha: </strong> DESLIGADO";
                                } 
                                
                            ?>
                        </p>
                    </div>

                    <div class="card-body">
                        <?php 
                        if($valor_ventoinha==1){
                            echo "<img src='images/ventoinhaLigada.png' alt='ventoinha_on'>";
                        }else{
                            echo "<img src='images/ventoinhaDesligada.png' alt='ventoinha_off'>";
                        }
                        ?>
                    </div>

                    <div class="card-footer">
                        <p class="text-center">
                            <strong>Atualização: </strong><?php echo $hora_ventoinha;  ?>
                            <a href="historico.php?nome=<?php echo strtolower($nome_ventoinha)?>">Historico</a>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-sm-3">
                <div class="card">
                    <div class="card-header atuador">
                        <p class="text-center">
                            <?php
                            
                                if($valor_buzzer== 1){
                                    echo "<strong>$nome_buzzer: </strong> LIGADO";
                                }else{
                                    echo "<strong>$nome_buzzer: </strong> DESLIGADO";
                                } 
                                
                            ?>
                        </p>
                    </div>

                    <div class="card-body">
                        <?php if($valor_buzzer== 0): ?>
                            <img src="images/camera-off.png" alt="Câmera Desligada" style="max-width: 100px; opacity: 0.5;">
                        <?php else: ?>
                            <video id="video-campainha" autoplay playsinline style="width: 100%;"></video>
                            
                            <canvas id="canvas-foto" style="display:none;"></canvas>
                            
                            <img src="#" id="foto-tirada" alt="Foto da campainha" style="display:none; width:100%;  border: 3px solid red;" >
                            
                            <p id="status-cam" class="text-danger mt-2 fw-bold">Campainha tocou! A capturar...</p>

                            <script>
                                // Guarda a referência do elemento de vídeo e do parágrafo de estado do HTML
                                const video = document.getElementById('video-campainha');
                                const status = document.getElementById('status-cam');

                                // Invoca a API de dispositivos do navegador para solicitar o uso da câmara de vídeo
                                navigator.mediaDevices.getUserMedia({ video: true })
                                    .then(stream => {
                                        // Em caso de sucesso (permissão concedida), liga a transmissão (stream) ao elemento de vídeo
                                        video.srcObject = stream;
                                        
                                        // Atualiza o texto na interface para indicar sucesso
                                        status.innerText = 'Camera Ligada';
                                    })
                                    .catch(err => {
                                        // Em caso de falha (bloqueio de permissão ou falta de câmara), regista no console e informa o utilizador
                                        console.error("Erro na câmera:", err);
                                        status.innerText = "Erro: Câmera sem permissão ou inexistente.";
                                    });
                            </script>

                        <?php endif; ?>
                    </div>

                    <div class="card-footer">
                        <p class="text-center">
                            <strong>Atualização: </strong><?php echo $hora_buzzer;  ?>
                            <a href="historico.php?nome=<?php echo strtolower($nome_buzzer)?>">Historico</a>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-sm-3">
                <div class="card">
                    <div class="card-header atuador">
                        <p class="text-center">
                            <?php
                                if($valor_alarme == 1){
                                    echo "<strong>$nome_alarme: </strong> LIGADO";
                                }else{
                                    echo "<strong>$nome_alarme: </strong> DESLIGADO";
                                } 
                                
                            ?>
                        </p>
                    </div>

                    <div class="card-body">
                        <?php 
                        if($valor_alarme==1){
                            echo "<img src='images/AlarmeLigado.png' alt='alarme_on'>";
                        }else{
                            echo "<img src='images/AlarmeDesligado.png' alt='alarme_off'>";
                        }
                        ?>
                    </div>

                    <div class="card-footer">
                        <p class="text-center">
                            <strong>Atualização: </strong><?php echo $hora_alarme;  ?>
                            <a href="historico.php?nome=<?php echo strtolower($nome_alarme)?>">Historico</a>
                        </p>
                    </div>
                </div>
            </div>
             
            
            <!-- Verifica se é admin ou gestor para que os botoes para ligar a luz e a ventoinha apareçam -->                   
            <?php if($isAdmin || $isGestor): ?>
            <div class="card-body justify-content-center">
                
                <form action="dashboard.php" method="POST" class="mb-3" onsubmit="MudarEstado(event, this)">
                    <input type="hidden" name="nome" value="led">
                    <input type="hidden" name="valor" value="<?php echo ($valor_led == 1) ? '0' : '1'; ?>">
                    <input type="hidden" name="hora" value="<?php echo date('Y-m-d H:i:s'); ?>">

                    <button type="submit" class="btn <?php echo ($valor_led == 1) ? 'btn-danger' : 'btn-success'; ?> w-100">
                        <?php echo ($valor_led == 1) ? 'Desligar' : 'Ligar';?> luz
                    </button>
                </form>

                <form action="dashboard.php" method="POST" onsubmit="MudarEstado(event, this)">
                    <input type="hidden" name="nome" value="ventoinha">
                    <input type="hidden" name="valor" value="<?php echo ($valor_ventoinha == 1) ? '0' : '1'; ?>">
                    <input type="hidden" name="hora" value="<?php echo date('Y-m-d H:i:s'); ?>">

                    <button type="submit" class="btn <?php echo ($valor_ventoinha == 1) ? 'btn-danger' : 'btn-success'; ?> w-100">
                        <?php echo ($valor_ventoinha == 1) ? 'Desligar' : 'Ligar';?> ventoinha
                    </button>
                </form>

            </div>
            <?php endif; ?>
        </div>
        
        <br>
        
        <div class="col-sm-12">
            <div class="card tabela">
                <div class="card-header">
                    Tabela de Sensores & Atuadores
                </div>

                <div class="card-body">
                    <table class="table">

                        <thead>
                            <tr>
                                <th>Tipo de Dispositivo IoT</th>
                                <th>Valor</th>
                                <th>Data de Atualização</th>
                                <th>Estados</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td><?php echo $nome_temperatura ?></td>
                                <td><?php echo $valor_temperatura ?>°C</td>
                                <td><?php echo $hora_temperatura ?></td>
                                <td>
                                    <span class="badge rounded-pill bg-success">
                                        Normal
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <td><?php echo $nome_humidade ?></td>
                                <td><?php echo $valor_humidade ?></td>
                                <td><?php echo $hora_humidade ?></td>
                                <td>
                                    <span class="badge rounded-pill bg-danger">
                                        Elevada
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <td><?php echo $nome_led ?></td>
                                <td>
                                    <?php
                                        echo $valor_led
                                    ?>
                                </td>
                                <td><?php echo $hora_led ?></td>
                                <td>
                                    <span class="badge rounded-pill <?php echo ($valor_led == 1) ? 'bg-success' : 'bg-danger'; ?>   ;?>">
                                        <?php
                                        if($valor_led == 1){
                                            echo "Ligado";
                                        }else{
                                            echo "Desligado";
                                        }  
                                    ?>
                                    </span>
                                </td>
                            </tr>

                             <tr>
                                <td><?php echo $nome_buzzer ?></td>
                                <td>
                                    <?php
                                        echo $valor_buzzer
                                    ?>
                                </td>
                                <td><?php echo $hora_buzzer ?></td>
                                <td>
                                    <span class="badge rounded-pill <?php echo ($valor_buzzer == 1) ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php
                                        if($valor_led == 1){
                                            echo "Ligado";
                                        }else{
                                            echo "Desligado";
                                        }  
                                    ?>
                                    </span>
                                </td>
                            </tr>

                             <tr>
                                <td><?php echo $nome_alarme ?></td>
                                <td>
                                    <?php
                                        echo $valor_alarme
                                    ?>
                                </td>
                                <td><?php echo $hora_alarme ?></td>
                                <td>
                                    <span class="badge rounded-pill <?php echo ($armado_alarme == 1) ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php
                                        if($armado_alarme == 1){
                                            echo "Armado";
                                        }else{
                                            echo "Desarmado";
                                        }  
                                    ?>
                                    </span>
                                </td>
                            </tr>
                        
                            <tr>
                                <td><?php echo $nome_ventoinha ?></td>
                                <td>
                                    <?php
                                        echo $valor_ventoinha
                                    ?>
                                </td>
                                <td><?php echo $hora_ventoinha ?></td>
                                <td>
                                    <span class="badge rounded-pill <?php echo ($valor_ventoinha == 1) ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php
                                        if($valor_led == 1){
                                            echo "Ligado";
                                        }else{
                                            echo "Desligado";
                                        }  
                                    ?>
                                    </span>
                                </td>
                            </tr>

                        </tbody>

                    </table>
                </div>

            </div>
        </div>

    </div>

<script>

// Declara a função invocada quando o utilizador prime um botão de ligar/desligar na zona de controlo
function MudarEstado(evento, formulario) {
    // Interrompe o comportamento normal do formulário, que seria recarregar totalmente a página
    evento.preventDefault();

    // Cria um objeto contendo todos os dados dos campos de input presentes no formulário submetido
    let dados = new FormData(formulario);
    
    // Procura e guarda a referência do elemento 'button' que pertence ao formulário que disparou a ação
    let botao = formulario.querySelector('button');

    // Altera momentaneamente o texto do botão para informar o utilizador que o processo começou
    botao.innerHTML = "A enviar";

    // Inicia um pedido assíncrono para o URL definido no 'action' do formulário (o próprio dashboard.php)
    fetch(formulario.action, {
        // Define que os dados serão enviados via método HTTP POST
        method: 'POST',
        // O corpo do pedido contém os valores retirados do formulário (nome do atuador, estado desejado e hora)
        body: dados
    })
    // Assim que o servidor responde, transforma a resposta bruta em texto simples legível
    .then(resposta => resposta.text())
    // Após extrair o texto, avalia o que o servidor comunicou
    .then(texto => {
        // Se a reposta foi positiva (neste script em específico espera pela string literal "Sucesso", apesar de não estar implementada no lado do servidor)
        if (texto === "Sucesso") {
            // Atualiza o texto do botão finalizando o processo de feedback visual
            botao.innerHTML = "Feito!";
        }
    })
    // Caso ocorra alguma falha na comunicação de rede durante o pedido (ex. perda de net)
    .catch(erro => {
        // Imprime os detalhes técnicos do erro no console do navegador para ajudar no debug
        console.error("Erro:", erro);
        // Atualiza a interface gráfica do botão avisando o utilizador que a ação falhou
        botao.innerHTML = "Erro";
    });
}
</script>
</body>
</html>