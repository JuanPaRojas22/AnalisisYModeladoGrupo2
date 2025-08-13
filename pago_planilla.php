<?php
// Establecer la zona horaria a Costa Rica
date_default_timezone_set('America/Costa_Rica');
require 'conexion.php';
$conn = obtenerConexion();
session_start();

$mensaje = '';

if (isset($_POST['ejecutar_pago'])) {
    $fecha_pago = date("Y-m-d");
    $id_usuario_sesion = $_SESSION['id_usuario'];
    $rol_usuario = $_SESSION['id_rol'];

    // Obtener usuarios que serán procesados según el rol
    if ($rol_usuario == 2) {
        // Admin Master: procesa todos los usuarios
        $query_usuarios = "SELECT id_usuario, salario_base, total_deducciones, salario_neto FROM planilla";
        $result_usuarios = $conn->query($query_usuarios);
    } elseif ($rol_usuario == 1) {
        // Jefe de departamento: filtrar por su mismo departamento
        $query_departamento = "SELECT id_departamento FROM usuario WHERE id_usuario = ?";
        $stmt_dep = $conn->prepare($query_departamento);
        $stmt_dep->bind_param("i", $id_usuario_sesion);
        $stmt_dep->execute();
        $stmt_dep->bind_result($id_departamento_jefe);
        $stmt_dep->fetch();
        $stmt_dep->close();

        if (!$id_departamento_jefe) {
            echo json_encode(['mensaje' => 'No se encontró el departamento del jefe.']);
            exit;
        }

        $query_usuarios = "SELECT p.id_usuario, p.salario_base, p.total_deducciones, p.salario_neto 
                           FROM planilla p
                           JOIN usuario u ON p.id_usuario = u.id_usuario
                           WHERE u.id_departamento = ?";
        $stmt_usuarios = $conn->prepare($query_usuarios);
        $stmt_usuarios->bind_param("i", $id_departamento_jefe);
        $stmt_usuarios->execute();
        $result_usuarios = $stmt_usuarios->get_result();
    } else {
        echo json_encode(['mensaje' => 'No tienes permisos para ejecutar pagos.']);
        exit;
    }

    if ($result_usuarios && $result_usuarios->num_rows > 0) {
        $pagos_realizados = 0;
        while ($row = $result_usuarios->fetch_assoc()) {
            $id_usuario = $row['id_usuario'];
            $salario_base = $row['salario_base'];
            $total_deducciones = $row['total_deducciones'];
            $salario_neto = $row['salario_neto'];

            // Buscar id_planilla
            $query_planilla = "SELECT id_planilla FROM planilla WHERE id_usuario = ?";
            $stmt_planilla = $conn->prepare($query_planilla);
            $stmt_planilla->bind_param("i", $id_usuario);
            $stmt_planilla->execute();
            $stmt_planilla->bind_result($id_planilla);
            $stmt_planilla->fetch();
            $stmt_planilla->close();

            if (!$id_planilla) {
                continue;
            }

            // Calcular quincena según la fecha de pago
            $dia = date("d", strtotime($fecha_pago));
            $tipo_quincena = ($dia >= 1 && $dia <= 15) ? 'Primera Quincena' : 'Segunda Quincena';

            // Verificar si ya existe pago para este usuario y quincena
            $query_existente = "SELECT 1 
                    FROM pago_planilla 
                    WHERE id_usuario = ? 
                      AND tipo_quincena = ? 
                      AND MONTH(fecha_pago) = MONTH(?) 
                      AND YEAR(fecha_pago) = YEAR(?)";
            $stmt_existente = $conn->prepare($query_existente);
            $stmt_existente->bind_param("isss", $id_usuario, $tipo_quincena, $fecha_pago, $fecha_pago);
            $stmt_existente->execute();
            $stmt_existente->store_result();

            $existe_pago = $stmt_existente->num_rows > 0;
            $stmt_existente->close();

            if ($existe_pago) {
                // Solo saltar este usuario si ya tiene pago registrado
                continue;
            }


            // Obtener bonos
            $query_bonos = "SELECT SUM(monto_total) FROM bonos WHERE id_usuario = ?";
            $stmt_bonos = $conn->prepare($query_bonos);
            $stmt_bonos->bind_param("i", $id_usuario);
            $stmt_bonos->execute();
            $stmt_bonos->bind_result($total_bonos);
            $stmt_bonos->fetch();
            $stmt_bonos->close();
            $total_bonos = $total_bonos ?? 0;

            // Obtener horas extra
            $query_horas = "SELECT SUM(monto_pago) FROM horas_extra WHERE id_usuario = ?";
            $stmt_horas = $conn->prepare($query_horas);
            $stmt_horas->bind_param("i", $id_usuario);
            $stmt_horas->execute();
            $stmt_horas->bind_result($pago_horas_extras);
            $stmt_horas->fetch();
            $stmt_horas->close();
            $pago_horas_extras = $pago_horas_extras ?? 0;

            // Insertar pago
            $query_insert = "INSERT INTO pago_planilla (id_planilla, id_usuario, salario_base, total_deducciones, total_bonos, pago_horas_extras, salario_neto, tipo_quincena, fecha_pago) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt_insert = $conn->prepare($query_insert);
            $stmt_insert->bind_param("iiddddds", $id_planilla, $id_usuario, $salario_base, $total_deducciones, $total_bonos, $pago_horas_extras, $salario_neto, $tipo_quincena);

            if ($stmt_insert->execute()) {
                $pagos_realizados++;

                // Copiar horas extra al historial
                $stmt_copiar = $conn->prepare("INSERT INTO historial_horas_extras (id_usuario, fecha, horas, monto_pago, fecha_pago)
                                               SELECT id_usuario, fecha, horas, monto_pago, NOW() FROM horas_extra WHERE id_usuario = ?");
                $stmt_copiar->bind_param("i", $id_usuario);
                $stmt_copiar->execute();
                $stmt_copiar->close();

                // Eliminar horas extras (esto activa el trigger)
                $stmt_borrar = $conn->prepare("DELETE FROM horas_extra WHERE id_usuario = ?");
                $stmt_borrar->bind_param("i", $id_usuario);
                $stmt_borrar->execute();
                $stmt_borrar->close();

                // Consultar salario_neto actualizado
                $stmt_salario = $conn->prepare("SELECT salario_neto FROM planilla WHERE id_usuario = ?");
                $stmt_salario->bind_param("i", $id_usuario);
                $stmt_salario->execute();
                $stmt_salario->bind_result($salario_neto_actualizado);
                $stmt_salario->fetch();
                $stmt_salario->close();

                // Actualizar salario_neto en pago_planilla
                $stmt_actualizar = $conn->prepare("UPDATE pago_planilla SET salario_neto = ? WHERE id_usuario = ? AND tipo_quincena = ? AND MONTH(fecha_pago) = MONTH(CURDATE()) AND YEAR(fecha_pago) = YEAR(CURDATE())");
                $stmt_actualizar->bind_param("dis", $salario_neto_actualizado, $id_usuario, $tipo_quincena);
                $stmt_actualizar->execute();
                $stmt_actualizar->close();
            }

            $stmt_insert->close();
        }

        if ($pagos_realizados > 0) {
            $mensaje = "Los pagos fueron ejecutados correctamente.";
        } elseif ($result_usuarios && $result_usuarios->num_rows > 0) {
            $mensaje = "Ya se habían generado los pagos para esta quincena.";
        } else {
            $mensaje = "No se encontraron usuarios para procesar pagos.";
        }

        $conn->close();
        echo json_encode(['mensaje' => $mensaje]);
    }
}
?>