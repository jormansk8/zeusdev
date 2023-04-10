<?php
session_start();

if (!isset($_SESSION["username"]) || !isset($_SESSION["password"])) {
    // Si no se ha iniciado sesión, redirigir al inicio de sesión
    header("Location: index.php");
    exit();
}

if (!isset($_GET["id"])) {
    // Si no se proporcionó un ID de examen, redirigir al dashboard
    header("Location: dashboard.php");
    exit();
}

// Conexión a la base de datos PostgreSQL
$dbhost = "localhost";
$dbname = "exams";
$dbuser = "postgres";
$dbpass = "1234";

$dbconn = pg_connect("host=$dbhost dbname=$dbname user=$dbuser password=$dbpass");

if (!$dbconn) {
    die("Error al conectar a la base de datos.");
}

$id = pg_escape_string($_GET["id"]);

// Obtener información del examen
$query = "SELECT * FROM exams WHERE id = '$id'";
$result = pg_query($dbconn, $query);

if (pg_num_rows($result) != 1) {
    // Si no se encontró el examen, redirigir al dashboard
    header("Location: dashboard.php");
    exit();
}

$row = pg_fetch_assoc($result);

// Solo permitir imprimir ticket si el examen está pagado
if ($row['payment_status'] != 'pagado') {
    // Si el examen no está pagado, redirigir al dashboard
    header("Location: dashboard.php");
    exit();
}

// Obtener información del usuario que inició sesión
$username = $_SESSION["username"];
$query = "SELECT * FROM usuarios WHERE nombre_usuario = '$username'";
$result = pg_query($dbconn, $query);
$user = pg_fetch_assoc($result);

// Verificar si ya tiene un número de ticket asignado
if ($row['ticket_number'] == null) {
    // Generar número de ticket
    $ticket_number = time();

    // Actualizar registro del examen con el número de ticket generado
    $query = "UPDATE exams SET ticket_number = $ticket_number WHERE id = '$id'";
    $result = pg_query($dbconn, $query);

    // Obtener información del examen actualizada
    $query = "SELECT * FROM exams WHERE id = '$id'";
    $result = pg_query($dbconn, $query);
    $row = pg_fetch_assoc($result);
} else {
    $ticket_number = $row['ticket_number'];
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket de Examen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.2/css/bootstrap.min.css">
    <style>
        @media print {
            .card-footer {
                display: none;
            }
        }
        .card{
            height: auto;
            display: block;
            margin: 0 auto;
        }
        .logo {
            max-width: 30%;
            height: auto;
            display: block;
            margin: 0 auto;
            filter: invert(100%);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-ticket-alt"></i> Ticket de Examen</h4>
                    </div>
                    <div class="card-body">
                        <img class="logo" src="/zeusdev.png" alt="Logo de ZeusDev">
                        <p><strong>Número de Ticket:</strong> <?php echo $ticket_number; ?></p>
                        <p><strong>Nombre del Cliente:</strong> <?php echo $row['client_name']; ?></p>
                        <p><strong>Teléfono:</strong> <?php echo $row['phone']; ?></p>
                        <p><strong>Monto a Cobrar:</strong> $<?php echo $row['amount_to_charge']; ?></p>
                        <p><strong>Fecha del Examen:</strong> <?php echo $row['exam_date']; ?></p>
                        <p><strong>Hora de Inicio:</strong> <?php echo $row['start_time']; ?></p>
                        <p><strong>Hora de Finalización:</strong> <?php echo $row['end_time']; ?></p>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary" onclick="window.print()">Imprimir</button>
                        <a href="dashboard.php" class="btn btn-secondary">Volver al Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
