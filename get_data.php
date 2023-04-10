<?php
// Conectar a la base de datos
$conn = pg_connect("host=localhost dbname=exams user=postgres password=1234");
// Obtener los datos de la base de datos
$query = "SELECT payment_status, COUNT(*) AS count, 
SUM(amount_to_charge) AS sum 
FROM exams 
GROUP BY payment_status;";
$result = pg_query($conn, $query);

// Procesar los datos en formato JSON
$data = array();
while ($row = pg_fetch_assoc($result)) {
    $data[] = $row;
}
echo json_encode($data);

pg_close($conn);
?>