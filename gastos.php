<?php
session_start();
// Conectar a la base de datos
$dbconn = pg_connect("host=localhost dbname=exams user=postgres password=1234");

// Si se envía el formulario, se insertan los datos en la base de datos
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['amount_spent']) && is_numeric($_POST['amount_spent'])) {
    $amount_spent = $_POST['amount_spent'];

    // Si el switch está activo, se convierte el monto a negativo
    if (isset($_POST['invert_amount']) && $_POST['invert_amount'] == 'on') {
        $amount_spent = -$amount_spent;
    }

    $expense_reason = $_POST['expense_reason'];
    $query = "INSERT INTO expenses (amount_spent, expense_reason) VALUES ($1, $2)";
    pg_prepare($dbconn, "", $query);
    pg_execute($dbconn, "", array($amount_spent, $expense_reason));

    // Redirigir al usuario a la misma página
    header("Location: " . $_SERVER['PHP_SELF']);
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

        .conta {
            margin-top: 0px;
            margin-left: 220px;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 135px;
            height: 34px;
        }

        .switch-input {
            display: none;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #6c757d;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        .switch-input:checked+.slider {
            background-color: #007bff;
        }

        .switch-input:focus+.slider {
            box-shadow: 0 0 1px #2196F3;
        }

        .switch-input:checked+.slider:before {
            transform: translateX(100px);
        }

        .switch-label {
            position: absolute;
            left: 0;
            top: 6px;
            display: block;
            width: 100%;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            font-weight: bold;
            text-transform: uppercase;
            transition: .4s;
            pointer-events: none;
        }

        .switch-input:checked+.slider+.switch-label:before {
            color: white;
            content: attr(data-on-text);
        }

        .switch-input:not(:checked)+.slider+.switch-label:before {
            color: white;
            content: attr(data-off-text);
        }

        /* Rounded sliders */
        .slider.round:before {
            border-radius: 50%;
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
            <div style="position: relative;">
                <form method="post">
                    <div class="form-group">
                        <label for="expense_reason">Motivo de gasto:</label>
                        <input type="text" class="form-control form-control-sm" id="expense_reason" name="expense_reason">
                    </div>
                    <div class="form-group">
                        <label for="amount_spent">Monto gastado:</label>
                        <input type="number" step="0.01" min="0.00" class="form-control form-control-sm" placeholder="0.00" aria-label="Cantidad (al dólar más cercano)" id="amount_spent" name="amount_spent">
                    </div>
                    <button type="submit" class="btn btn-primary" name="submit" <?php if ($dinero_restante <= 0) echo 'disabled'; ?>>
                        <i class="material-icons align-middle">add</i> Agregar
                    </button>

                    <style>
                        .align-middle {
                            vertical-align: middle;
                            margin-right: 5px;
                        }
                    </style>
                    <div style="position: absolute; bottom: 0; right: 0;">
                        <label class="switch">
                            <input type="checkbox" class="switch-input" id="invert_amount" name="invert_amount">
                            <span class="slider round"></span>
                            <span class="switch-label" data-on-text="Ingresos" data-off-text="Gastos"></span>
                        </label>
                    </div>
                </form>


            </div>



            <div class="mt-3">

                <style media="print">
                    body * {
                        visibility: hidden;
                    }

                    .d-print-block {
                        display: block !important;
                    }

                    .table,
                    .table * {
                        visibility: visible;
                    }

                    .table {
                        position: absolute;
                        left: 0;
                        top: 0;
                    }
                </style>



                <div class="d-flex justify-content-between">
                    <div class="bg-success rounded p-2">
                        <p class="h5 text-white mb-0"><i class="material-icons align-middle">attach_money</i> Dinero restante: $<?php echo number_format($dinero_restante, 2); ?></p>
                    </div>
                    <div class="bg-danger rounded p-2">
                        <p class="h5 text-white mb-0"><i class="material-icons align-middle">money_off</i> Dinero gastado: $<?php echo number_format($total_gastado, 2); ?></p>
                    </div>

                </div>
                <div class="bg-warning rounded" style="margin-top: 7px;">
                    <center>
                        <?php if ($total_gastado > $dinero_restante) : ?>
                            <p class="text-white mb-0">Recomendación: ¡Cuidado con los gastos! Ya has gastado más de lo que tenías planificado.</p>
                        <?php endif; ?>
                    </center>
                </div>

            </div>
        </div>

    </div>

    <!-- Agregar un contenedor adicional con clase "container-fluid" para el gráfico -->
    <div class="container-fluid" style="margin-left: 110px; margin-top: -10px;">
        <div class="card mt-3 mx-auto shadow-sm rounded" style="background-color: whitesmoke; width: 70%;">
            <div class="bg-secondary card-header">
                <center>
                    <h4 class="text-white">Gráfica de gastos e ingresos</h4>
                </center>
            </div>
            <div class="card-body">
                <canvas id="myChart"></canvas>

                <div style="position: absolute; bottom: 0; right: 0;">
                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#consejosModal" style="display: flex; align-items: center;">
                        Consejos <i class="material-icons" style="margin-left: 5px;">lightbulb</i>
                    </button>
                </div>

            </div>
            <div class="text-center mt-3">
                <button class="btn btn-secondary" style="margin-top: -30px;" data-toggle="modal" data-target="#fechasModal">
                    <i class="fa fa-print"></i> Imprimir gastos
                </button>
            </div>
        </div>
    </div>

    <!-- Modal para seleccionar rango de fecha de gastos a imprimir -->
    <div class="modal fade" id="fechasModal" tabindex="-1" role="dialog" aria-labelledby="fechasModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="fechasModalLabel">Seleccionar rango de fechas</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label for="fechaInicio">Fecha de inicio:</label>
                            <input type="date" class="form-control" id="fechaInicio" required>
                        </div>
                        <div class="form-group">
                            <label for="fechaFin">Fecha de fin:</label>
                            <input type="date" class="form-control" id="fechaFin" required onchange="validarFechas()">
                            <div class="invalid-feedback">La fecha de fin debe ser mayor o igual que la de inicio.</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="imprimirGastos()">Imprimir</button>
                </div>
            </div>
        </div>
    </div>




    <!-- Aquí se mostrará el modal de los consejos -->
    <div class="modal fade" id="consejosModal" tabindex="-1" role="dialog" aria-labelledby="consejosModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="consejosModalLabel">Consejos para administrar tus finanzas</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Aquí se mostrarán los consejos -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>







    <div class="container mt-3 d-print-block d-none">
        <h4 class="text-center">Tabla de gastos</h4>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Motivo</th>
                    <th>Monto gastado</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Conectar a la base de datos
                $dbconn = pg_connect("host=localhost dbname=exams user=postgres password=1234");
                // Consulta para obtener todos los gastos ordenados por fecha
                $query = "SELECT id, expense_reason, amount_spent, TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS') as created_at_formatted FROM expenses ORDER BY created_at";
                $result = pg_query($query);
                // Mostrar los resultados en la tabla
                while ($row = pg_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . $row['expense_reason'] . "</td>";
                    echo "<td>$" . $row['amount_spent'] . "</td>";
                    echo "<td>" . $row['created_at_formatted'] . "</td>";
                    echo "</tr>";
                }
                // Cerrar la conexión a la base de datos
                pg_close($dbconn);
                ?>
            </tbody>

        </table>
    </div>
    </div>
    <script>
        $(document).ready(function() {
            var dinero_restante = <?php echo $dinero_restante; ?>;
            var total_gastado = <?php echo $total_gastado; ?>;

            // Obtener el contenedor del modal y el cuerpo del modal
            var consejosModal = $('#consejosModal');
            var modalBody = consejosModal.find('.modal-body');

            // Definir los consejos para diferentes situaciones
            var consejos = [{
                    situacion: 'Tienes suficiente dinero',
                    consejo: '¡Felicidades! Sigues en camino hacia tus metas financieras.'
                },
                {
                    situacion: 'Te estás quedando sin dinero',
                    consejo: 'Reduce tus gastos innecesarios y busca formas de generar ingresos adicionales.'
                },
                {
                    situacion: 'Has gastado más de lo que tienes',
                    consejo: 'Es importante que ajustes tus gastos y priorices tus necesidades más importantes.'
                },
                {
                    situacion: 'Estás en números rojos',
                    consejo: 'Busca ayuda financiera y considera opciones para salir de la deuda lo antes posible.'
                }
            ];

            // Mostrar el consejo correspondiente según la situación financiera
            if (dinero_restante > total_gastado) {
                modalBody.html('<p>' + consejos[0].consejo + '</p>');
            } else if (dinero_restante > 0) {
                modalBody.html('<p>' + consejos[1].consejo + '</p>');
            } else if (dinero_restante < 0 && total_gastado < 100) {
                modalBody.html('<p>' + consejos[2].consejo + '</p>');
            } else if (dinero_restante < 0 && total_gastado >= 100) {
                modalBody.html('<p>' + consejos[3].consejo + '</p>');
            }

            // Mostrar el modal
            consejosModal.modal('show');
        });
    </script>
    <script>
        const invertCheckbox = document.getElementById('invert_amount');
        const expenseReasonLabel = document.querySelector('label[for="expense_reason"]');
        const amountSpentLabel = document.querySelector('label[for="amount_spent"]');
        const switchlabel = document.querySelector('.switch-label');

        invertCheckbox.addEventListener('change', function() {
            if (this.checked) {
                expenseReasonLabel.textContent = 'Motivo de ingreso:';
                amountSpentLabel.textContent = 'Monto de ingreso:';
            } else {
                expenseReasonLabel.textContent = 'Motivo de gasto:';
                amountSpentLabel.textContent = 'Monto gastado:';
            }
        });
    </script>



    <script>
        function imprimirGastos() {
            // Obtener las fechas seleccionadas
            var fechaInicio = document.getElementById("fechaInicio").value;
            var fechaFin = document.getElementById("fechaFin").value;

            // Validar que la fecha de fin es mayor o igual que la de inicio
            if (fechaFin < fechaInicio) {
                $('#fechasModal .invalid-feedback').show();
                return;
            } else {
                $('#fechasModal .invalid-feedback').hide();
            }

            // Hacer la consulta a la base de datos
            $.ajax({
                url: "imprimir_gastos.php",
                method: "POST",
                data: {
                    fechaInicio: fechaInicio,
                    fechaFin: fechaFin
                },
                success: function(response) {
                    // Crear la nueva tabla con los resultados
                    var nuevaTabla = '<div class="container mt-3 d-print-block d-none">';
                    nuevaTabla += '<h4 class="text-center">Tabla de gastos</h4>';
                    nuevaTabla += '<table class="table table-striped">';
                    nuevaTabla += '<thead><tr><th>Id</th><th>Motivo</th><th>Monto gastado</th><th>Fecha</th></tr></thead>';
                    nuevaTabla += '<tbody>' + response + '</tbody>';
                    nuevaTabla += '</table></div>';

                    // Agregar la nueva tabla al cuerpo del documento y abrir la ventana de impresión
                    $("body").append(nuevaTabla);
                    window.print();

                    // Eliminar la nueva tabla del cuerpo del documento después de imprimir
                    setTimeout(function() {
                        $(".d-print-block").remove();
                    }, 1000);
                }
            });
        }
    </script>

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