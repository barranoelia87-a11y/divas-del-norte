<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'reservas_de_citas';

$conexion = mysqli_connect($host, $user, $password, $database);

if (!$conexion) {
    die("ERROR de conexión: " . mysqli_connect_error());
}

mysqli_set_charset($conexion, "utf8mb4");
?>