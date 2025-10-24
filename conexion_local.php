<?php
// conexion_local.php

$servername = "127.0.0.1";
$username = "root";
$password = "";
$database = "gestionEmpleados";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $database);
mysqli_set_charset($conn, "utf8mb4");

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>
