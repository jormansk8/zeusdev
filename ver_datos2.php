<?php
session_start();
if (!isset($_SESSION["username"])) {
  header("Location: index.php");
  exit();
}
?>
<?php
// Conectarse a la base de datos
$dbconn = pg_connect("host=localhost dbname=exams user=postgres password=1234");

// Comprobar si la conexión se ha establecido correctamente
if (!$dbconn) {
  echo "Error al conectar a la base de datos.";
  exit;
}

// Consultar los eventos de la tabla exams
$sql = "SELECT * FROM exams";
$result = pg_query($dbconn, $sql);

// Crear un array para almacenar los eventos en formato JSON
$events = array();

// Recorrer los resultados de la consulta SQL y agregar los eventos al array
while ($row = pg_fetch_assoc($result)) {
  $color = '';
  switch ($row['payment_status']) {
    case 'pagado':
      $color = '#20c67a';
      break;
    case 'pendiente':
      $color = '#2196f3';
      break;
    case 'anulado':
      $color = '#ff5f7c';
      break;
  }

  $event = array(
    'title' => htmlspecialchars($row['client_name']),
    'start' => htmlspecialchars($row['exam_date']) . 'T' . htmlspecialchars($row['start_time']),
    'end' => htmlspecialchars($row['exam_date']) . 'T' . htmlspecialchars($row['end_time']),
    'color' => $color
  );
  array_push($events, $event);
}

// Consultar los eventos próximos en los próximos 7 días
$sql_upcoming = "SELECT * FROM exams WHERE exam_date BETWEEN current_date AND current_date + INTERVAL '2 days' ORDER BY exam_date, start_time";
$result_upcoming = pg_query($dbconn, $sql_upcoming);

// Crear un array para almacenar los eventos próximos
$upcoming_events = array();

// Recorrer los resultados de la consulta SQL y agregar los eventos próximos al array
while ($row = pg_fetch_assoc($result_upcoming)) {
  array_push($upcoming_events, $row);
}

// Convertir el array de eventos a formato JSON
$json = json_encode($events);

// Cerrar la conexión a la base de datos
pg_close($dbconn);
?>


<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Calendario de Exámenes</title>
  <!-- Agregar los estilos de FullCalendar y Bootstrap -->
  <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css">
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

.calendar {
  height: calc(75% - 60px);
  /* 60px es la altura del encabezado y el margen inferior */
  margin-left: 220px;
  /* El valor debe ser mayor al ancho del sidebar más el margen lateral */
}
.fc-content {
  height: 20px !important;
  /* Cambiar el valor de height según lo que se necesite */
  font-size: 12px;
  /* Cambiar el valor de font-size según lo que se necesite */
}
.espacio {
  margin-bottom: 15px;
}

footer {
  margin-top: 20px;
  margin-left: 200px;
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
<body>
<div class="sidebar">
    <center>
      <a href="/dashboard.php" class="logo"><img src="zeusdev.png" alt="Logo de Zeusdev" width="90" height="90" style="pointer-events:none;"></a>
    </center>
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
  <div class="espacio"></div>
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="calendar" id="calendar"></div>
      </div>
    </div>
  </div>


  <!-- Agregar los scripts de FullCalendar y Bootstrap -->
  <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
  <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <!-- Agregar la biblioteca de idiomas de FullCalendar para español -->
  <script src="//cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/locale/es.js"></script>
  <script>
    $(document).ready(function() {
      // Crear el mensaje de la alerta con los próximos eventos
      var upcomingEvents = <?php echo json_encode($upcoming_events) ?>;
      var message = '';
      if (upcomingEvents.length > 0) {
        message += '<div class="modal fade" id="upcoming-events-modal" tabindex="-1" role="dialog" aria-labelledby="upcoming-events-modal-label">';
        message += '<div class="modal-dialog" role="document">';
        message += '<div class="modal-content">';
        message += '<div class="bg-primary  modal-header">';
        message += '<h4 class="modal-title" id="upcoming-events-modal-label">Eventos próximos en los próximos 2 días:</h4>';
        message += '</div>';
        message += '<div class="modal-body">';
        message += '<table class="table">';
        message += '<thead>';
        message += '<tr>';
        message += '<th>Nombre del cliente</th>';
        message += '<th>Fecha</th>';
        message += '<th>Hora de inicio</th>';
        message += '<th>Hora de fin</th>';
        message += '</tr>';
        message += '</thead>';
        message += '<tbody>';
        for (var i = 0; i < upcomingEvents.length; i++) {
          message += '<tr>';
          message += '<td>' + upcomingEvents[i]['client_name'] + '</td>';
          message += '<td>' + upcomingEvents[i]['exam_date'] + '</td>';
          message += '<td>' + upcomingEvents[i]['start_time'] + '</td>';
          message += '<td>' + upcomingEvents[i]['end_time'] + '</td>';
          message += '</tr>';
        }
        message += '</tbody>';
        message += '</table>';
        message += '</div>';
        message += '<div class="modal-footer">';
        message += '<button type="button" class="btn btn-primary" data-dismiss="modal">Cerrar</button>';
        message += '</div>';
        message += '</div>';
        message += '</div>';
        message += '</div>';

        // Agregar el mensaje al final del cuerpo de la página
        $('body').append(message);

        // Mostrar la alerta en un modal al cargar la página
        $('#upcoming-events-modal').modal('show');
      }
    });
  </script>
  <script>
    function logout() {
      $.ajax({
        url: "logout.php",
        type: "POST",
        success: function() {
          window.location.href = "index.php";
        }
      });
    }
  </script>
  <script>
    // Configurar FullCalendar para usar el idioma español y el formato de los meses en mayúsculas
    $(document).ready(function() {
      $('#calendar').fullCalendar({
        locale: 'es',
        monthNames: [
          'ENERO',
          'FEBRERO',
          'MARZO',
          'ABRIL',
          'MAYO',
          'JUNIO',
          'JULIO',
          'AGOSTO',
          'SEPTIEMBRE',
          'OCTUBRE',
          'NOVIEMBRE',
          'DICIEMBRE'
        ],
        defaultView: 'month', // Vista por defecto
        header: {
          left: 'prev,next today',
          center: 'title',
          right: 'month,agendaWeek,agendaDay' // Agregar las opciones de vista
        },
        events: <?php echo $json ?>,
        nowIndicator: true 
      });
    });
  </script>

</body>

</html>