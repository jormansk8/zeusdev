<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit();
}

$show_modal = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Conectarse a la base de datos
    $dbconn = pg_connect("host=localhost dbname=exams user=postgres password=1234");

    // Comprobar si la conexión se ha establecido correctamente
    if (!$dbconn) {
        echo "Error al conectar a la base de datos.";
        exit;
    }

    // Obtener los datos del formulario
    $client_name = $_POST['client_name'];
    $phone = $_POST['phone'];
    $amount_to_charge = $_POST['amount_to_charge'];
    $payment_status = $_POST['payment_status'];
    $exam_date = $_POST['exam_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    // Comprobar si la fecha y hora de inicio ya están ocupadas en la base de datos
    $sql_check = "SELECT * FROM exams WHERE exam_date = '$exam_date' AND ((start_time <= '$start_time' AND end_time >= '$start_time') OR (start_time <= '$end_time' AND end_time >= '$end_time'))";
    $result_check = pg_query($dbconn, $sql_check);

    if (pg_num_rows($result_check) > 0) {
        // Si la fecha y hora de inicio ya están ocupadas, mostrar un mensaje de error
        $show_modal = true;
    } else {
        // Si la fecha y hora de inicio están disponibles, insertar los datos en la base de datos
        $sql_insert = "INSERT INTO exams (client_name, phone, amount_to_charge, payment_status, exam_date, start_time, end_time) VALUES ('$client_name', '$phone', '$amount_to_charge', '$payment_status', '$exam_date', '$start_time', '$end_time')";
        $result_insert = pg_query($dbconn, $sql_insert);

        if (!$result_insert) {
            //echo "Error al insertar los datos en la tabla exams.";
            $show_modal = false;
        } else {
            //echo "Datos insertados correctamente en la tabla exams.";
            $show_modal = false;
        }
    }

    // Cerrar la conexión a la base de datos
    pg_close($dbconn);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Formulario de Exámenes</title>
    <!-- Agregar los estilos de Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Enlace a Material Design Lite -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.indigo-pink.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Enlace a Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
    <style>
        body {
            font-family: "Roboto", "Helvetica", "Arial", sans-serif;
            font-size: 16px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            padding-top: 50px;
        }

        .container {
            margin-left: 200px;
            height: 100%;
            padding-top: 0;
            width: 80%;
            transform: scale(0.9);
        }

        .sidebar {
            background-color: #29ABE2;
            height: 100%;
            position: fixed;
            top: 0;
            left: 0;
            width: 200px;
            z-index: 1;
            overflow-x: hidden;
            padding-top: 20px;
        }

        .sidebar a {
            display: block;
            color: #f2f2f2;
            padding: 16px;
            text-decoration: none;
        }

        .sidebar a:hover {
            background-color: white;
            color: black;
        }

        div.card {
            margin-top: -110px;
            max-height: 755px;
        }

        /* Eliminar hover en el logo */
        .sidebar a.logo:hover {
            background-color: transparent !important;
        }

        .logout-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1;
        }

        .logout-btn .btn {
            background-color: #29ABE2;
            color: #ffffff;
            border: none;
            border-radius: 3px;
            padding: 10px 15px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            transition: all 0.2s ease;
        }

        .logout-btn .dropdown-menu a {
            text-decoration: none;
            color: black;
            margin-left: 10px;
        }

        .logout-btn .dropdown-menu a:hover {
            text-decoration: none;
            color: #1c8bb9;
        }

        .logout-btn .btn:hover {
            background-color: #1c8bb9;
        }
    </style>
    <!-- Agregar los scripts de Bootstrap y el script para abrir el modal -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<body>
    <div class="sidebar">
        <center><a href="/dashboard.php" class="logo"><img src="zeusdev.png" alt="Logo de Zeusdev" width="90" height="90" style="pointer-events:none;"></a></center>
        <a href="#" class="nav-link" onclick="logout()"><i class="fa fa-user"></i> Cerrar sesión</a>
        <a href="/form_insertar_datos.php"><i class="fa fa-pencil"></i> Ingresar exámenes</a>
        <a href="/ver_datos2.php"><i class="fa fa-calendar"></i> Calendarios</a>
        <a href="/control_datos.php"><i class="fa fa-cog"></i> Gestión</a>
        <a href="/gastos.php"><i class="fa fa-money"></i> Gastos e ingresos</a>
    </div>

    <div class="modal fade" id="alert-modal" tabindex="-1" role="dialog" aria-labelledby="alert-modal-label">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="alert-modal-label">Fecha y Hora Ocupada</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    La fecha y hora seleccionadas ya están ocupadas. Por favor, seleccione otra fecha u hora.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
            <h2 class="mb-4">Formulario de Exámenes <i class="material-icons align-middle">description</i></h2>
            </div>
            <div class="card-body">
                <form method="post" action="form_insertar_datos.php">
                <div class="form-group">
    <label for="client_name">
        <i class="material-icons align-middle">person</i> Nombre del Cliente:
    </label>
    <input type="text" class="form-control" id="client_name" name="client_name" required>
</div>
<div class="form-group">
    <label for="phone">
        <i class="material-icons align-middle">phone</i> Teléfono:
    </label>
    <input type="text" class="form-control" id="phone" name="phone" required>
</div>
<div class="form-group">
    <label for="amount_to_charge">
        <i class="material-icons align-middle">attach_money</i> Monto a Cobrar:
    </label>
    <input type="number" step="0.01" class="form-control" id="amount_to_charge" name="amount_to_charge" required>
</div>
<div class="form-group">
    <label for="payment_status">
        <i class="material-icons align-middle">payment</i> Estado del Pago:
    </label>
    <select class="form-control" id="payment_status" name="payment_status" required>
        <option value="pendiente">Pendiente</option>
        <option value="pagado">Pagado</option>
        <option value="anulado">Anulado</option>
    </select>
</div>
<div class="form-group">
    <label for="exam_date">
        <i class="material-icons align-middle">today</i> Fecha del Examen:
    </label>
    <input type="date" class="form-control" id="exam_date" name="exam_date" required>
</div>
<div class="form-group">
    <label for="start_time">
        <i class="material-icons align-middle">access_time</i> Hora de Inicio:
    </label>
    <input type="time" class="form-control" id="start_time" name="start_time" required>
</div>
<div class="form-group">
    <label for="end_time">
        <i class="material-icons align-middle">access_time</i> Hora de Fin:
    </label>
    <input type="time" class="form-control" id="end_time" name="end_time" required>
</div>

                    <button type="submit" class="btn btn-primary">
                    Enviar 
                        <i class="material-icons align-middle mr-1">send</i>
                    </button>

                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            <?php if ($show_modal) { ?>
                $('#alert-modal').modal('show');
            <?php } ?>
        });
    </script>

</body>

</html>