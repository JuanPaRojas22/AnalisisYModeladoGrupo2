<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

if (isset($_POST['usuario_id'])) {
    $_SESSION['benef_user_id'] = (int) $_POST['usuario_id'];
    
    // Si viene de "Agregar Beneficio"
    if (isset($_POST['accion']) && $_POST['accion'] === 'agregar') {
        header("Location: agregar_beneficio.php"); // o tu modal
        exit;
    }
    
    // Si viene de "Ver Beneficios"
    header("Location: detalles_beneficios.php");
    exit;
}

// Opción para volver a modo normal (mis beneficios)
if (isset($_POST['clear_focus'])) {
    unset($_SESSION['benef_user_id']);
    header("Location: admin_beneficios.php");
    exit;
}
