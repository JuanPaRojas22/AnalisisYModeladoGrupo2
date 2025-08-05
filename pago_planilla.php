<?php
date_default_timezone_set('America/Costa_Rica');
require 'conexion.php';
$conn = obtenerConexion();
session_start();

$mensaje = '';
$pagos_realizados = 0;
$pagos_omitidos = 0;

if (isset($_POST['ejecutar_pago'])) {
    $fecha_pago = date("Y-m-d");

    $query_usuarios = "SELECT id_usuario, salario_base, total_deducciones FROM planilla";
    $result_usuarios = $conn->query($query_usuarios);

    if ($result_usuarios->num_rows > 0) {
        while ($row = $result_usuarios->fetch_assoc()) {
            $id_usuario = $row['id_usuario'];
            $salario_base = $row['salario_base'];
            $total_deducciones = $row['total_deducciones'] ?? 0;

            // Buscar ID planilla
            $stmt_planilla = $conn->prepare("SELECT id_planilla FROM planilla WHERE id_usuario = ?");
            $stmt_planilla->bind_param("i", $id_usuario);
            $stmt_planilla->execute();
            $stmt_planilla->bind_result($id_planilla);
            $stmt_planilla->fetch();
            $stmt_planilla->close();

            if (!$id_planilla) continue;

            // Sumar horas extras
            $stmt_horas_extras = $conn->prepare("SELECT SUM(monto_pago) FROM horas_extra WHERE id_usuario = ?");
            $stmt_horas_extras->bind_param("i", $id_usuario);
            $stmt_horas_extras->execute();
            $stmt_horas_extras->bind_result($pago_horas_extras);
            $stmt_horas_extras->fetch();
            $stmt_horas_extras->close();

            $pago_horas_extras = $pago_horas_extras ?? 0;

            // Sumar bonos
            $stmt_bonos = $conn->prepare("SELECT SUM(monto_total) FROM bonos WHERE id_usuario = ?");
            $stmt_bonos->bind_param("i", $id_usuario);
            $stmt_bonos->execute();
            $stmt_bonos->bind_result($total_bonos);
            $stmt_bonos->fetch();
            $stmt_bonos->close();

            $total_bonos = $total_bonos ?? 0;

            // Calcular salario neto
            $salario_neto = $salario_base / 2 - $total_deducciones + $total_bonos + $pago_horas_extras;

            // Determinar tipo de quincena
            $tipo_quincena = (intval(date("d", strtotime($fecha_pago))) <= 15) ? 'Primera Quincena' : 'Segunda Quincena';

            // Intentar insertar en pago_planilla
            $stmt_insert = $conn->prepare("INSERT INTO pago_planilla (id_planilla, id_usuario, salario_base, total_deducciones, total_bonos, pago_horas_extras, salario_neto, tipo_quincena, fecha_pago) 
                                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt_insert->bind_param("iiddddds", $id_planilla, $id_usuario, $salario_base, $total_deducciones, $total_bonos, $pago_horas_extras, $salario_neto, $tipo_quincena);

            if ($stmt_insert->execute()) {
                // Guardar en historial
                $observaciones = 'Pago procesado automáticamente';
                $stmt_historial = $conn->prepare("INSERT INTO historial_pagos (id_usuario, fecha_pago, tipo_quincena, salario_base, total_deducciones, total_bonos, pago_horas_extras, salario_neto, observaciones)
                                                  VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?)");
                $stmt_historial->bind_param("issddddds", $id_usuario, $tipo_quincena, $salario_base, $total_deducciones, $total_bonos, $pago_horas_extras, $salario_neto, $observaciones);
                $stmt_historial->execute();
                $stmt_historial->close();

                // Eliminar horas extra pagadas
                $stmt_borrar = $conn->prepare("DELETE FROM horas_extra WHERE id_usuario = ?");
                $stmt_borrar->bind_param("i", $id_usuario);
                $stmt_borrar->execute();
                $stmt_borrar->close();

                $pagos_realizados++;
            } else {
                $pagos_omitidos++;
            }

            $stmt_insert->close();
        }

        $mensaje = "✅ Pagos realizados: $pagos_realizados. ⛔ Pagos omitidos (ya existen): $pagos_omitidos.";
    } else {
        $mensaje = "⚠️ No se encontraron registros en la tabla planilla.";
    }

    $conn->close();
    echo json_encode(['mensaje' => $mensaje]);
}
?>
