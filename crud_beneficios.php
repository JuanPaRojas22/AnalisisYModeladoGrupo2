<?php
session_start();
header('Content-Type: application/json');

// Conexión
$host = "accespersoneldb.mysql.database.azure.com";
$user = "adminUser";
$password = "admin123+";
$dbname = "gestionEmpleados";
$port = 3306;

$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
if (!$conn->real_connect($host, $user, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
    echo json_encode(["success"=>false,"message"=>"Error de conexión: ".mysqli_connect_error()]); exit;
}
mysqli_set_charset($conn, "utf8mb4");

// Usuario objetivo (foco si existe; si no, el logueado)
$targetUserId = isset($_SESSION['benef_user_id'])
    ? (int)$_SESSION['benef_user_id']
    : (int)($_SESSION['id_usuario'] ?? 0);
if ($targetUserId <= 0) { echo json_encode(["success"=>false,"message"=>"Usuario no válido"]); exit; }

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ===== CREATE =====
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Seguridad: si es admin puede agregar para cualquier usuario; si no, solo para sí mismo
    $isAdminMaster = isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin_master';

    $id_usuario_post = (int)($_POST['id_usuario'] ?? 0);
    $id_usuario_sesion = (int)($_SESSION['id_usuario'] ?? 0);

    $id_usuario = $isAdminMaster ? $id_usuario_post : $id_usuario_sesion;

    $razon           = trim($_POST['razon'] ?? '');
    $monto           = (float)($_POST['monto'] ?? 0);
    $medismart       = trim($_POST['identificacion_medismart'] ?? '');
    $valor_total     = (float)($_POST['valor_plan_total'] ?? 0);
    $aporte_patrono  = (float)($_POST['aporte_patrono'] ?? 0);
    $beneficiarios   = (int)($_POST['beneficiarios'] ?? 0);

    if ($id_usuario <= 0 || $razon === '') {
        echo json_encode(["success" => false, "message" => "Datos inválidos"]);
        exit;
    }

    $sql = "INSERT INTO beneficios
            (id_usuario, razon, monto, identificacion_medismart, valor_plan_total, aporte_patrono, beneficiarios, fechacreacion)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["success" => false, "message" => "Error en prepare: " . $conn->error]);
        exit;
    }

    // Tipos: i s d s d d i
    if (!$stmt->bind_param("isdsddi", $id_usuario, $razon, $monto, $medismart, $valor_total, $aporte_patrono, $beneficiarios)) {
        echo json_encode(["success" => false, "message" => "Error en bind_param: " . $stmt->error]);
        exit;
    }

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Beneficio agregado correctamente"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al agregar el beneficio"]);
    }
    $stmt->close();
    exit;
}

// ===== UPDATE =====
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_beneficio = (int)($_POST['id_beneficio'] ?? 0);
    $razon = trim($_POST['razon'] ?? '');
    $monto = (float)($_POST['monto'] ?? 0);
    $medismart = trim($_POST['identificacion_medismart'] ?? '');
    $valor_total = (float)($_POST['valor_plan_total'] ?? 0);
    $aporte_patrono = (float)($_POST['aporte_patrono'] ?? 0);
    $beneficiarios = (int)($_POST['beneficiarios'] ?? 0);

    if ($id_beneficio <= 0 || $razon === '') { echo json_encode(["success"=>false,"message"=>"Datos inválidos"]); exit; }

    $sql = "UPDATE beneficios
            SET razon=?, monto=?, identificacion_medismart=?, valor_plan_total=?,
                aporte_patrono=?, beneficiarios=?, fechamodificacion=NOW()
            WHERE id_beneficio=? AND id_usuario=?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { echo json_encode(["success"=>false,"message"=>"Prepare falló: ".$conn->error]); exit; }

    if (!$stmt->bind_param("sdsddiii", $razon, $monto, $medismart, $valor_total, $aporte_patrono, $beneficiarios, $id_beneficio, $targetUserId)) {
        echo json_encode(["success"=>false,"message"=>"Bind falló: ".$stmt->error]); exit;
    }

    $ok = $stmt->execute();
    $stmt->close();
    echo json_encode(["success"=>$ok,"message"=>$ok?"Beneficio actualizado.":"No se actualizó (pertenencia)"]); exit;
}

// ===== DELETE =====
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $id_beneficio = (int)($_GET['id'] ?? 0);
    if ($id_beneficio <= 0) { echo json_encode(["success"=>false,"message"=>"ID inválido"]); exit; }

    $sql = "DELETE FROM beneficios WHERE id_beneficio=? AND id_usuario=?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { echo json_encode(["success"=>false,"message"=>"Prepare falló: ".$conn->error]); exit; }

    if (!$stmt->bind_param("ii", $id_beneficio, $targetUserId)) {
        echo json_encode(["success"=>false,"message"=>"Bind falló: ".$stmt->error]); exit;
    }

    $ok = $stmt->execute();
    $stmt->close();
    echo json_encode(["success"=>$ok,"message"=>$ok?"Beneficio eliminado.":"No se eliminó (pertenencia)"]); exit;
}

echo json_encode(["success"=>false,"message"=>"Acción no válida"]);
