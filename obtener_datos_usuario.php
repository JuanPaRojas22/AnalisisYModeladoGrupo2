<?php
$conexion = new mysqli("localhost", "root", "", "gestionempleados");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$id_usuario = $_GET['id_usuario'];

// Traer ocupación y salario base
$sql = "SELECT u.id_ocupacion, p.salario_base 
        FROM Usuario u
        LEFT JOIN Planilla p ON u.id_usuario = p.id_usuario 
        WHERE u.id_usuario = '$id_usuario' 
        LIMIT 1";

$resultado = $conexion->query($sql);

$data = [];

if ($resultado->num_rows > 0) {
    $fila = $resultado->fetch_assoc();
    $data = [
        'puesto_anterior' => $fila['id_ocupacion'],
        'sueldo_anterior' => $fila['salario_base']
    ];
}

echo json_encode($data);
?>
