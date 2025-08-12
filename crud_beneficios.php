<?php
session_start();
header('Content-Type: application/json'); 

// ===== Conexión (SSL) =====
$host = "accespersoneldb.mysql.database.azure.com";
$user = "adminUser";
$password = "admin123+";
$dbname = "gestionEmpleados";
$port = 3306;

$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);

if (!$conn->real_connect($host, $user, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
    echo json_encode(["success" => false, "message" => "Error de conexión: " . mysqli_connect_error()]);
    exit;
}
mysqli_set_charset($conn, "utf8mb4");

// ===== Determinar usuario objetivo =====
// - Usuario normal: su propio id.
// - Admin master: el usuario en foco guardado en $_SESSION['benef_user_id'].
$isAdminMaster = isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin_master';
$targetUserId  = (int)($_SESSION['id_usuario'] ?? 0);
if ($isAdminMaster && isset($_SESSION['benef_user_id'])) {
    $targetUserId = (int)$_SESSION['benef_user_id'];
}
if ($targetUserId <= 0) {
    echo json_encode(["success" => false, "message" => "Usuario no válido (sesión)"]);
    exit;
}

// ===== Acción =====
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ===== CREATE (add) =====
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ignoramos cualquier id_usuario del POST y usamos $targetUserId
    $razon        = trim($_POST['razon'] ?? '');
    $monto        = (float)($_POST['monto'] ?? 0);
    $medismart    = trim($_POST['identificacion_medismart'] ?? '');
    $valor_total  = (float)($_POST['valor_plan_total'] ?? 0);
    $aporte_patrono = (float)($_POST['aporte_patrono'] ?? 0);
    $beneficiarios  = (int)($_POST['beneficiarios'] ?? 0);

    if ($razon === '') {
        echo json_encode(["success" => false, "message" => "Razón requerida"]);
        exit;
    }

    $sql = "INSERT INTO beneficios 
            (id_usuario, razon, monto, identificacion_medismart, valor_plan_total, aporte_patrono, beneficiarios, fechacreacion)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { echo json_encode(["success"=>false,"message"=>"Prepare falló: ".$conn->error]); exit; }

    // Tipos: i s d s d d i
    if (!$stmt->bind_param("isdsddi", $targetUserId, $razon, $monto, $medismart, $valor_total, $aporte_patrono, $beneficiarios)) {
        echo json_encode(["success"=>false,"message"=>"Bind falló: ".$stmt->error]); exit;
    }

    $ok = $stmt->execute();
    $stmt->close();

    echo json_encode(["success" => $ok, "message" => $ok ? "Beneficio agregado." : "No se pudo agregar"]);
    exit;
}

// ===== UPDATE (edit) =====
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_beneficio = (int)($_POST['id_beneficio'] ?? 0);
    $razon        = trim($_POST['razon'] ?? '');
    $monto        = (float)($_POST['monto'] ?? 0);
    $medismart    = trim($_POST['identificacion_medismart'] ?? '');
    $valor_total  = (float)($_POST['valor_plan_total'] ?? 0);
    $aporte_patrono = (float)($_POST['aporte_patrono'] ?? 0);
    $beneficiarios  = (int)($_POST['beneficiarios'] ?? 0);

    if ($id_beneficio <= 0 || $razon === '') {
        echo json_encode(["success" => false, "message" => "Datos inválidos para actualizar"]);
        exit;
    }

    $sql = "UPDATE beneficios
            SET razon = ?, monto = ?, identificacion_medismart = ?, valor_plan_total = ?, 
                aporte_patrono = ?, beneficiarios = ?, fechamodificacion = NOW()
            WHERE id_beneficio = ? AND id_usuario = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { echo json_encode(["success"=>false,"message"=>"Prepare falló: ".$conn->error]); exit; }

    // Tipos: s d s d d i i i
    if (!$stmt->bind_param("sdsddiii", $razon, $monto, $medismart, $valor_total, $aporte_patrono, $beneficiarios, $id_beneficio, $targetUserId)) {
        echo json_encode(["success"=>false,"message"=>"Bind falló: ".$stmt->error]); exit;
    }

    $ok = $stmt->execute();
    $stmt->close();

    echo json_encode(["success" => $ok, "message" => $ok ? "Beneficio actualizado." : "No se actualizó (verifica pertenencia)"]);
    exit;
}

// ===== DELETE =====
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $id_beneficio = (int)($_GET['id'] ?? 0);
    if ($id_beneficio <= 0) {
        echo json_encode(["success" => false, "message" => "ID inválido"]);
        exit;
    }

    // Solo borra si pertenece al usuario objetivo
    $sql = "DELETE FROM beneficios WHERE id_beneficio = ? AND id_usuario = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { echo json_encode(["success"=>false,"message"=>"Prepare falló: ".$conn->error]); exit; }

    if (!$stmt->bind_param("ii", $id_beneficio, $targetUserId)) {
        echo json_encode(["success"=>false,"message"=>"Bind falló: ".$stmt->error]); exit;
    }

    $ok = $stmt->execute();
    $stmt->close();

    echo json_encode(["success" => $ok, "message" => $ok ? "Beneficio eliminado." : "No se eliminó (verifica pertenencia)"]);
    exit;
}

// ===== Acción no válida =====
echo json_encode(["success" => false, "message" => "Acción no válida"]);
$conn->close();
