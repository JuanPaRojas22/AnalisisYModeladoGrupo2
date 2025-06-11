<?php
$host = "localhost"; 
$usuario = "root";   
$contrasena = "";    
$base_de_datos = "gestionempleados"; //base de datos

// Crear conexión
$conn = new mysqli($host, $usuario, $contrasena, $base_de_datos);

// Verificar conexión
if ($conn->connect_error) {
    die("La conexión a la base de datos falló: " . $conn->connect_error);
}
?>