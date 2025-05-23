<?php 
session_start();

require_once __DIR__ . '/Impl/UsuarioDAOSImpl.php';
require_once __DIR__ . '/Impl/VacacionDAOSImpl.php';
require_once __DIR__ . '/Impl/Historial_Solicitud_Modificacion_VacacionesDAOSImpl.php';
require_once __DIR__ . '/Impl/historialVacacionesDAOSImpl.php';
include "template.php";

$UsuarioDAO = new UsuarioDAOSImpl();
$VacacionDAO = new VacacionDAOSImpl();
$Historial_Solicitud_Modificacion_VacacionesDAO = new Historial_Solicitud_Modificacion_VacacionesDAOSImpl();
$HistorialVacacionDAO = new historialVacacionesDAOSImpl();

//var_dump($_SESSION);
// Por si el id viene en la URL, guardarlo en sesión
if (isset($_GET['id'])) {
    $_SESSION['id_vacacion'] = $_GET['id'];

}

// Se Verifica que id_vacacion esté disponible en sesión
if (!isset($_SESSION['id_vacacion'])) {
    die("Error: No se encontró una vacación para editar.");
}

$vacacion_id = $_SESSION['id_vacacion'];

// Se obtiene el id del usuario relacionado con la vacación
$id_usuario = $VacacionDAO->getUserByIdVacacion($vacacion_id);

// En caso de que no se encuentre un usuario asociado a la vacación
if (!$id_usuario) {
    die("Error: No se encontró un usuario asociado a esta vacación.");
}

// Logica para crear una vacacion utilizando el metodo de IngresarVacacion 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar datos del formulario
    $id_vacacion = $vacacion_id;
    $fecha_solicitud = date('Y-m-d');
    $fecha_resolucion = NULL; // Quiero mandarlo nulo
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $fecha_fin = $_POST['fecha_fin'] ?? '';
    $dias_solicitados = $_POST['dias_solicitados'] ?? '';
    $id_usuario = $VacacionDAO->getUserByIdVacacion($id_vacacion);;
    $usuario_aprobador = ""; // Tiene que obtener el nombre del usuario que lo apruebe
    $razon_modificacion = $_POST['razon_modificacion'] ?? '';
    $estado = 'Pendiente';

    // Validar campos obligatorios
    $errores = [];
    if (empty($id_vacacion)) $errores[] = "La vacación es requerida."; // 
    if (empty($fecha_solicitud)) $errores[] = "La fecha de solicitud es requerida.";
    // if (empty($fecha_resolucion)) $errores[] = "La fecha de resolución es requerida."; // 
    if (empty($fecha_inicio)) $errores[] = "La fecha de inicio es requerida."; 
    if (empty($fecha_fin)) $errores[] = "La fecha de fin es requerida.";
    if (empty($dias_solicitados)) $errores[] = "Los días solicitados son requeridos.";
    if (empty($id_usuario)) $errores[] = "El usuario es requerido."; // 
    if (empty($razon_modificacion)) $errores[] = "La razón de la modificación es requerida.";


    // Si hay errores, mostrarlos
    if (!empty($errores)) {
        var_dump($id_vacacion);
        var_dump($id_usuario);
        var_dump($_SESSION);
        foreach ($errores as $error) {
            echo "<script>alert('$error');</script>";
            
        }
    } else {
            
            $Historial_Solicitud_Modificacion_VacacionesDAO->IngresarHistorialSolicitudModificacionVacaciones
            ($id_vacacion, $fecha_solicitud, $fecha_resolucion, $fecha_inicio, $fecha_fin, $dias_solicitados, 
            $id_usuario, $usuario_aprobador, $razon_modificacion, $estado); 
            echo "<script>alert('Solicitud de edicion de vacaciones ingresada correctamente.');</script>";
            
        
    }
}    


?>


<!DOCTYPE html>
<html>
<head>
<style>
    body {
        font-family: 'Ruda', sans-serif;
        background-color: #f7f7f7;
        margin: 0;
        padding: 0;
    }

    .container {
        width: 60%;
        max-width: 800px;
        margin: 50px auto;
        padding: 20px;
        background-color: #ffffff;
        border-radius: 12px;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
        position: relative;
        text-align: center;
    }

    h1 {
        color: #333;
        margin-bottom: 20px;
        font-weight: bold;
        font-size: 28px;
    }

    .form-group {
        margin-bottom: 20px;
        text-align: left;
    }

    .form-group label {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 5px;
    }

    input[type="text"], input[type="date"], input[type="number"] {
        width: 100%;
        padding: 12px;
        margin: 8px 0;
        display: inline-block;
        border: 1px solid #ddd;
        background: #f9f9f9;
        font-size: 16px;
        border-radius: 5px;
    }

    input:focus {
        background-color: #e8f4f8;
        outline: none;
        border-color: #147964;
    }

    .btn-container {
        margin-top: 20px;
    }

    .btn {
        background-color: #0B4F6C;
        color: white;
        padding: 8px 16px; /* Reduced padding */
        font-size: 14px; /* Smaller font size */
        font-weight: bold;
        text-align: center;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s ease;
        border: none;
        outline: none;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .btn:hover {
        background-color: #C64A4A;
    }

    .btn1 {
        background-color: #147964;
        color: white;
        padding: 8px 16px; /* Reduced padding */
        font-size: 14px; /* Smaller font size */
        font-weight: bold;
        text-align: center;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s ease;
        border: none;
        outline: none;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .btn1:hover {
        background-color: #147964;
    }

    /* Positioning the 'Volver' button in the left corner */
    .volunteer-btn {
        position: absolute;
        top: 20px;
        left: 20px;
        background-color: #C64A4A;
        color: white;
        padding: 8px 16px; /* Reduced padding */
        font-size: 14px; /* Smaller font size */
        font-weight: bold;
        text-align: center;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s ease;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .volunteer-btn:hover {
        background-color: #C64A4A;
    }
    td, div {
            color: black !important;
        }
</style>


</head>
<body>

<div class="container">
    
    <h1>Solicitar Edición de Vacaciones</h1>
    <p>Por favor, complete este formulario para solicitar la edición de sus vacacion.</p>
    <hr>

    <form action="SolicitarEdicionVacacion.php" method="post">
        <label for="fecha_inicio"><b>Fecha de Inicio</b></label>
        <input type="date" placeholder="Ingrese la fecha de inicio" name="fecha_inicio" required>

        <label for="fecha_fin"><b>Fecha de Fin</b></label>
        <input type="date" placeholder="Ingrese la fecha de fin" name="fecha_fin" required>

        <label for="dias_solicitados"><b>Días Tomados</b></label>
        <input type="number" placeholder="Ingrese los días tomados" name="dias_solicitados" required>

        <label for="razon_modificacion"><b>Razón</b></label>
        <input type="text" placeholder="Ingrese la razón" name="razon_modificacion" required>
        <a href="SolicitarVacacion.php" class="volunteer-btn">Volver</a>
        <div class="clearfix">
            <button type="submit" class="btn1">Solicitar</button>
        </div>
        
    </form>
</div>

</body>
</html>
