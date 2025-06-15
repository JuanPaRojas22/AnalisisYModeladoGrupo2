<?php
session_start();
// Parámetros de conexión
$host = "accespersoneldb.mysql.database.azure.com";
$user = "adminUser";
$password = "admin123+";
$dbname = "gestionEmpleados";
$port = 3306;

// Ruta al certificado CA para validar SSL
$ssl_ca = '/home/site/wwwroot/certs/BaltimoreCyberTrustRoot.crt.pem';

// Inicializamos mysqli
$conn = mysqli_init();

// Configuramos SSL
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);


// Intentamos conectar usando SSL (con la bandera MYSQLI_CLIENT_SSL)
if (!$conn->real_connect($host, $user, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Establecemos el charset
mysqli_set_charset($conn, "utf8mb4");

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
        $stmt = $conn->prepare($sql);
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

$conn->close();
?>
