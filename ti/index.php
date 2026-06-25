<?php
session_start();
$erro_login = false;

if (isset($_POST['username'], $_POST['password'])) { 
  $users = []; 
  
  // Ler o ficheiro e popular o array
  if (file_exists('credenciais.txt')) {
      $linhas = file('credenciais.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
      foreach ($linhas as $linha) {
          if (strpos($linha, ' ') !== false) {
              list($username_do_ficheiro, $password_hash) = explode(' ', $linha, 2);
              $users[trim($username_do_ficheiro)] = trim($password_hash);
          }
      }
  }
  
  $user_inserido = trim($_POST['username']);    
  $pass_inserida = $_POST['password'];

  // Verificar se o user existe e a password coincide
  if (isset($users[$user_inserido]) && password_verify($pass_inserida, $users[$user_inserido])) {
      $_SESSION['username'] = $user_inserido;
      header('Location: dashboard.php'); 
      exit(); 
  } else {
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