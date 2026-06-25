<?php 
session_start();
if(!isset($_SESSION['username'])){
    header("refresh:5;url=index.php");
    exit("Acesso Restrito");
}
$user = $_SESSION['username'];
$isAdmin = ($user === 'admin');
$isGestor = ($user === 'gestor');

//variaveis
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome'], $_POST['valor'], $_POST['hora'])) {
    $nome = $_POST['nome'];
    $valor = $_POST['valor'];
    $hora = $_POST['hora'];
    
    // Segurança: só deixa mexer no led ou ventoinha por aqui
    if ($nome === 'led' || $nome === 'ventoinha') {
        $dir = "api/files/" . $nome;
        
        // Atualiza valor e hora
        file_put_contents("$dir/valor.txt", $valor);
        file_put_contents("$dir/hora.txt", $hora);
        
        // Bónus: Atualiza também o log.txt para o teu histórico!
        $log = $hora . ";" . $valor . PHP_EOL;
        file_put_contents("$dir/log.txt", $log, FILE_APPEND);
    }
}
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
              <?php if($isAdmin || $isGestor): ?>
                <li class="nav-item">
                    <a class="nav-link" href="historico.php">Historico</a>
                </li>
              <?php endif; ?>
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
            <p class="h6">user: <?php echo $_SESSION['username']?></p>
        </div>

        <img width="300" src="images/estg.png" alt="Logo estg">
    </div>
    
    <!-- Esta parte é o que contem os cartões dos sensores e atuadores  -->
    <div class="container text-center">
        <div class="row justify-content-center g-4">
            
            <!-- Temperatura  -->
            <div class="col-sm-3">
                <div class="card">
                    <div class="card-header sensor">
                        <p class="text-center">
                            <?php echo "<strong>$nome_temperatura: </strong> $valor_temperatura";  ?>
                        </p>
                    </div>

                    <div class="card-body">
                       <?php 
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
                            <a href="historico.php?nome=<?php echo strtolower($nome_temperatura)?>">Historico</a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Humidade  -->
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
            <!-- Luz  -->
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
            <!-- ventoinha  -->
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
            <!-- Campainha  -->
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
                                // Apanha apenas o vídeo e o texto de estado
                                const video = document.getElementById('video-campainha');
                                const status = document.getElementById('status-cam');

                                // Permissão para usar a câmara
                                navigator.mediaDevices.getUserMedia({ video: true })
                                    .then(stream => {
                                        //Envia a imagem da câmara diretamente para o leitor de vídeo no ecrã
                                        video.srcObject = stream;
                                        
                                        //Atualiza o texto para mostrar que está a gravar
                                        status.innerText = 'Camera Ligada';
                                    })
                                    .catch(err => {
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
            <!-- Alarme  -->
            <!-- ventoinha  -->
            <div class="col-sm-3">
                <div class="card">
                    <div class="card-header atuador">
                        <p class="text-center">
                            <?php
                                if($valor_ventoinha == 1){
                                    echo "<strong>$nome_alarme: </strong> LIGADO";
                                }else{
                                    echo "<strong>$nome_alarme: </strong> DESLIGADO";
                                } 
                                
                            ?>
                        </p>
                    </div>

                    <div class="card-body">
                        <?php 
                        if($valor_ventoinha==1){
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

            <!-- Zona de controlo apenas admin e gestor  -->
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
function MudarEstado(evento, formulario) {
    //Trava o reload da página
    evento.preventDefault();

    //Prepara a encomenda
    let dados = new FormData(formulario);
    let botao = formulario.querySelector('button');

    //Feedback visual imediato
    botao.innerHTML = "A enviar";

    //Envia para o topo do próprio dashboard.php
    fetch(formulario.action, {
        method: 'POST',
        body: dados
    })
    .then(resposta => resposta.text())
    .then(texto => {
        if (texto === "Sucesso") {
            botao.innerHTML = "Feito!";
        }
    })
    .catch(erro => {
        console.error("Erro:", erro);
        botao.innerHTML = "Erro";
    });
}
</script>
</body>
</html>