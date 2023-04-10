<?php
session_start();
if (!isset($_SESSION["username"])) {
  header("Location: index.php");
  exit();
}
// Conectarse a la base de datos
$dbconn = pg_connect("host=localhost dbname=exams user=postgres password=1234");

// Comprobar si la conexión se ha establecido correctamente
if (!$dbconn) {
    echo "Error al conectar a la base de datos.";
    exit;
}

// Si se ha enviado un formulario para editar o eliminar un examen, procesarlo
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener el ID del examen a editar o eliminar
    if (isset($_POST["id"]) && is_numeric($_POST["id"])) {
        $id = $_POST["id"];

        // Si se ha pulsado el botón de "Eliminar", borrar el examen de la tabla
        if (isset($_POST["delete"])) {
            $sql = "DELETE FROM exams WHERE id = $id";
            $result = pg_query($dbconn, $sql);

            /* if (!$result) {
                echo "Error al eliminar el examen.";
            } else {
                echo "El examen ha sido eliminado correctamente.";
            } */
        }
        // Si se ha pulsado el botón de "Guardar cambios", actualizar los datos del examen en la tabla
        elseif (isset($_POST["edit"])) {
            // Obtener los datos del examen desde el formulario
            $client_name = $_POST["client_name"];
            $phone = $_POST["phone"];
            $amount_to_charge = $_POST["amount_to_charge"];
            $payment_status = $_POST["payment_status"];
            $exam_date = $_POST["exam_date"];
            $start_time = $_POST["start_time"];
            $end_time = $_POST["end_time"];

            // Actualizar los datos del examen en la tabla
            $sql = "UPDATE exams SET client_name = '$client_name', phone = '$phone', amount_to_charge = $amount_to_charge, payment_status = '$payment_status', exam_date = '$exam_date', start_time = '$start_time', end_time = '$end_time' WHERE id = $id";
            $result = pg_query($dbconn, $sql);

            /* if (!$result) {
                echo "Error al guardar los cambios del examen.";
            } else {
                echo "Los cambios del examen han sido guardados correctamente.";
            } */
        }
    } else {
        echo "El ID del examen no es válido.";
    }
}

