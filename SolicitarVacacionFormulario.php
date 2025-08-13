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

// Obtener los dias reservados por el empleado para que no pueda solicitar vacaciones en esas fechas
$fechasReservadas = $VacacionDAO->getFechasReservadasEmpleado($user_id);

$rangosFechas = array_map(function ($row) {
    return ["from" => $row['fecha_inicio'], "to" => $row['fecha_fin']];
}, $fechasReservadas);

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
    // Capturar datos del formulario
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $fecha_fin = $_POST['fecha_fin'] ?? '';
    $razon = $_POST['razon'] ?? '';
    $observaciones = $_POST['observaciones'] ?? '';
    $medio_dia = $_POST['medio_dia'] ?? '';
    $seleccion_medio_dia = isset($_POST['seleccion_medio_dia']);

    // Validar campos obligatorios
    if (empty($fecha_inicio))
        $errores[] = "La fecha de inicio es obligatoria.";
    if (empty($fecha_fin))
        $errores[] = "La fecha de fin es obligatoria.";
    if (empty($razon))
        $errores[] = "La razón es obligatoria.";
    if (empty($observaciones))
        $errores[] = "Las observaciones son obligatorias.";

    // Calcular días del rango
    $dias_rango = getDiasEntreFechas($fecha_inicio, $fecha_fin);
    $total_dias = count($dias_rango);

    // Preguntar por medio día si el rango es mayor a 1 día y aún no se ha seleccionado
    if (!$seleccion_medio_dia && $total_dias > 0 && empty($errores)) {
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
            <h2>¿Deseas que alguno de estos días sea medio día?</h2>
            <form method="POST">
                <input type="hidden" name="fecha_inicio" value="<?= htmlspecialchars($fecha_inicio) ?>">
                <input type="hidden" name="fecha_fin" value="<?= htmlspecialchars($fecha_fin) ?>">
                <input type="hidden" name="razon" value="<?= htmlspecialchars($razon) ?>">
                <input type="hidden" name="observaciones" value="<?= htmlspecialchars($observaciones) ?>">
                <input type="hidden" name="seleccion_medio_dia" value="1">
                <div class="form-group">
                    <?php foreach ($dias_rango as $dia): ?>
                        <div>
                            <input type="radio" name="medio_dia" value="<?= $dia ?>" required> <?= $dia ?>
                        </div>
                    <?php endforeach; ?>
                    <div>
                        <input type="radio" name="medio_dia" value="omitir" required> No, todos completos
                    </div>
                </div>
                <button type="submit" class="btn">Continuar</button>
            </form>
        </div>
        </body>
        </html>
        <?php
        exit;
    }

    // Calcular días tomados
    if (!empty($medio_dia) && $medio_dia !== 'omitir') {
        $diasTomado = $total_dias - 1 + 0.5;
    } else {
        $diasTomado = $total_dias;
    }

    // Validar días disponibles
    if (!$VacacionDAO->validarDiasDisponibles($user_id, floatval($diasTomado))) {
        $errores[] = "No tienes suficientes días de vacaciones disponibles para esta solicitud.";
    }

    // Si no hay errores, registrar la solicitud y descontar días
    if (empty($errores)) {
        $id_historial = $HistorialVacacionDAO->getHistorialVacaciones($user_id);
        $fechacreacion = date("Y-m-d H:i:s");
        $usuariocreacion = "admin";
        $fechamodificacion = date("Y-m-d H:i:s");
        $usuariomodificacion = "admin";
        $id_estado_vacacion = 1;
        $SolicitudEditar = 'No';

        $resultado = $VacacionDAO->IngresarVacacion(
            $razon,
            $diasTomado,
            $fecha_inicio,
            $observaciones . (!empty($medio_dia) && $medio_dia !== 'omitir' ? " | Medio día: $medio_dia" : ""),
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

        if (!empty($resultado)) {
            $errores = array_merge($errores, $resultado);
        } else {
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
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Solicitar Vacaciones con Medio Día</title>
  <link href="assets/css/bootstrap.css" rel="stylesheet">
  <!-- Flatpickr CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
    .flatpickr-day.reservado {
        background-color: red !important;
        color: white !important;
        border-radius: 50%;
    }
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

    <form action="SolicitarVacacionFormulario.php" method="POST">
      <div class="form-group">
        <label for="fecha_inicio_solicitud">Fecha Inicio:</label>
        <input type="text" id="fecha_inicio_solicitud" name="fecha_inicio" class="form-control" placeholder="Ingrese la fecha de inicio" autofocus>
      </div>
      <div class="form-group">
        <label for="fecha_fin_solicitud">Fecha Fin:</label>
        <input type="text" id="fecha_fin_solicitud" name="fecha_fin" class="form-control" placeholder="Ingrese la fecha de fin">
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
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Fechas reservadas
        const fechasReservadas = <?php echo json_encode($rangosFechas); ?>;

        function configurarCalendario(idCampo) {
            flatpickr(idCampo, {
                dateFormat: "Y-m-d",
                disable: fechasReservadas.map(date => ({ from: date, to: date })),
                onDayCreate: function (dObj, dStr, fp, dayElem) {
                    const date = dayElem.dateObj.toISOString().split('T')[0];
                    fechasReservadas.forEach(range => {
                        if (date >= range.from && date <= range.to) {
                            dayElem.classList.add("reservado");
                            dayElem.style.pointerEvents = "none";
                            dayElem.style.opacity = "0.5";
                        }
                    });
                }
            });
        }

        configurarCalendario("#fecha_inicio_solicitud");
        configurarCalendario("#fecha_fin_solicitud");
    </script>
  </div>
</body>
</html>
