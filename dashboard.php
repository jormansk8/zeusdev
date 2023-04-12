<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit();
}
// Conectar a la base de datos
$dbconn = pg_connect("host=localhost dbname=exams user=postgres password=1234");

// Contar la cantidad de exámenes
$sql_count_exams = "SELECT COUNT(*) as count FROM exams WHERE client_name <> 'DINERO EN CUENTA'";
$result_count_exams = pg_query($dbconn, $sql_count_exams);
$row_count_exams = pg_fetch_assoc($result_count_exams);
$count_exams = $row_count_exams['count'];

// Contar la cantidad de clientes
$sql_count_clients = "SELECT COUNT(DISTINCT client_name) as count FROM exams  WHERE client_name <> 'DINERO EN CUENTA'";
$result_count_clients = pg_query($dbconn, $sql_count_clients);
$row_count_clients = pg_fetch_assoc($result_count_clients);
$count_clients = $row_count_clients['count'];

// Obtener la lista de próximos exámenes
$current_date = date('Y-m-d');
//$sql_upcoming_exams = "SELECT * FROM exams WHERE exam_date > NOW() and payment_status <> 'anulado' ORDER BY exam_date, start_time ASC";
$sql_upcoming_exams = "SELECT *
FROM exams WHERE exam_date >= CURRENT_DATE AND (exam_date > CURRENT_DATE OR start_time >= CURRENT_TIME) AND payment_status <> 'anulado' ORDER BY exam_date, start_time ASC";
$result_upcoming_exams = pg_query($dbconn, $sql_upcoming_exams);

//Obtener total por cobrar
$sql_total_amount = "SELECT SUM(amount_to_charge) as total_amount FROM exams WHERE payment_status = 'pendiente';";
$result_total_amount = pg_query($dbconn, $sql_total_amount);
$row_total_amount = pg_fetch_assoc($result_total_amount);
$total_amount = $row_total_amount['total_amount'];

//Obtener total cobrado
$sql_pay = "SELECT SUM(amount_to_charge) as pay FROM exams WHERE payment_status = 'pagado';";
$result_pay = pg_query($dbconn, $sql_pay);
$row_pay = pg_fetch_assoc($result_pay);
$total_pay = $row_pay['pay'];

// Consulta para obtener el total de dinero gastado
$query = "SELECT COALESCE(SUM(amount_spent),0) as total_gastado FROM expenses";
$result = pg_query($query);
$row = pg_fetch_assoc($result);
$total_gastado = $row['total_gastado'];

// Calcular el dinero restante
$dinero_restante = $total_pay - $total_gastado;

// Mostrar los resultados en el dashboard
?>


<!DOCTYPE html>
<html>

