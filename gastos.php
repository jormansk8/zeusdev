<?php
session_start();
// Conectar a la base de datos
$dbconn = pg_connect("host=localhost dbname=exams user=postgres password=1234");

// Si se envía el formulario, se insertan los datos en la base de datos
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['amount_spent']) && is_numeric($_POST['amount_spent'])) {
    $amount_spent = $_POST['amount_spent'];
    $query = "INSERT INTO expenses (amount_spent) VALUES ($1)";
    pg_prepare($dbconn, "", $query);
    pg_execute($dbconn, "", array($amount_spent));
    
    // Redirigir al usuario a la misma página
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Consulta para obtener el total de dinero recaudado
$query = "SELECT SUM(amount_to_charge) as total_recaudado FROM exams WHERE payment_status = 'pagado'";
$result = pg_query($query);
$row = pg_fetch_assoc($result);
$total_recaudado = $row['total_recaudado'];

// Consulta para obtener el total de dinero gastado
$query = "SELECT COALESCE(SUM(amount_spent),0) as total_gastado FROM expenses";
$result = pg_query($query);
$row = pg_fetch_assoc($result);
$total_gastado = $row['total_gastado'];

// Calcular el dinero restante
$dinero_restante = $total_recaudado - $total_gastado;

// Cerrar la conexión a la base de datos
pg_close($dbconn);
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZEUSDEV</title>
    <!-- Agregar referencia a Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        html,
        body {
            overflow-x: hidden;
        }

        body {
            font-family: "Roboto", "Helvetica", "Arial", sans-serif;
            font-size: 16px;
            line-height: 1.5;
        }

        .sidebar {
  width: 100%;
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
.conta{
    margin-top: 20px;
    margin-left: 220px;
}

    </style>
</head>
<body>
<div class="sidebar">
        <center><a href="/dashboard.php" class="logo"><img src="zeusdev.png" alt="Logo de Zeusdev" width="90" height="90" style="pointer-events:none;"></a></center>
        <a href="#" class="nav-link" onclick="logout()"><i class="fa fa-user"></i> Cerrar sesión</a>
        <a href="/form_insertar_datos.php"><i class="fa fa-pencil"></i> Ingresar exámenes</a>
        <a href="/ver_datos2.php"><i class="fa fa-calendar"></i> Calendarios</a>
        <a href="/control_datos.php"><i class="fa fa-cog"></i> Gestión</a>
        <a href="/gastos.php"><i class="fa fa-money"></i> Gastos</a>
    </div>


    <div class="logout-btn">
    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      <i class="fa fa-user"></i> <?php echo $_SESSION["username"]; ?>
    </button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
      <a class="dropdown-item" href="#" onclick="logout()"><i class="fa fa-sign-out"></i> Cerrar sesión</a>
    </div>
  </div>
    <br>
    <!-- Agregar el formulario y el gráfico dentro de un contenedor con clase "container" -->
    <div class="conta">
    <div class="container">
        <!-- Formulario para ingresar el dinero gastado -->
        <form method="post">
            <div class="form-group">
                <label for="amount_spent">Monto gastado:</label>
                <input type="number" step="0.01" min="0.00" class="form-control form-control-lg" placeholder="0.00" aria-label="Cantidad (al dólar más cercano)" id="amount_spent" name="amount_spent">
            </div>
            <button type="submit" class="btn btn-primary" name="submit" <?php if ($dinero_restante <= 0) echo 'disabled'; ?>>Agregar</button>
        </form>
        <div class="mt-3">
    <div class="bg-success rounded p-3">
        <p class="h4 text-white mb-0">Dinero restante: $<?php echo number_format($dinero_restante, 2); ?></p>
    </div>
</div>
<div class="mt-3">
    <div class="bg-danger rounded p-3">
        <p class="h4 text-white mb-0">Dinero gastado: $<?php echo number_format($total_gastado, 2); ?></p>
    </div>
</div>
    </div>

    <!-- Agregar un contenedor adicional con clase "container-fluid" para el gráfico -->
    <div class="container-fluid">
        <div class="card mt-3 mx-auto shadow-sm rounded"  style="background-color: whitesmoke; width: 70%;">
        <div class="bg-secondary card-header">
            <center><h4 class="text-white">Gráfica de gastos</h4></center>
        </div>
            <div class="card-body">
                <canvas id="myChart"></canvas>
            </div>
        </div>
    </div> 
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        var ctx = document.getElementById('myChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Gastado', 'Restante'],
                datasets: [{
                    label: 'Dinero ',
                    data: [<?php echo number_format($total_gastado, 2); ?>, <?php echo number_format($dinero_restante, 2); ?>],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.5)',
                        'rgba(40, 167, 69, 0.5)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(40, 167, 69, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: true
                    }
                },
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>
</body>
</html>