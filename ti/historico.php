<?php
<<<<<<< Updated upstream
=======
//verifica se fez login
session_start();


if(!isset($_SESSION['username'])){
    header("refresh:5;url=index.php");
    exit("Acesso Restrito");
}

//verifica se é gestor ou admin
$user = $_SESSION['username'];
$isAdmin = ($user === 'admin');
if($user !== 'gestor' && $user !== 'admin'){
    // Bloqueia a execução da página e mostra uma mensagem de erro
    die("Erro 403: Acesso Negado. Esta página é exclusiva para a Gestão.");
}

>>>>>>> Stashed changes
if(isset($_GET['nome'])){
    $nomeSensor = $_GET['nome'];
}else{
    $nomeSensor = null;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Histórico IoT</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
    <style>
        body {
            background: #f4f6f9;
        }

        .card {
            border: 0;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .header-box {
            background: #0d6efd;
            color: white;
            padding: 12px;
            border-radius: 12px 12px 0 0;
            font-weight: bold;
        }

        .table th {
            background: #e9ecef;
        }

        .badge-sensor {
            background: #0d6efd;
        }
    </style>
</head>

<body>

<div class="container mt-4">

<?php if (isset($nomeSensor)): ?>

    <!-- 🔵 HISTÓRICO INDIVIDUAL -->
    <a href="historico.php" class="btn btn-secondary mb-3">Voltar</a>

    <?php
        $dir = "api/files/" . $nomeSensor;
        $file = $dir . "/log.txt";

        if (!file_exists($file)) {
            echo "<div class='alert alert-danger'>O ficheiro nao existe</div>";
            header("refresh:5;url=historico.php");
        } else {

            $linhas = array_reverse(file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
    ?>

    <div class="card">
        <div class="header-box">Sensor: <?php echo strtoupper($nomeSensor); ?></div>

        <div class="card-body">

            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Hora</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>

                <?php foreach ($linhas as $l): ?>
                    <?php
                        $d = explode(";", $l);
                        $hora = $d[0] ?? '';
                        $valor = $d[1] ?? '';
                    ?>
                    <tr>
                        <td><?php echo $hora; ?></td>
                        <td><span class="badge bg-primary"><?php echo $valor; ?></span></td>
                    </tr>
                <?php endforeach; ?>

                </tbody>
            </table>

        </div>
    </div>

    <?php } ?>

<?php else: ?>

    <!-- 🟢 HISTÓRICO GERAL -->
    <h3 class="mb-4"><strong>Histórico Geral</strong></h3>

    <?php
        $base = "api/files/";

        if (!is_dir($base)) {
            die("<div class='alert alert-danger'>Ficheiro files não encontrada</div>");
            header("refresh:5;url=dashboard.php");
        }

        $sensores = array_diff(scandir($base), ['.', '..']);
    ?>

    <div class="row">

    <?php foreach ($sensores as $sensor): ?>

        <?php
            $file = $base . $sensor . "/log.txt";
            if (!file_exists($file)) continue;

            $linhas = array_reverse(file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
        ?>

        <div class="col-lg-4 col-md-6 mb-3">

            <div class="card h-100">

                <!-- HEADER -->
                <div class="header-box d-flex justify-content-between align-items-center py-2">
                    <span><?php echo strtoupper($sensor);  ?></span>

                    <a class="btn btn-light btn-sm py-0 px-2"
                       href="historico.php?nome=<?php echo $sensor; ?>">
                        Ver
                    </a>
                </div>

                <!-- BODY -->
                <div class="card-body p-2">

                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Valor</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php foreach (array_slice($linhas, 0, 4) as $l): ?>
                            <?php
                                $d = explode(";", $l);
                                $hora = $d[0] ?? '';
                                $valor = $d[1] ?? '';
                            ?>
                            <tr>
                                <td><?php echo $hora; ?></td>
                                <td>
                                    <span class="badge bg-primary">
                                        <?php echo $valor; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        </tbody>
                    </table>

                </div>

            </div>

        </div>

    <?php endforeach; ?>

    </div>

<?php endif; ?>

</div>

</body>
</html>