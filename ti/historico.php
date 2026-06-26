<?php
// Inicia ou retoma a sessão atual para aceder às variáveis globais do utilizador
session_start();

// Verifica se a variável de sessão 'username' não está definida (utilizador não autenticado)
if(!isset($_SESSION['username'])){
    // Redireciona o utilizador para a página de login após 5 segundos
    header("refresh:5;url=index.php");
    // Interrompe imediatamente a execução da página e exibe uma mensagem
    exit("Acesso Restrito");
}

// Guarda o nome do utilizador da sessão numa variável para facilitar as verificações
$user = $_SESSION['username'];

// Cria uma variável booleana que será verdadeira se o utilizador for o 'admin'
$isAdmin = ($user === 'admin');

// Regra de segurança: verifica se o utilizador não tem perfil de 'gestor' nem de 'admin'
if($user !== 'gestor' && $user !== 'admin'){
    // Bloqueia a execução da página e mostra uma mensagem de erro fatal
    die("Erro 403: Acesso Negado. Esta página é exclusiva para a Gestão.");
}

// Verifica se o URL contém um parâmetro chamado 'nome' (ex: historico.php?nome=temperatura)
if(isset($_GET['nome'])){
    // Guarda o nome do sensor solicitado numa variável
    $nomeSensor = $_GET['nome'];
}else{
    // Se não houver nenhum parâmetro no URL, define a variável como nula para mostrar o histórico geral
    $nomeSensor = null;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Histórico IoT</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">

    <style>
        .card {
            /* Remove a borda padrão do cartão */
            border: 0;
            /* Arredonda os cantos */
            border-radius: 12px;
            /* Aplica uma sombra suave para dar efeito de profundidade */
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .header-box {
            /* Fundo azul claro e semitransparente para o cabeçalho das tabelas */
            background-color: rgba(13, 110, 253, 0.2);
            color: black;
            padding: 12px;
            /* Arredonda apenas os cantos superiores para encaixar no cartão */
            border-radius: 12px 12px 0 0;
            font-weight: bold;
        }

        .table th {
            /* Fundo ligeiramente cinzento para os títulos das colunas da tabela */
            background: #e9ecef;
        }

        .badge-sensor {
            /* Cor azul padrão para alguns distintivos (badges) */
            background: #0d6efd;
        }
    </style>
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
              <!-- Verifica se é admin para mostrar as configurações na navbar -->
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

<div class="container mt-4">

<!-- Verifica se existe um nomeSensor -->
<?php if (isset($nomeSensor)): ?>
    
    <a href="historico.php" class="btn btn-secondary mb-3">Voltar</a>

    <?php
        // Constrói o caminho para a pasta do sensor escolhido
        $dir = "api/files/" . $nomeSensor;
        // Constrói o caminho completo para o ficheiro de texto com os registos (logs)
        $file = $dir . "/log.txt";

        // Verifica se o ficheiro de histórico desse sensor realmente existe
        if (!file_exists($file)) {
            // Se não existir, mostra um erro visual e redireciona de volta após 5 segundos
            echo "<div class='alert alert-danger'>O ficheiro nao existe</div>";
            header("refresh:5;url=historico.php");
        } else {
            // Se existir, lê o ficheiro inteiro para um array (ignorando quebras de linha vazias)
            // A função array_reverse inverte a ordem para que os dados mais recentes apareçam no topo
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
                <!-- Faz um foreach nas linhas -->
                <?php foreach ($linhas as $l): ?>
                    <?php
                        // Divide a linha de texto pelo separador ponto e vírgula ";"
                        $d = explode(";", $l);
                        // Atribui a primeira parte à variável hora (se não existir, fica vazio)
                        $hora = $d[0] ?? '';
                        // Atribui a segunda parte à variável valor
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

<!-- Se nao tiver nomeSensor -->
<?php else: ?>
    <h1 class="h3 mb-4"><strong>Histórico Geral</strong></h1>

    <?php
        // Define a diretoria base onde todas as pastas dos sensores estão guardadas
        $base = "api/files/";

        // Verifica se a pasta principal existe no servidor
        if (!is_dir($base)) {
            // Se não existir, interrompe o carregamento e avisa o utilizador
            die("<div class='alert alert-danger'>Ficheiro files não encontrada</div>");
            header("refresh:5;url=dashboard.php");
        }

        // Lê todos os ficheiros e pastas de 'api/files/' e remove as referências de navegação interna ('.' e '..')
        $sensores = array_diff(scandir($base), ['.', '..']);
    ?>

    <div class="row">
    <!-- Faz um foreach nos nos sensores -->
    <?php foreach ($sensores as $sensor): ?>

        <?php
            // Define o caminho para o log.txt dentro da pasta do sensor atual
            $file = $base . $sensor . "/log.txt";
            
            // Se esta pasta não tiver um ficheiro log.txt, ignora e avança para o próximo sensor
            if (!file_exists($file)) continue;

            // Lê o ficheiro e inverte a ordem para os registos recentes ficarem primeiro
            $linhas = array_reverse(file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
        ?>

        <div class="col-lg-4 col-md-6 mb-3">

            <div class="card h-100">

                <div class="header-box d-flex justify-content-between align-items-center py-2">
                    <span><?php echo strtoupper($sensor);  ?></span>

                    <a class="btn btn-light btn-sm py-0 px-2"
                       href="historico.php?nome=<?php echo $sensor; ?>">
                        Ver
                    </a>
                </div>

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
                                // Separa os dados pela estrutura padrão do log (hora;valor)
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