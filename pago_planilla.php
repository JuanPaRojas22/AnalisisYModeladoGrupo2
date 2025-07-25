<?php
// Establecer la zona horaria a Costa Rica
date_default_timezone_set('America/Costa_Rica');

// Conexión a la base de datos
require 'conexion.php';
$conn = obtenerConexion();    // Llama a la función y guarda la conexión en $conn
session_start();

// Inicializar la variable $mensaje
$mensaje = '';

// Verificar si el formulario ha sido enviado mediante AJAX
if (isset($_POST['ejecutar_pago'])) {
    // Obtener la fecha actual
    $fecha_pago = date("Y-m-d");

    // Consultar todos los usuarios en la tabla planilla
    $query_usuarios = "SELECT id_usuario, salario_base, total_deducciones FROM planilla";
    $result_usuarios = $conn->query($query_usuarios);

    // Verificar si la consulta devuelve resultados
    if ($result_usuarios->num_rows > 0) {
        // Recorrer cada usuario
        while ($row = $result_usuarios->fetch_assoc()) {
            $id_usuario = $row['id_usuario'];
            $salario_base = $row['salario_base'];
            $total_deducciones = $row['total_deducciones'];

            // Obtener el ID de la planilla para este usuario
            $query_planilla = "SELECT id_planilla FROM planilla WHERE id_usuario = ?";
            $stmt_planilla = $conn->prepare($query_planilla);
            $stmt_planilla->bind_param("i", $id_usuario);
            $stmt_planilla->execute();
            $stmt_planilla->bind_result($id_planilla);
            $stmt_planilla->fetch();
            $stmt_planilla->close();

            if (!$id_planilla) {
                $mensaje = "No se encontró la planilla para el usuario ID: $id_usuario.";
                continue;
            }

            // Obtener el total de horas extras
            $query_horas_extras = "SELECT SUM(monto_pago) FROM horas_extra WHERE id_usuario = ?";
            $stmt_horas_extras = $conn->prepare($query_horas_extras);
            $stmt_horas_extras->bind_param("i", $id_usuario);
            $stmt_horas_extras->execute();
            $stmt_horas_extras->bind_result($pago_horas_extras);
            $stmt_horas_extras->fetch();
            $stmt_horas_extras->close();

            // Obtener el total de bonos
            $query_bonos = "SELECT SUM(monto_total) FROM bonos WHERE id_usuario = ?";
            $stmt_bonos = $conn->prepare($query_bonos);
            $stmt_bonos->bind_param("i", $id_usuario);
            $stmt_bonos->execute();
            $stmt_bonos->bind_result($total_bonos);
            $stmt_bonos->fetch();
            $stmt_bonos->close();

            // Si no hay bonos, asignar 0
            if ($total_bonos === null) {
                $total_bonos = 0;
            }

            // Si no hay deducciones, asignar 0
            if ($total_deducciones === null) {
                $total_deducciones = 0;
            }

            // Calcular el salario neto
            $salario_neto = $salario_base/2 - $total_deducciones + $total_bonos + $pago_horas_extras;

            // Obtener el día del mes de la fecha de pago
            $dia_del_mes = date("d", strtotime($fecha_pago));

            // Determinar el tipo de quincena
            if ($dia_del_mes >= 1 && $dia_del_mes <= 15) {
                $tipo_quincena = 'Primera Quincena';
            } else {
                $tipo_quincena = 'Segunda Quincena';
            }

            // Insertar los datos en la tabla pago_planilla
            $query_insert = "INSERT INTO pago_planilla (id_planilla, id_usuario, salario_base, total_deducciones, total_bonos, pago_horas_extras, salario_neto, tipo_quincena, fecha_pago) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt_insert = $conn->prepare($query_insert);
            $stmt_insert->bind_param("iiddddds", $id_planilla, $id_usuario, $salario_base, $total_deducciones, $total_bonos, $pago_horas_extras, $salario_neto, $tipo_quincena);

            if ($stmt_insert->execute()) {
                $mensaje = "Los pagos fueron ejecutados correctamente.";
            } else {
                $mensaje = "Ya se realizaron los pagos para esta Quincena.";
            }

            $stmt_insert->close(); // Cerrar la declaración de insert
        }
    } else {
        $mensaje = "No se encontraron registros en la tabla planilla.";
    }

    // Cerrar la conexión
    $conn->close();

    // Devolver el mensaje en formato JSON
    echo json_encode(['mensaje' => $mensaje]);
}
?>