// Consultar todos los exámenes de la tabla
$sql = "SELECT * FROM exams ORDER BY 6";
$result = pg_query($dbconn, $sql);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Lista de Exámenes</title>
    <!-- Agregar los estilos de Bootstrap -->
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css">
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

        /* Agregar margen izquierdo a la tabla */
        h1 {
            text-align: center;
            margin-left: 170px;
            width: 80%;
        }

        table {
            width: 100%;
            margin-left: 130px;
            table-layout: fixed;
        }

        table th:nth-child(2),
        table td:nth-child(2) {
            width: 20%;
        }

        table th:nth-child(1),
        table td:nth-child(1),
        table th:nth-child(3),
        table td:nth-child(3),
        table th:nth-child(4),
        table td:nth-child(4),
        table th:nth-child(5),
        table td:nth-child(5) {
            width: 10%;
        }

        table th:nth-child(6),
        table td:nth-child(6),
        table th:nth-child(7),
        table td:nth-child(7),
        table th:nth-child(8),
        table td:nth-child(8) {
            width: 15%;
        }

        table th:last-child,
        table td:last-child {
            width: 30%;
            white-space: nowrap;
        }

        table td {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        footer {
            margin-left: 200px;
        }

        /* Agregar scroll horizontal a la tabla en pantallas pequeñas */
        @media (max-width: 991px) {
            table {
            width: 100%;
            margin-left: 90px;
            table-layout: fixed;
        }

        table th:nth-child(1),
        table td:nth-child(1),
        table th:nth-child(2),
        table td:nth-child(2),
        table th:nth-child(3),
        table td:nth-child(3),
        table th:nth-child(4),
        table td:nth-child(4),
        table th:nth-child(5),
        table td:nth-child(5) {
            width: 10%;
        }

        table th:nth-child(6),
        table td:nth-child(6),
        table th:nth-child(7),
        table td:nth-child(7),
        table th:nth-child(8),
        table td:nth-child(8) {
            width: 15%;
        }

        table th:last-child,
        table td:last-child {
            width: 30%;
            white-space: nowrap;
        }

        table td {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
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
        <a href="/gastos.php"><i class="fa fa-money"></i> Gastos</a>
    </div>

    <div class="container" style="overflow-x: hidden;">
        <h1>Gestión de Exámenes</h1>
        <table class="table" id="tbl">
            <thead>
                <tr>
                    <th style="display: none;">ID</th>
                    <th>Nombre del Cliente</th>
                    <th style="display: none;">Teléfono</th>
                    <th>Monto a Cobrar</th>
                    <th>Estado de Pago</th>
                    <th>Fecha del Examen</th>
                    <th>Hora de Inicio</th>
                    <th>Hora de Finalización</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Recorrer los resultados de la consulta SQL y agregar cada examen a la tabla
                while ($row = pg_fetch_assoc($result)) {
                ?>
                    <tr>
                        <td style="display: none;"><?php echo $row['id']; ?></td>
                        <td><?php echo $row['client_name']; ?></td>
                        <td style="display: none;"><?php echo $row['phone']; ?></td>
                        <td><?php echo $row['amount_to_charge']; ?></td>
                        <td><?php echo $row['payment_status']; ?></td>
                        <td><?php echo $row['exam_date']; ?></td>
                        <td><?php echo $row['start_time']; ?></td>
                        <td><?php echo $row['end_time']; ?></td>
                        <td>
                        <?php if ($row['payment_status'] == 'pagado') { ?>
                <!-- Botón para imprimir el ticket -->
                <a href="imprimir_ticket.php?id=<?php echo $row['id']; ?>" target="_blank" class="btn btn-success btn-xs">Ticket</a>
            <?php } ?>
                            <!-- Botón para abrir el formulario de edición del examen -->
                            <button type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#editModal<?php echo $row['id']; ?>">Editar</button>
                            <!-- Botón para abrir el diálogo de confirmación de eliminación del examen -->
                            <button type="button" class="btn btn-danger btn-xs" data-toggle="modal" data-target="#deleteModal<?php echo $row['id']; ?>">Eliminar</button>
                            
                            <!-- Formulario para editar el examen -->
                            <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <form method="post">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                                                <h4 class="modal-title" id="editModalLabel">Editar Examen</h4>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                <div class="form-group">
                                                    <label for="client_name">Nombre del Cliente</label>
                                                    <input type="text" class="form-control" id="client_name" name="client_name" value="<?php echo htmlspecialchars($row['client_name']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="phone">Teléfono</label>
                                                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($row['phone']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="amount_to_charge">Monto a Cobrar</label>
                                                    <input type="number" step="0.01" class="form-control" id="amount_to_charge" name="amount_to_charge" value="<?php echo htmlspecialchars($row['amount_to_charge']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="payment_status">Estado de Pago</label>
                                                    <select class="form-control" id="payment_status" name="payment_status" required>
                                                        <option value="pendiente" <?php if ($row['payment_status'] == 'pendiente') echo 'selected'; ?>>Pendiente</option>
                                                        <option value="pagado" <?php if ($row['payment_status'] == 'pagado') echo 'selected'; ?>>Pagado</option>
                                                        <option value="anulado" <?php if ($row['payment_status'] == 'anulado') echo 'selected'; ?>>Anulado</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="exam_date">Fecha del Examen</label>
                                                    <input type="date" class="form-control" id="exam_date" name="exam_date" value="<?php echo htmlspecialchars($row['exam_date']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="start_time">Hora de Inicio</label>
                                                    <input type="time" class="form-control" id="start_time" name="start_time" value="<?php echo htmlspecialchars($row['start_time']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="end_time">Hora de Fin</label>
                                                    <input type="time" class="form-control" id="end_time" name="end_time" value="<?php echo htmlspecialchars($row['end_time']); ?>" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-primary" name="edit" value="<?php echo $row['id']; ?>">Guardar Cambios</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <!-- Diálogo de confirmación de eliminación del examen -->
                            <div class="modal fade" id="deleteModal<?php echo $row['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <form method="post">
                                            <div class="modal-header">
                                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                                                <h4 class="modal-title" id="deleteModalLabel">Eliminar Examen</h4>
                                            </div>
                                            <div class="modal-body">
                                                <p>¿Está seguro que desea eliminar este examen?</p>
                                                <p><strong>Cliente: <?php echo $row['client_name']; ?></strong></p>
                                                <p><strong>Fecha: <?php echo $row['exam_date']; ?></strong></p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-danger" name="delete" value="<?php echo $row['id']; ?>">Eliminar</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Scripts de Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

    <footer>
        Derechos Reservados © 2023, Desarrollado por Jorman LP.
    </footer>
</body>

</html>