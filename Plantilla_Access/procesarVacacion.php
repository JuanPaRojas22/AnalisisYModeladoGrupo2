<?php
session_start();
require_once __DIR__ . '/Impl/VacacionDAOSImpl.php';
// Verifica si se recicbio el id del usuario y la accion a realizar
if(isset($_GET['id']) && isset($_GET['accion'])){
    $id_vacacion = $_GET['id'];
    $accion = $_GET['accion'];
    $VacacionDAO = new VacacionDAOSImpl();
    // Verifica si la accion es aprobar o rechazar
    if($accion == 'aprobar'){
        $VacacionDAO->aprobarSolicitud($id_vacacion);
    }else if($accion == 'rechazar'){
        $VacacionDAO->rechazarSolicitud($id_vacacion);
    }
    
    // Se redirije de nuevo a la pagina de detalle de vacaciones
    header('Location: vacaciones.php?id='.$id_vacacion);
    exit();
} else {
    echo "Parametros incorrectos";
}

?>