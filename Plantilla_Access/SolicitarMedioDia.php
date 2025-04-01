<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  header("Location: login.php");
  exit;
}
require_once __DIR__ . '/Impl/UsuarioDAOSImpl.php';
require_once __DIR__ . '/Impl/VacacionDAOSImpl.php';
require_once __DIR__ . '/Impl/historialVacacionesDAOSImpl.php';
include "template.php";

 //Inicializar las clases DAO
$UsuarioDAO = new UsuarioDAOSImpl();
$VacacionDAO = new VacacionDAOSImpl();
$HistorialVacacionDAO = new historialVacacionesDAOSImpl();

$user_id = $_SESSION['id_usuario'];

// Obtener días de vacaciones restantes para mostrar al usuario
$diasRestantes = $HistorialVacacionDAO->getDiasRestantes($user_id);

$mensaje_exito = "";
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Captura de datos del formulario
    $fecha = $_POST['fecha'] ?? '';
    $razon = $_POST['razon'] ?? '';
    $observaciones = $_POST['observaciones'] ?? '';
    // Para medio día se registra 0.5 día de vacaciones
    $diasTomado = 0.5;

    // Validaciones
    if (empty($fecha)) {
        $errores[] = "La fecha es obligatoria.";
    }
    if (empty($razon)) {
        $errores[] = "La razón es obligatoria.";
    }
    if (empty($observaciones)) {
        $errores[] = "Las observaciones son obligatorias.";
    }
    if (!$VacacionDAO->validarDiasDisponibles($user_id, $diasTomado)) {
        $errores[] = "No tienes suficientes días de vacaciones disponibles para esta solicitud.";
    }

    // Si no hay errores, registrar la solicitud
    if (empty($errores)) {
        $id_historial = $HistorialVacacionDAO->getHistorialVacaciones($user_id);
        $fechacreacion = date("Y-m-d H:i:s");
        $usuariocreacion = "admin"; 
        $fechamodificacion = date("Y-m-d H:i:s");
        $usuariomodificacion = "admin";
        $id_estado_vacacion = 1; // Pendiente
        $SolicitudEditar = 'No';

        $VacacionDAO->IngresarVacacion(
            $razon, 
            $diasTomado, 
            $fecha, 
            $observaciones, 
            $user_id, 
            $id_historial, 
            $fechacreacion, 
            $usuariocreacion, 
            $fechamodificacion, 
            $usuariomodificacion, 
            $id_estado_vacacion, 
            $SolicitudEditar, 
            $fecha // Se usa la misma fecha como inicio y fin
        );

        $mensaje_exito = "Solicitud de medio día ingresada correctamente.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Solicitud de Medio Día</title>
  <link href="assets/css/bootstrap.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
  <style>
    /* Estilos para el modal */
    .modal {
      display: none; 
      position: fixed; 
      z-index: 1;
      left: 0;
      top: 0;
      width: 100%; 
      height: 100%; 
      background-color: rgba(0, 0, 0, 0.5);
      justify-content: center;
      align-items: center;
    }
    .modal-content {
      background-color: #fff;
      padding: 20px;
      border-radius: 10px;
      width: 300px;
      position: relative;
      text-align: center;
    }
    .close {
      position: absolute;
      top: 10px;
      right: 20px;
      font-size: 25px;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <div class="container mt-4">
    <h2 class="text-center">Solicitud de Medio Día de Vacaciones</h2>

    <!-- Mostrar días restantes -->
    <div class="alert alert-info text-center">
      <strong>Días Restantes:</strong> <?php echo $diasRestantes; ?>
    </div>

    <!-- Botón para abrir el modal -->
    <div class="text-center mb-4">
      <button onclick="document.getElementById('modalMedioDia').style.display='flex'" class="btn btn-primary">
        Solicitar Medio Día
      </button>
      <a href="SolicitarVacacion.php" class="btn btn-secondary">Volver</a>
    </div>

    <!-- Mensajes de error y éxito (opcional, se pueden mostrar en la misma página) -->
    <?php if (!empty($errores)): ?>
      <div class="alert alert-danger">
        <ul>
          <?php foreach ($errores as $error): ?>
            <li><?php echo $error; ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if (!empty($mensaje_exito)): ?>
      <div class="alert alert-success text-center">
        <?php echo $mensaje_exito; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Modal para Solicitud de Medio Día -->
  <div id="modalMedioDia" class="modal">
    <div class="modal-content">
      <span onclick="document.getElementById('modalMedioDia').style.display='none'" class="close" title="Cerrar Modal">&times;</span>
      <h3>Solicitar Medio Día</h3>
      <form action="" method="POST">
        <div class="form-group">
          <label for="fecha">Fecha de Vacaciones:</label>
          <input type="date" id="fecha" name="fecha" class="form-control" required>
        </div>
        <div class="form-group mt-2">
          <label for="razon">Razón:</label>
          <input type="text" id="razon" name="razon" class="form-control" placeholder="Motivo de la solicitud" required>
        </div>
        <div class="form-group mt-2">
          <label for="observaciones">Observaciones:</label>
          <input type="text" id="observaciones" name="observaciones" class="form-control" placeholder="Observaciones adicionales" required>
        </div>
        <div class="form-group text-center mt-3">
          <button type="submit" class="btn btn-primary">Enviar Solicitud</button>
          <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalMedioDia').style.display='none'">Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Cerrar el modal si se hace clic fuera del contenido
    window.onclick = function(event) {
      var modal = document.getElementById('modalMedioDia');
      if (event.target == modal) {
        modal.style.display = "none";
      }
    }
  </script>

<style>
    body {
        font-family: 'Ruda', sans-serif;
        background-color: #f7f7f7;
        margin: 0;
        padding: 0;
    }

    .container {
        width: 80%;
        margin: 200px auto;
        padding: 20px;
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.48);


    }

    h1 {
        text-align: center;
        color: #333;
        margin-bottom: 50px;
        margin-right: 10%;
        font-weight: bold;
    }

    h3 {
        text-align: center;
        color: black;
        margin-bottom: 50px;
        margin-right: 10%;
        font-weight: bold;
    }

    .button {
        display: inline-block;
        background-color: #c9aa5f;
        color: white;
        padding: 10px 20px;
        font-size: 16px;
        font-weight: bold;
        text-align: center;
        text-decoration: none;
        border-radius: 5px;
        margin-bottom: 20px;
        transition: background-color 0.3s;
    }

    .btn {
        display: inline-block;
        background-color: #c9aa5f;
        color: white;
        padding: 10px 20px;
        font-size: 25px;
        font-weight: bold;
        text-align: center;
        text-decoration: none;
        border-radius: 5px;
        margin-bottom: 20px;
        transition: background-color 0.3s;
    }



    .btn:hover {
        background-color: #c9aa5f;
    }

    .btn:active {
        background-color: #c9aa5f;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        border-radius: 8px;
        overflow: hidden;
    }

    th,
    td {
        padding: 12px;
        text-align: center;
        font-size: 16px;
        color: #555;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #c9aa5f;
        color: #fff;
        text-align: center;
    }

    tr:hover {
        background-color: #f1f1f1;
    }

    td {
        background-color: #f9f9f9;
    }

    .no-records {
        text-align: center;
        font-style: italic;
        color: #888;
    }

    /* Estilos del fondo del modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
    }

    /* Contenido del modal */
    .modal-content {
        background-color: white;
        padding: 20px;
        border-radius: 10px;
        width: 300px;
        text-align: center;
        margin-bottom: 5%;

    }

    /* Botón de cerrar */
    .close {
        position: absolute;
        top: 10px;
        right: 20px;
        font-size: 25px;
        cursor: pointer;
    }

    /* Botones dentro del modal */
    .modal-content a {
        display: block;
        margin: 10px 0;
        padding: 10px;
        text-decoration: none;
        color: white;
        background-color: gray;
        border-radius: 5px;
        background-color: #c9aa5f;
    }

    .modal-content a:hover {
        background-color: darkgray;
    }

    /* Estilos para los botones alineados */
    .button-container {
        display: flex;
        justify-content: space-between;
        /* Distribuye el espacio entre los botones */
        width: 100%;
    }
</style>

</body>
</html>
