<?php
session_start();
$conexion = new mysqli("localhost", "root", "", "gestionempleados");

if ($conexion->connect_error) {
    die(json_encode(["success" => false, "message" => "Error de conexión: " . $conexion->connect_error]));
}

// Validar si la acción está definida
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action == 'add') {
    // Agregar beneficio
    $id_usuario = $_POST['id_usuario'] ?? 0;
    $razon = $_POST['razon'] ?? '';
    $monto = $_POST['monto'] ?? 0;
    $medismart = $_POST['identificacion_medismart'] ?? '';
    $valor_total = $_POST['valor_plan_total'] ?? 0;
    $aporte_patrono = $_POST['aporte_patrono'] ?? 0;
    $beneficiarios = $_POST['beneficiarios'] ?? 0;

    if ($id_usuario > 0 && !empty($razon)) {
        $sql = "INSERT INTO beneficios (id_usuario, razon, monto, identificacion_medismart, valor_plan_total, aporte_patrono, beneficiarios, fechacreacion) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("isdsddi", $id_usuario, $razon, $monto, $medismart, $valor_total, $aporte_patrono, $beneficiarios);
        
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Beneficio agregado correctamente"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al agregar el beneficio"]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Datos inválidos"]);
    }
}

elseif ($action == 'edit') {
    // Editar beneficio
    $id_beneficio = $_POST['id_beneficio'] ?? 0;
    $razon = $_POST['razon'] ?? '';
    $monto = $_POST['monto'] ?? 0;
    $medismart = $_POST['identificacion_medismart'] ?? '';
    $valor_total = $_POST['valor_plan_total'] ?? 0;
    $aporte_patrono = $_POST['aporte_patrono'] ?? 0;
    $beneficiarios = $_POST['beneficiarios'] ?? 0;

    if ($id_beneficio > 0) {
        $sql = "UPDATE beneficios SET razon = ?, monto = ?, identificacion_medismart = ?, valor_plan_total = ?, aporte_patrono = ?, beneficiarios = ?, fechamodificacion = NOW() WHERE id_beneficio = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sdsddii", $razon, $monto, $medismart, $valor_total, $aporte_patrono, $beneficiarios, $id_beneficio);
        
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Beneficio actualizado correctamente"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al actualizar el beneficio"]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "ID de beneficio inválido"]);
    }
}

elseif ($action == 'delete') {
    // Eliminar beneficio
    $id_beneficio = $_GET['id'] ?? 0;

    if ($id_beneficio > 0) {
        $sql = "DELETE FROM beneficios WHERE id_beneficio = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id_beneficio);
        
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Beneficio eliminado correctamente"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al eliminar el beneficio"]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "ID de beneficio inválido"]);
    }
} 

else {
    echo json_encode(["success" => false, "message" => "Acción no válida"]);
}

$conexion->close();
?>
