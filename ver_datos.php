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
        'start' => htmlspecialchars($row['exam_date']).'T'.htmlspecialchars($row['start_time']),
        'end' => htmlspecialchars($row['exam_date']).'T'.htmlspecialchars($row['end_time']),
        'color' => $color
    );
    array_push($events, $event);
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
    <style>
        html, body, .container {
            height: 80%;
        }
        .calendar {
            height: calc(85% - 40px); /* 40px es la altura del encabezado */
        }
    </style>
</head>
<body>
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
        events: <?php echo $json ?>
    });
});

    </script>
</body>
</html>
