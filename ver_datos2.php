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

// Consultar los eventos de la tabla exams
$sql = "SELECT client_name, exam_date, start_time, end_time, payment_status FROM exams WHERE client_name <> 'DINERO EN CUENTA'";
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
    'title' => htmlspecialchars_decode($row['client_name']),
    'start' => htmlspecialchars($row['exam_date']) . 'T' . htmlspecialchars($row['start_time']),
    'end' => htmlspecialchars($row['exam_date']) . 'T' . htmlspecialchars($row['end_time']),
    'color' => $color
  );
  array_push($events, $event);
}


// Consultar los eventos próximos en los próximos días
$sql_upcoming = "SELECT client_name, exam_date, start_time, end_time FROM exams WHERE exam_date BETWEEN current_date AND current_date + INTERVAL '2 days' AND end_time >= current_time ORDER BY exam_date, start_time";
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
      height: 100% !important;
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
    $(function() {
      // Crear el mensaje de la alerta con los próximos eventos
      var upcomingEvents = <?php echo json_encode($upcoming_events) ?>;
      var message = `
      <div class="modal fade" id="upcoming-events-modal" tabindex="-1" role="dialog" aria-labelledby="upcoming-events-modal-label">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="bg-primary  modal-header">
              <h4 class="modal-title" id="upcoming-events-modal-label">Eventos próximos en los próximos 2 días:</h4>
            </div>
            <div class="modal-body">
              <table class="table">
                <thead>
                  <tr>
                    <th>Nombre del cliente</th>
                    <th>Fecha</th>
                    <th>Hora de inicio</th>
                    <th>Hora de fin</th>
                  </tr>
                </thead>
                <tbody>
                  ${upcomingEvents.length ? 
                    upcomingEvents.map(event => `
                      <tr>
                        <td>${event.client_name}</td>
                        <td>${event.exam_date}</td>
                        <td>${event.start_time}</td>
                        <td>${event.end_time}</td>
                      </tr>
                    `).join('') : 
                    '<tr><td colspan="4">No hay eventos próximos en los próximos 2 días.</td></tr>'
                  }
                </tbody>
              </table>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-primary" data-dismiss="modal">Cerrar</button>
            </div>
          </div>
        </div>
      </div>
    `;

      // Agregar el mensaje al final del cuerpo de la página
      $('body').append(message);

      // Mostrar la alerta en un modal al cargar la página
      $('#upcoming-events-modal').modal('show');

      // Configurar FullCalendar para usar el idioma español y el formato de los meses en mayúsculas
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
        nowIndicator: true,
        eventRender: function(event, element) {
  // Calcular la diferencia de tiempo entre el evento y la hora actual
  var timeDiff = new Date(event.start).getTime() - new Date().getTime();
  var diffInMinutes = Math.round(timeDiff / 60000);

  // Si la diferencia es de menos de 5 minutos, mostrar el modal del evento próximo
  if (diffInMinutes > 0 && diffInMinutes <= 5) {
    // Verificar si ya se mostró el modal del próximo evento
    if ($('#upcoming-event-modal').length === 0) {
      // Crear el mensaje del modal
      var modalMessage = `
        <div class="modal fade" id="upcoming-event-modal" tabindex="-1" role="dialog" aria-labelledby="upcoming-event-modal-label">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title" id="upcoming-event-modal-label">Próximo evento:</h4>
              </div>
              <div class="modal-body">
                <p>El evento de ${event.title} comienza en ${diffInMinutes} minutos.</p>
                <p><strong>Cliente:</strong> ${event.client_name}</p>
                <p><strong>Fecha:</strong> ${moment(event.start).format('LL')}</p>
                <p><strong>Hora de inicio:</strong> ${moment(event.start).format('LT')}</p>
                <p><strong>Hora de fin:</strong> ${moment(event.end).format('LT')}</p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Cerrar</button>
              </div>
            </div>
          </div>
        </div>
      `;
      // Agregar el mensaje al final del cuerpo de la página
      $('body').append(modalMessage);

      // Mostrar el modal del próximo evento
      $('#upcoming-event-modal').modal('show');
    }
  }
}


      });
    });
  </script>



</body>

</html>