<head>
    <title>ZEUSDEV</title>
    <!-- Enlace a Bootstrap 5 -->
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <!-- Enlace a Material Design Lite -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.indigo-pink.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Enlace a Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>

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

        .main {
            margin-left: 200px;
            padding: 0px 10px;
        }

        footer {
            margin-top: 20px;
            margin-left: 200px;
        }

        .card {
            margin-bottom: 20px;
        }

        .card-body h4 {
            font-size: 1.25rem;
            margin-bottom: 10px;
        }

        .card-body p {
            font-size: 1rem;
            margin-bottom: 0;
        }

        /* Agregamos estilos para la sección "Próximos exámenes" */
        .upcoming-exams {
            max-height: calc(100vh - 200px);
            /* Establecemos la altura máxima de la sección para que ocupe el espacio restante de la ventana */
            overflow-y: scroll;
            /* Agregamos scroll vertical para los contenidos que superen la altura máxima */
        }

        .upcoming-exams .card {
            margin-bottom: 10px;
        }

        .fondo {
            background-color: #29ABE2;
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


    <div class="main">

        <h1 class="text-center" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);">
            DASHBOARD
        </h1>

        <div class="row" style="margin-left: 0.02px;">
            <div class="col-md-3" style="width: fit-content;">
                <div class="card shadow-sm hover-shadow">
                    <div class="card-body text-center mx-3">
                        <h4 class="card-title mx-4"><i class="fa fa-file"></i> Cantidad de exámenes</h4>
                        <p class="card-text"><?php echo $count_exams; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3" style="width: fit-content;">
                <div class="card shadow-sm">
                    <div class="card-body text-center mx-3">
                        <h4 class="card-title mx-4"><i class="fa fa-users"></i> Clientes registrados</h4>
                        <p class="card-text"><?php echo $count_clients; ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-3" style="width: fit-content;">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <center>
                            <table>
                                <tr class="text-center mx-3">
                                    <th class="text-center mx-3">
                                        <h4 class="card-title mx-4"><i class="fa fa-check-circle" style="font-size: 15px;"></i> Cobrado</h4>
                                    </th>
                                    <th class="text-center">
                                        <h4 class="card-title mx-4"><i class="fa fa-money fa" style="font-size: 15px;"></i> Pendiente</h4>
                                    </th>
                                    <th class="text-center">
                                        <h4 class="card-title"><i class="fa fa-usd" style="font-size: 15px;"></i> Total</h4>
                                    </th>
                                </tr>
                                <tr>
                                    <td class="text-center">
                                        <p class="card-text">$ <?php echo $total_pay; ?></p>
                                    </td>
                                    <td class="text-center">
                                        <p class="card-text">$ <?php echo $total_amount; ?></p>
                                    </td>
                                    <td class="text-center">
                                        <p class="card-text">$ <?php echo ($total_amount + $total_pay); ?></p>
                                    </td>
                                </tr>
                            </table>
                        </center>
                    </div>
                </div>
            </div>


            <div class="col-md-3" style="width: 150px; height: fit-content;">
                <div class="card shadow-sm">
                    <div class="card-body text-center ">
                        <center>
                            <table>
                                <tr class="text-center">
                                    <th class="text-center">
                                        <h4 class="card-title"><i class="material-icons rounded-circle" style="font-size: 15px;">account_balance_wallet</i> Wallet</h4>
                                    </th>
                                </tr>
                                <tr>
                                    <td class="text-center">
                                        <p class="card-text">$ <?php echo $dinero_restante; ?></p>
                                    </td>
                                </tr>
                            </table>
                        </center>
                    </div>
                </div>
            </div>


        </div>
        <div class="row">
            <div class="col-md-4 ">
                <div class="card mb-0 shadow-sm">
                    <div class="card-body scrollable" style="max-height: 370px; overflow: scroll;">
                        <h4 class="card-title"><i class="fa fa-clock-o"></i> Próximos exámenes</h4>
                        <?php
                        $colors = array('bg-light', 'fondo text-white');
                        $i = 0;
                        while ($row_upcoming_exams = pg_fetch_assoc($result_upcoming_exams)) {
                            $color_index = $i % count($colors);
                            $color_class = $colors[$color_index];
                            echo "<div class='card $color_class'>";
                            echo "<div class='card-body'>";
                            echo "<p><strong><i class='fa fa-user'></i> Cliente:</strong> " . $row_upcoming_exams['client_name'] . "</p>";
                            echo "<p><strong><i class='fa fa-calendar'></i> Fecha:</strong> " . $row_upcoming_exams['exam_date'] . "</p>";
                            echo "<p><strong><i class='fa fa-clock-o'></i> Hora de inicio:</strong> " . $row_upcoming_exams['start_time'] . "</p>";
                            echo "<p><strong><i class='fa fa-clock-o'></i> Hora de finalización:</strong> " . $row_upcoming_exams['end_time'] . "</p>";
                            echo "</div>";
                            echo "</div>";
                            $i++;
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="col-md-8" style="height: 370px;">
                <div class="card shadow-sm">
                    <div class="card-body" style="max-height: 370px;">
                        <h4 class="card-title"><i class="fa fa-bar-chart"></i> Estado de pago y monto total a cobrar</h4>
                        <canvas id="paymentChart" style="height: 280px; display: flex; width: 690px;"></canvas>
                    </div>
                </div>
            </div>


        </div>
    </div>
    <!-- Enlaces a los archivos JavaScript -->

    <!-- Enlace al script de jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="/js/bootstrap.bundle.min.js"></script>
    <!-- Enlace al script de Popper.js (necesario para Bootstrap) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.3/umd/popper.min.js"></script>
    <!-- Enlace al script de Bootstrap 5 -->
    <script src="/js/bootstrap.min.js"></script>
    <!-- Enlace al script de Material Design Lite -->
    <script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
    <script>
        function logout() {
            $.ajax({
                url: "logout.php",
                type: "POST",
                success: function() {
                    window.location.href = "index.php";
                },
                error: function() {
                    alert("Error al cerrar sesión. Inténtalo de nuevo.");
                }
            });
        }
    </script>
    <script>
        $(document).ready(function() {
            // Obtener los datos de la base de datos
            $.ajax({
                url: "get_data.php",
                dataType: "json",
                success: function(data) {
                    // Procesar los datos para crear la gráfica
                    var labels = [];
                    var counts = [];
                    var amounts = [];
                    for (var i in data) {
                        labels.push(data[i].payment_status);
                        counts.push(parseInt(data[i].count));
                        amounts.push(data[i].sum);
                    }

                    // Crear la gráfica
                    var ctx = document.getElementById("paymentChart").getContext("2d");
                    var chart = new Chart(ctx, {
                        type: "bar",
                        data: {
                            labels: labels,
                            datasets: [{
                                    label: "Cantidad de exámenes",
                                    data: counts,
                                    backgroundColor: "rgba(54, 162, 235, 0.5)",
                                    borderColor: "rgba(54, 162, 235, 1)",
                                    borderWidth: 1
                                },
                                {
                                    label: "Monto total a cobrar",
                                    data: amounts,
                                    backgroundColor: "rgba(255, 206, 86, 0.5)",
                                    borderColor: "rgba(255, 206, 86, 1)",
                                    borderWidth: 1
                                }
                            ]
                        },
                        options: {
                            scales: {
                                yAxes: [{
                                    ticks: {
                                        beginAtZero: true
                                    }
                                }]
                            },
                            tooltips: {
                                callbacks: {
                                    label: function(tooltipItem, data) {
                                        var label = data.datasets[tooltipItem.datasetIndex].label || '';
                                        if (label === "Cantidad de exámenes") {
                                            label += ': ' + data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                                        } else {
                                            label += ': $' + data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index].toLocaleString('en-US', {
                                                minimumFractionDigits: 2,
                                                maximumFractionDigits: 2
                                            });
                                        }
                                        return label;
                                    }
                                }
                            }
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>