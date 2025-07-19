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

$UsuarioDAO = new UsuarioDAOSImpl();
$VacacionDAO = new VacacionDAOSImpl();
$HistorialVacacionDAO = new historialVacacionesDAOSImpl();

$user_id = $_SESSION['id_usuario'];
$diasRestantes = $HistorialVacacionDAO->getDiasRestantes($user_id);

$mensaje_exito = "";
$errores = [];

function getDiasEntreFechas($inicio, $fin) {
    $dias = [];
    $current = strtotime($inicio);
    $end = strtotime($fin);
    while ($current <= $end) {
        $dias[] = date('Y-m-d', $current);
        $current = strtotime('+1 day', $current);
    }
    return $dias;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $fecha_fin = $_POST['fecha_fin'] ?? '';
    $razon = $_POST['razon'] ?? '';
    $observaciones = $_POST['observaciones'] ?? '';
    $diasTomado = $_POST['diasTomado'] ?? '';
    $medio_dia = $_POST['medio_dia'] ?? '';

    // Validaciones básicas
    if (empty($fecha_inicio)) $errores[] = "La fecha de inicio es obligatoria.";
    if (empty($fecha_fin)) $errores[] = "La fecha de fin es obligatoria.";
    if (empty($diasTomado)) $errores[] = "Los días tomados son obligatorios.";
    if (empty($razon)) $errores[] = "La razón es obligatoria.";
    if (empty($observaciones)) $errores[] = "Las observaciones son obligatorias.";

    // Calcular días del rango
    $dias_rango = getDiasEntreFechas($fecha_inicio, $fecha_fin);

    // Si el usuario aún no ha seleccionado el día de medio día y el número es decimal
    if (empty($medio_dia) && fmod(floatval($diasTomado), 1) !== 0.0 && empty($errores)) {
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="utf-8">
            <title>Seleccionar Medio Día</title>
            <link href="assets/css/bootstrap.css" rel="stylesheet">
            <style>
                body { font-family: 'Open Sans', sans-serif; background: #f9f9f9; }
                .container { background: #fff; padding: 40px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); width: 50%; margin: 80px auto; }
                .form-group { margin-bottom: 20px; }
                .btn { 
                    background-color: #0A3D55; 
                    color: white; 
                    padding: 10px 20px; 
                    border-radius: 5px; 
                    border: none; 
                }
                .btn:hover { background-color: #147964; }
            </style>
        </head>
        <body>
        <div class="container">
            <h2>¿Cuál de estos días será medio día?</h2>
            <form method="POST">
                <input type="hidden" name="fecha_inicio" value="<?= htmlspecialchars($fecha_inicio) ?>">
                <input type="hidden" name="fecha_fin" value="<?= htmlspecialchars($fecha_fin) ?>">
                <input type="hidden" name="razon" value="<?= htmlspecialchars($razon) ?>">
                <input type="hidden" name="observaciones" value="<?= htmlspecialchars($observaciones) ?>">
                <input type="hidden" name="diasTomado" value="<?= htmlspecialchars($diasTomado) ?>">
                <div class="form-group">
                    <?php foreach ($dias_rango as $dia): ?>
                        <div>
                            <input type="radio" name="medio_dia" value="<?= $dia ?>" required> <?= $dia ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="btn">Confirmar</button>
                <a href="SolicitarMedioDia.php" class="btn">Cancelar</a>
            </form>
        </div>
        </body>
        </html>
        <?php
        exit;
    }

    // Validar días disponibles
    if (!$VacacionDAO->validarDiasDisponibles($user_id, floatval($diasTomado))) {
        $errores[] = "No tienes suficientes días de vacaciones disponibles para esta solicitud.";
    }

    // Si no hay errores, registrar la solicitud y descontar días
    if (empty($errores)) {
        $id_historial = $HistorialVacacionDAO->getHistorialVacaciones($user_id);
        $fechacreacion = date("Y-m-d H:i:s");
        $usuariocreacion = $_SESSION['username'] ?? "admin";
        $fechamodificacion = date("Y-m-d H:i:s");
        $usuariomodificacion = $_SESSION['username'] ?? "admin";
        $id_estado_vacacion = 1; // Pendiente
        $SolicitudEditar = 'No';

        // Registrar la vacación
        $VacacionDAO->IngresarVacacion(
            $razon,
            $diasTomado,
            $fecha_inicio,
            $observaciones . ($medio_dia ? " | Medio día: $medio_dia" : ""),
            $user_id,
            $id_historial,
            $fechacreacion,
            $usuariocreacion,
            $fechamodificacion,
            $usuariomodificacion,
            $id_estado_vacacion,
            $SolicitudEditar,
            $fecha_fin
        );

        // Descontar los días directamente en la base de datos
        include 'conexion.php'; 
        $diasTomadoFloat = floatval($diasTomado);
        $sql = "UPDATE historial_vacaciones SET DiasRestantes = DiasRestantes - ? WHERE id_usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $diasTomadoFloat, $user_id);
        $stmt->execute();
        $stmt->close();

        $mensaje_exito = "Solicitud de vacaciones ingresada correctamente.";
        $diasRestantes = $HistorialVacacionDAO->getDiasRestantes($user_id);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Solicitar Medio Día de Vacación</title>
  <link href="assets/css/bootstrap.css" rel="stylesheet">
  <style>
    body { font-family: 'Open Sans', sans-serif; background: #f9f9f9; }
    .container { background: #fff; padding: 40px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); width: 50%; margin: 80px auto; }
    .form-group { margin-bottom: 20px; }
    .btn { 
        background-color: #0B4F6C; 
        color: white; 
        padding: 10px 20px; 
        border-radius: 5px; 
        border: none; 
    }
    .btn:hover { background-color: #147964; }
    .alert { margin-bottom: 20px; }
  </style>
</head>
<body>
  <div class="container">
    <h2 class="text-center">Solicitar Vacaciones con Medio Día</h2>
    <div class="alert alert-info text-center">
      <strong>Días Restantes:</strong> <?= $diasRestantes ?>
    </div>
    <?php if (!empty($errores)): ?>
      <div class="alert alert-danger">
        <ul>
          <?php foreach ($errores as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
    <?php if (!empty($mensaje_exito)): ?>
      <div class="alert alert-success text-center">
        <?= htmlspecialchars($mensaje_exito) ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label for="fecha_inicio">Fecha de inicio:</label>
        <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" required>
      </div>
      <div class="form-group">
        <label for="fecha_fin">Fecha de fin:</label>
        <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" required>
      </div>
      <div class="form-group">
        <label for="diasTomado">Días a tomar (puede ser decimal, ej: 3.5):</label>
        <input type="number" step="0.5" min="0.5" id="diasTomado" name="diasTomado" class="form-control" required>
      </div>
      <div class="form-group">
        <label for="razon">Razón:</label>
        <input type="text" id="razon" name="razon" class="form-control" required>
      </div>
      <div class="form-group">
        <label for="observaciones">Observaciones:</label>
        <input type="text" id="observaciones" name="observaciones" class="form-control" required>
      </div>
      <div class="form-group text-center">
        <button type="submit" class="btn">Solicitar</button>
        <a href="SolicitarVacacion.php" class="btn">Volver</a>
      </div>
    </form>
  </div>
</body>
</html>

