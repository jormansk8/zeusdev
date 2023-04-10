<?php
session_start();
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    unset($_SESSION['username']);
    session_destroy();
}
header("Location: index.php");
exit();

?>
