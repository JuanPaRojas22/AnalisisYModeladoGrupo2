<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php"); exit;
}

// Quitar foco (volver al panel)
if (isset($_POST['clear_focus'])) {
    unset($_SESSION['benef_user_id']);
    session_write_close();
    header("Location: admin_beneficios.php"); exit;
}

// Fijar foco y redirigir a detalles
if (isset($_POST['usuario_id'])) {
    $_SESSION['benef_user_id'] = (int) $_POST['usuario_id'];
    // opcional: decidir destino por 'next' o 'accion'
    $next = $_POST['next'] ?? '';
    if (isset($_POST['accion']) && $_POST['accion'] === 'agregar') { $next = 'agregar'; }

    session_write_close();
    if ($next === 'agregar') {
        header("Location: detalles_beneficios.php"); // o agregar_beneficio.php si lo usas
        exit;
    }
    header("Location: detalles_beneficios.php"); exit;
}

// Si no vino nada, vuelve al panel
header("Location: admin_beneficios.php"); exit;

