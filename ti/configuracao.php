<?php
// Inicia ou retoma a sessão atual para aceder aos dados do utilizador
session_start();

// Verifica se o utilizador não está autenticado (se a variável de sessão não existe)
if(!isset($_SESSION['username'])){
    // Redireciona o utilizador de volta para a página de login após 5 segundos
    header("refresh:5;url=index.php");
    // Interrompe imediatamente a execução da página e exibe uma mensagem
    exit("Acesso Restrito");
}

// Guarda o nome do utilizador da sessão numa variável
$user = $_SESSION['username'];

// Regra de segurança rígida: verifica se o utilizador é diferente de 'admin'
if($user !== 'admin'){
    // Se não for admin (por exemplo, for um gestor), bloqueia o acesso totalmente e mostra mensagem de erro
    die("Erro 403: Acesso Negado. Esta página é exclusiva para o admin.");
}

// Verifica se a página recebeu dados vindos de um formulário através do método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Se o formulário enviou um valor para 'temp_alvo' (Temperatura Alvo)
    if (isset($_POST['temp_alvo'])) {
        // Guarda esse novo valor diretamente no ficheiro de texto correspondente, substituindo o anterior
        file_put_contents("api/files/tAlvo/valor.txt", $_POST['temp_alvo']);
    }
    
    // Se o formulário enviou um valor para 'estado_alarme'
    if (isset($_POST['estado_alarme'])) {
        // Guarda esse novo estado (0 ou 1) no ficheiro do gatilho do alarme
        file_put_contents("api/files/gatilho_alarme/valor.txt", $_POST['estado_alarme']);
    }
    
    // Após gravar os dados, redireciona a página para ela própria para limpar o histórico do POST 
    // e evitar reenvios de formulário se o utilizador atualizar a página
    header("Location: configuracao.php");
    exit();
}

// Lê os valores atuais gravados nos ficheiros de texto para poder mostrá-los nos formulários
$temp_alvo_atual = file_get_contents("api/files/tAlvo/valor.txt");
$alarme_atual = file_get_contents("api/files/gatilho_alarme/valor.txt");

// Valores por defeito (fallback): 
// Se o ficheiro da temperatura estiver vazio ou não existir, assume o valor 25
if (!$temp_alvo_atual) $temp_alvo_atual = 25; 
// Se o ficheiro do alarme estiver vazio ou não existir, assume o valor 0 (Desarmado)
if (!$alarme_atual) $alarme_atual = 0; 
?>

<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configurações IOT</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous">

    <link rel="stylesheet" href="dashboard.css">
    
    <style>
    .card-header{
        /* Força a cor de fundo do cabeçalho dos cartões a ser um azul transparente */
        background-color: rgba(13, 110, 253, 0.4) !important; 
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
                  <a class="nav-link" href="dashboard.php">Home</a>
              </li>
              <li class="nav-item">
                  <a class="nav-link" href="historico.php">Historico</a>
              </li>
              <li class="nav-item">
                  <a class="nav-link active" aria-current="page" href="configuracao.php">Configuração</a>
              </li>
            </ul>
            <form action="logout.php" class="d-flex" method="POST">
              <button class="btn btn-outline-secondary" type="submit">Logout</button>
            </form>
          </div>
        </div>
    </nav>
    
    <div class="container mt-4 mb-4 text-center">
        <p class="h2">Painel de Configurações</p>
        <p class="text-muted">Apenas o Administrador pode alterar estes valores.</p>
    </div>

    <div class="container">
        <div class="row justify-content-center g-4">
            
            <div class="col-sm-4">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white text-center">
                        <strong>Temperatura Alvo (Ventoinha)</strong>
                    </div>

                    <div class="card-body">
                        <form action="configuracao.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label">A ventoinha liga aos (°C):</label>
                                <input type="number" name="temp_alvo" class="form-control" value="<?php echo $temp_alvo_atual?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Atualizar Temperatura</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-sm-4">
                <div class="card h-100">
                    <div class="card-header bg-danger text-white text-center">
                        <strong>Sistema de Alarme (Buzzer)</strong>
                    </div>

                    <div class="card-body">
                        <form action="configuracao.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Estado do Alarme: <?php echo($alarme_atual == 1)? 'Armado' : 'Desarmado';?></label>
                                <select name="estado_alarme" class="form-select">
                                    <option value="1">Armado (Ativo)</option>
                                    <option value="0">Desarmado (Inativo)</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-danger w-100">Atualizar Alarme</button>
                        </form>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</body>
</html>