<?php
  
  /*if (isset($_POST['username'])){
  echo "O username submetido foi: ".$_POST['username']."<br>";
  }
  if (isset($_POST['password'])){
  echo "A password submetida foi: ".$_POST['password']."<br>";
  }
  */
  
  session_start();
  $username="admin";
  #admin1234
  $password_hash= '$2y$10$Cums9KctaZDiFw6.EpTVxe7y8/ue9aZx4FwU5FC/4OBGcITJ3PveK';

   if(isset($_POST['username'],$_POST['password'])){ 
    if($username == $_POST['username'] and password_verify($_POST['password'],$password_hash)){
      header('refresh:0;url=dashboard.php');
      $_SESSION['username'] = $_POST['username'];
    }
    // else{
    //   echo "Credenciais Incorretas";
    // }
  }

?>



<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
</head>
  <body>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <div class="container">
        <div class="row justify-content-center">
            <form class="AulaForm" method="post">
                <a href="index.php"><img src="images/estg_h.png" alt="logo estg"></a>
                <div class="mb-3">
                    <label for="username" class="form-label">Username:</label>
                    <input name="username" type="text" class="form-control" id="username" placeholder="Insira o seu username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password:</label>
                    <input name="password" type="password" class="form-control" id="password" placeholder="Insira a sua password" required>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>

    </div>



</body>
</html>