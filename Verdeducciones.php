<?php
require 'conexion.php';
session_start();
require 'template.php';

$rol = $_SESSION['id_rol'];
$id_usuario_logueado = $_SESSION['id_usuario'];

// Obtener departamento del usuario logueado si rol == 1 (admin normal)
$id_departamento_logueado = null;
if ($rol == 1) {
    $sql_dep = "SELECT id_departamento FROM Usuario WHERE id_usuario = ?";
    $stmt_dep = $conn->prepare($sql_dep);
    $stmt_dep->bind_param("i", $id_usuario_logueado);
    $stmt_dep->execute();
    $result_dep = $stmt_dep->get_result();
    if ($row_dep = $result_dep->fetch_assoc()) {
        $id_departamento_logueado = $row_dep['id_departamento'];
    }
}

// Paginación
$por_pagina = 5;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
$offset = ($pagina_actual - 1) * $por_pagina;

// Filtro usuario desde POST (solo para rol 2)
$id_usuario_seleccionado = null;
if ($rol == 2 && isset($_POST['id_usuario']) && $_POST['id_usuario'] !== '') {
    $id_usuario_seleccionado = (int)$_POST['id_usuario'];
}

// Construir consulta base
$params = [];
$tipos = '';
$sql_base = "
    SELECT SQL_CALC_FOUND_ROWS
        d.id_deduccion, 
        d.id_usuario, 
        u.nombre, 
        u.apellido, 
        d.razon, 
        d.deudor, 
        d.concepto, 
        d.lugar, 
        d.deuda_total, 
        d.monto_mensual,
        d.aportes, 
        d.saldo_pendiente, 
        d.saldo_pendiente_dolares, 
        d.fechacreacion
    FROM Deducciones d
    INNER JOIN Usuario u ON d.id_usuario = u.id_usuario
";

$where_clauses = [];

// Filtrado según rol
if ($rol == 3) {
    // Usuario normal solo sus datos
    $where_clauses[] = "d.id_usuario = ?";
    $tipos .= "i";
    $params[] = $id_usuario_logueado;

} elseif ($rol == 1) {
    // Admin normal: usuarios de su departamento
    if ($id_departamento_logueado !== null) {
        $where_clauses[] = "u.id_departamento = ?";
        $tipos .= "i";
        $params[] = $id_departamento_logueado;
    }

} elseif ($rol == 2) {
    // Admin master: puede filtrar o ver todo
    if ($id_usuario_seleccionado !== null) {
        $where_clauses[] = "d.id_usuario = ?";
        $tipos .= "i";
        $params[] = $id_usuario_seleccionado;
    }
}

if (count($where_clauses) > 0) {
    $sql_base .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql_base .= " ORDER BY d.fechacreacion DESC LIMIT ? OFFSET ?";
$tipos .= "ii";
$params[] = $por_pagina;
$params[] = $offset;

$stmt = $conn->prepare($sql_base);
$stmt->bind_param($tipos, ...$params);
$stmt->execute();
$result_deducciones = $stmt->get_result();

$total_resultado = $conn->query("SELECT FOUND_ROWS()")->fetch_row()[0];
$total_paginas = ceil($total_resultado / $por_pagina);

// Obtener usuarios para filtro solo rol 2 (admin master)
$usuarios = [];
if ($rol == 2) {
    $sql_usuarios = "SELECT id_usuario, nombre, apellido FROM Usuario";
    $result_usuarios = $conn->query($sql_usuarios);
    while ($row = $result_usuarios->fetch_assoc()) {
        $usuarios[] = $row;
    }
}
?>
<!-- Resto de tu HTML y lógica permanece igual -->
