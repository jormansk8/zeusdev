<?php
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
    header("Location: form_insertar_datos.php");
    echo "<script>$(document).ready(function() { $('#alert-modal').modal('show'); });</script>";
    exit;
} else {
    // Si la fecha y hora de inicio están disponibles, insertar los datos en la base de datos
    $sql_insert = "INSERT INTO exams (client_name, phone, amount_to_charge, payment_status, exam_date, start_time, end_time) VALUES ('$client_name', '$phone', '$amount_to_charge', '$payment_status', '$exam_date', '$start_time', '$end_time')";
    $result_insert = pg_query($dbconn, $sql_insert);

    if (!$result_insert) {
        echo "Error al insertar los datos en la tabla exams.";
        exit;
    } else {
        echo "<script>window.location.href ='/form_insertar_datos.php'</script>";
    }
}

// Cerrar la conexión a la base de datos
pg_close($dbconn);
?>
