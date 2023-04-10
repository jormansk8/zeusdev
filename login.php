<?php
session_start();

// Conexión a la base de datos PostgreSQL
$dbhost = "localhost";
$dbname = "exams";
$dbuser = "postgres";
$dbpass = "1234";

$dbconn = pg_connect("host=$dbhost dbname=$dbname user=$dbuser password=$dbpass");

if (!$dbconn) {
  die("Error al conectar a la base de datos.");
}

// Verificación de credenciales de inicio de sesión
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = pg_escape_string($_POST["username"]);
  $password = pg_escape_string($_POST["password"]);

  $query = "SELECT * FROM usuarios WHERE nombre_usuario = '$username' AND contraseña = '$password'";
  $result = pg_query($dbconn, $query);

  if (pg_num_rows($result) == 1) {
    // Credenciales correctas, iniciar sesión
    $_SESSION["username"] = $username;
    header("Location: loading.php");
  } else {
    // Credenciales incorrectas, mostrar error
    $error = "Nombre de usuario o contraseña incorrectos.";
  }
}
?>