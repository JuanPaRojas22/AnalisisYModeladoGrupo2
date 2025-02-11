<?php
// Incluir el archivo de conexión
require_once __DIR__ . '/conexion.php';  // Asegúrate de que la ruta sea correcta

// Inicializar las variables
$id_usuario = $horas_extras = $tipo_quincena = $mes = $anio = $usuario_creacion = "";
$salario_base = $salario_hora = $monto_horas_extra = $salario_neto = 0;

// Si el formulario fue enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los valores del formulario
    $id_usuario = $_POST['id_usuario'];
    $horas_extras = $_POST['horas_extras'];
    $tipo_quincena = $_POST['tipo_quincena'];
    $mes = $_POST['mes'];
    $anio = $_POST['anio'];
    $usuario_creacion = "admin"; // Cambia esto por el nombre del usuario que está creando el registro

    // Obtener el salario base del usuario
    $sql_salario = "SELECT salario_base FROM Usuario WHERE id_usuario = ?";
    $stmt = $conn->prepare($sql_salario);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $stmt->bind_result($salario_base);
    $stmt->fetch();
    $stmt->close();

    // Calcular salario por hora (80 horas en una quincena)
    $salario_hora = $salario_base / 80;

    // Calcular el monto de las horas extras (en base al salario por hora)
    $monto_horas_extra = $horas_extras * $salario_hora * 1.5; // Tarifa extra del 150%

    // Calcular el salario neto (sin incluir deducciones ni bonos aún)
    $salario_neto = ($salario_base / 2) + $monto_horas_extra; // Se divide por 2 porque es quincenal

    // Insertar la planilla con las horas extras en la base de datos
    $sql_insert = "INSERT INTO planilla 
        (id_usuario, horas_extras, salario_base, tipo_quincena, salario_neto, mes, anio, fechacreacion, usuariocreacion) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param("iiddssiss", $id_usuario, $horas_extras, $salario_base, $tipo_quincena, $salario_neto, $mes, $anio, $usuario_creacion);
    
    if ($stmt->execute()) {
        echo "Horas extras registradas correctamente y salario actualizado.";
    } else {
        echo "Error al registrar las horas extras: " . $conn->error;
    }
    $stmt->close();
}

// Obtener los registros de planillas
$sql_display = "SELECT p.id_planilla, u.nombre AS usuario, p.horas_extras, p.salario_base, p.salario_neto, p.tipo_quincena, p.mes, p.anio 
                FROM planilla p 
                JOIN Usuario u ON p.id_usuario = u.id_usuario";

// Ejecutar la consulta
$result = $conn->query($sql_display);

// Verificar si se encontraron resultados
if ($result && $result->num_rows > 0) {
    $planillas = $result->fetch_all(MYSQLI_ASSOC); // Recuperar los resultados
} else {
    $planillas = []; // No hay registros disponibles
}

// Cerrar la conexión
$conn->close();
?>
