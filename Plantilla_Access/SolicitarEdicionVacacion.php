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
        body { font-family: Arial, Helvetica, sans-serif; }
        * { box-sizing: border-box; }

        input[type=text], input[type=date], input[type=number] {
            width: 100%;
            padding: 15px;
            margin: 5px 0 22px 0;
            display: inline-block;
            border: none;
            background: #f1f1f1;
        }

        input:focus {
            background-color: #ddd;
            outline: none;
        }

        button {
            background-color: #04AA6D;
            color: white;
            padding: 14px 20px;
            margin: 8px 0;
            border: none;
            cursor: pointer;
            width: 100%;
            opacity: 0.9;
        }

        button:hover {
            opacity: 1;
        }

        .cancelbtn {
            padding: 14px 20px;
            background-color: #f44336;
        }

        .container {
            padding: 16px;
            max-width: 1000px;
            width: 80%;
            margin: 70px auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.1);
        }

        @media screen and (max-width: 600px) {
            .container {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <a href="SolicitarVacacion.php" class="btn btn-success">Volver</a>
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

        <div class="clearfix">
            <button type="submit" class="signupbtn">Solicitar</button>
        </div>
    </form>
</div>

</body>
</html>
