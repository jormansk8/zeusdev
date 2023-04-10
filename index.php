<?php
session_start();
if (!isset($_SESSION["username"])) {
  header("Location: index.php");
  exit();
}

// Conexión a la base de datos PostgreSQL
$dbhost = "localhost";
$dbname = "exams";
$dbuser = "postgres";
$dbpass = "1234";

$dbconn = pg_connect("host=$dbhost dbname=$dbname user=$dbuser password=$dbpass");
$username = isset($_POST["username"]) ? pg_escape_string($_POST["username"]) : "";
$password = isset($_POST["password"]) ? pg_escape_string($_POST["password"]) : "";

if (!$dbconn) {
  die("Error al conectar a la base de datos.");
}
if (isset($_POST["username"])) {
  $username = pg_escape_string($_POST["username"]);
} else {
  $username = "";
}

if (isset($_POST["password"])) {
  $password = pg_escape_string($_POST["password"]);
} else {
  $password = "";
}
// Verificación de credenciales de inicio de sesión
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = isset($_POST["username"]) ? pg_escape_string($_POST["username"]) : "";
  $password = isset($_POST["password"]) ? pg_escape_string($_POST["password"]) : "";

  $query = "SELECT * FROM usuarios WHERE nombre_usuario = '$username' AND contraseña = '$password'";
  $result = pg_query($dbconn, $query);

  if (pg_num_rows($result) == 1) {
    // Credenciales correctas, iniciar sesión
    $_SESSION["username"] = $username;
    $_SESSION["password"] = $password;
    header("Location: dashboard.php");
    exit();
  } else {
    // Credenciales incorrectas, mostrar error en modal
    echo "<script>
            var modal = document.getElementById('myModal');
            var span = document.getElementsByClassName('close')[0];
            var errorMessage = document.getElementById('errorMessage');
            errorMessage.innerHTML = 'Nombre de usuario o contraseña incorrectos.';
            modal.style.display = 'block';
            span.onclick = function() {
              modal.style.display = 'none';
            }
            window.onclick = function(event) {
              if (event.target == modal) {
                modal.style.display = 'none';
              }
            }
          </script>";
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>ZEUSDEV | Login</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <style>
    body {
      background-color: #f5f5f5;
    }

    .card {
      margin-top: 50px;
      border-radius: 10px;
      box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
    }

    .card-header {
      background-color: #29ABE2;
      color: white;
      border-radius: 10px 10px 0px 0px;
    }

    .card-footer {
      background-color: white;
      border-radius: 0px 0px 10px 10px;
      border-top: none;
    }

    /* Añadir estilos para loading-container */
    #loading-container {
  background-color: #29ABE2;
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 9999;
}

#loading-container .logo {
  max-width: 100px;
  background-color: #29ABE2;
  padding: 5px;
  border-radius: 50%;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%) scale(1.35);
}

#loading-container .progress-container {
  width: calc(35% - 20px);
  height: 10px;
  background-color: #e0e0e0;
  border-radius: 5px;
  position: absolute;
  top: 60%;
  left: 50%;
  transform: translate(-50%, -50%);
}

#loading-container .progress-bar {
  width: 0%;
  height: 100%;
  background-color: #fff;
  border-radius: 5px;
  position: relative;
  animation: progress 1.6s ease-in-out infinite;
  animation-direction: alternate-reverse;
}

@keyframes progress {
  0% {
    width: 0%;
  }
  100% {
    width: 100%;
  }
}


  </style>
</head>

<body>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h4><i class="fas fa-user"></i> Iniciar sesión</h4>
          </div>
          <div class="card-body">
            
          <div class="card-footer text-muted text-center">
            <img src="/zeusdev.png" alt="Logo" style="max-width: 100px; background-color: #29ABE2; padding: 5px; border-radius: 50%;">
          </div>
            <form action="index.php" method="post" onsubmit="event.preventDefault(); showLoadingAndRedirect();">
              <div class="mb-3">
                <label for="username" class="form-label">Nombre de usuario:</label>
                <input type="text" name="username" id="username" class="form-control" required>
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Contraseña:</label>
                <input type="password" name="password" id="password" class="form-control" required>
              </div>
              <button type="submit" class="btn btn-primary">Iniciar sesión</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div id="myModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <p id="errorMessage"></p>
    </div>
  </div>
  <div id="loading-container">
    <img src="zeusdev.png" alt="ZeusDev Logo" class="logo">
    <div class="progress-container">
        <div class="progress-bar"></div>
    </div>
  </div>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.3/umd/popper.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.2/js/bootstrap.min.js"></script>
  <script>
    window.history.pushState({}, document.title, "/login_zeusdev");
    
    function showLoadingAndRedirect() {
      document.getElementById('loading-container').style.display = 'block';
      setTimeout(function() {
        document.querySelector('form').submit();
      }, 3000); // Puedes ajustar el tiempo de espera antes de la redirección (1000 ms = 1 segundo)
    }
  </script>
</body>
</html>

