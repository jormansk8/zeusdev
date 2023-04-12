<?php
// Conectar a la base de datos
$dbconn = pg_connect("host=localhost dbname=exams user=postgres password=1234");

// Obtener las fechas seleccionadas
$fechaInicio = $_POST['fechaInicio'];
$fechaFin = $_POST['fechaFin'];

// Consulta para obtener los gastos dentro del rango de fechas
$query = "SELECT id, expense_reason, amount_spent, TO_CHAR(created_at, 'YYYY-MM-DD') as created_at_formatted FROM expenses WHERE created_at::date >= '$fechaInicio' AND created_at::date <= '$fechaFin' ORDER BY created_at";
$result = pg_query($query);

// Crear la tabla con los resultados
$table = '';
while ($row = pg_fetch_assoc($result)) {
    $table .= '<tr>';
    $table .= '<td>' . $row['id'] . '</td>';
    $table .= '<td>' . $row['expense_reason'] . '</td>';
    $table .= '<td>$' . $row['amount_spent'] . '</td>';
    $table .= '<td>' . $row['created_at_formatted'] . '</td>';
    $table .= '</tr>';
}

// Retornar la tabla en formato de texto
echo $table;

// Cerrar la conexión a la base de datos
pg_close($dbconn);
?>
