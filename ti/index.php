<?php
// A passe para o login é sempre o utlizador123 onde o utilizador é o texto a esquerda no ficheiro "credenciais.txt"
session_start();

// Variável que controla se ocorreu um erro durante a tentativa de login
$erro_login = false;

// Verifica se o formulário foi submetido (se existem os campos username e password no POST)
if (isset($_POST['username'], $_POST['password'])) { 
  // Cria um array vazio para armazenar os utilizadores e passwords lidos do ficheiro
  $users = []; 
  
  // Verifica se o ficheiro de texto com as credenciais existe no servidor
  if (file_exists('credenciais.txt')) {
      // Lê o ficheiro para um array, ignorando quebras de linha e linhas vazias
      $linhas = file('credenciais.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
      
      // Percorre cada linha lida do ficheiro
      foreach ($linhas as $linha) {
          // Verifica se existe um espaço na linha (que separa o utilizador da password)
          if (strpos($linha, ' ') !== false) {
              // Divide a linha em duas partes a partir do primeiro espaço encontrado
              list($username_do_ficheiro, $password_hash) = explode(' ', $linha, 2);
              
              // Guarda no array $users o nome de utilizador como chave e a password como valor, removendo espaços extra
              $users[trim($username_do_ficheiro)] = trim($password_hash);
          }
      }
  }
  
  // Guarda o username inserido no formulário e remove os espaços em branco no início e no fim
  $user_inserido = trim($_POST['username']);    
  // Guarda a password inserida no formulário
  $pass_inserida = $_POST['password'];

  // Verifica se o utilizador existe no array e se a password inserida corresponde ao hash guardado
  if (isset($users[$user_inserido]) && password_verify($pass_inserida, $users[$user_inserido])) {
      // Em caso de sucesso, guarda o nome do utilizador na variável global de sessão
      $_SESSION['username'] = $user_inserido;
      
      // Redireciona o utilizador para a página principal (dashboard)
      header('Location: dashboard.php'); 
      
      // Termina imediatamente a execução do script para garantir que o redirecionamento ocorre de forma segura
      exit(); 
  } else {
      // Se as credenciais estiverem erradas ou o utilizador não existir, ativa a variável de erro
      $erro_login = true;
  }
}
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>Login ESTG</title> 
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    
    <link rel="stylesheet" href="style.css">
    
    <style>
        body {
          background-image: url("images/login.webp");
          background-size: cover;      
          background-position: center;  
          background-attachment: fixed;
          margin: 0;                    
        } 
        .AulaForm {
          background-color: rgba(255, 255, 255, 0.85); 
          padding: 40px; 
          border-radius: 15px; 
          width: 100%;
          max-width: 450px;
          box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2);
        }
    </style>
  </head>
  
  <body>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    
    <div class="container min-vh-100 d-flex justify-content-center align-items-center">
        
        <form class="AulaForm" method="post">
            
            <div class="text-center mb-4">
                <a href="index.php"><img src="images/estg_h.png" alt="logo estg" class="img-fluid"></a>
            </div>
            
            <div class="mb-3">
                <label for="username" class="form-label">Username:</label>
                <input name="username" type="text" class="form-control <?php if($erro_login) { echo 'is-invalid'; } ?>" id="username" placeholder="Insira o seu username" required>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input name="password" type="password" class="form-control <?php if($erro_login) { echo 'is-invalid'; } ?>" id="password" placeholder="Insira a sua password" required>
                
                <?php if($erro_login): ?>
                  <div class="invalid-feedback">
                    Username ou password incorretos.
                  </div>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">Submit</button>
        </form>
    </div>
  </body>
</html>