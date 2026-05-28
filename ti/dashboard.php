<?php 
  session_start();
  if(!isset($_SESSION['username'])){
    header("refresh:5;url=index.php");
    die("Acesso Restrito");
  }
  
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

    <link rel="stylesheet" href="style.css">
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
              <li class="nav-item">
                <a class="nav-link" href="historico.php">Historico</a>
              </li>
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
            <h6>user:Alexandre Lopes</h6>
        </div>

        <img width="300" src="images/estg.png" alt="Logo estg">
    </div>

    <div class="container text-center">
        <div class="row justify-content-center">

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
                            <strong>Atualizacao: </strong><?php echo $hora_temperatura;  ?>
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
                            <strong>Atualizacao: </strong><?php echo $hora_humidade;  ?>
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
                            <strong>Atualizacao: </strong><?php echo $hora_led;  ?>
                            <a href="historico.php?nome=<?php echo strtolower($nome_led)?>">Historico</a>
                        </p>
                    </div>
                </div>
            </div>

        </div>
        <br>
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <th>Tabela de Sensores</th>
                </div>

                <div class="card-body">
                    <table class="table">

                        <thead>
                            <tr>
                                <th>Tipo de Dispositivo IoT</th>
                                <th>Valor</th>
                                <th>Data de Atualização</th>
                                <th>Estados Alertas</th>
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
                                        if($valor_led == 1){
                                            echo "LIGADO";
                                        }else{
                                            echo "DESLIGADO";
                                        }  
                                    ?>
                                </td>
                                <td><?php echo $hora_led ?></td>
                                <td>
                                    <span class="badge rounded-pill bg-dark">
                                        Desconhecido
                                    </span>
                                </td>
                            </tr>




                        </tbody>

                    </table>
                </div>

            </div>
        </div>

    </div>

</body>
</html>