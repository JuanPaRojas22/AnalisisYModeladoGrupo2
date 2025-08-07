<?php
require 'conexion.php';
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  header("Location: login.php");
  exit;
}
require 'template.php';

// Mensaje de sesión para mostrar feedback
if (isset($_SESSION['mensaje_exito'])) {
    $mensaje = "<div class='alert alert-success text-center'>" . $_SESSION['mensaje_exito'] . "</div>";
    unset($_SESSION['mensaje_exito']);
} elseif (isset($_SESSION['mensaje_error'])) {
    $mensaje = "<div class='alert alert-danger text-center'>" . $_SESSION['mensaje_error'] . "</div>";
    unset($_SESSION['mensaje_error']);
} else {
    $mensaje = "";
}

// Consulta para obtener planillas con info usuario
$query_planilla = "SELECT p.id_planilla, p.id_usuario, u.nombre, u.apellido, p.salario_base 
FROM Planilla p 
JOIN Usuario u ON p.id_usuario = u.id_usuario";
$result_planilla = $conn->query($query_planilla);

function calcularRetenciones($salario_base)
{
  $salario_base = (float) $salario_base;
  $seguro_social = $salario_base * 0.105;
  if ($salario_base <= 941000) {
    $impuesto_renta = 0;
  } elseif ($salario_base <= 1385000) {
    $impuesto_renta = ($salario_base - 941000) * 0.10;
  } else {
    $impuesto_renta = ((1385000 - 941000) * 0.10) + (($salario_base - 1385000) * 0.15);
  }
  $total_retenciones = $seguro_social + $impuesto_renta;
  return [
    'salario_base' => $salario_base,
    'seguro_social' => $seguro_social,
    'impuesto_renta' => $impuesto_renta,
    'total_retenciones' => $total_retenciones
  ];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $id_usuario = $_POST["id_usuario"];

  // Obtener salario base
  $query_salario = "SELECT salario_base FROM Planilla WHERE id_usuario = ?";
  $stmt_salario = $conn->prepare($query_salario);
  $stmt_salario->bind_param("i", $id_usuario);
  $stmt_salario->execute();
  $result_salario = $stmt_salario->get_result();

  if ($result_salario->num_rows > 0) {
    $row = $result_salario->fetch_assoc();
    $salario_base = $row['salario_base'];

    $retenciones_mensuales = calcularRetenciones($salario_base);
    $retenciones_quincenales = [
      'salario_base' => $retenciones_mensuales['salario_base'] / 2,
      'seguro_social' => $retenciones_mensuales['seguro_social'] / 2,
      'impuesto_renta' => $retenciones_mensuales['impuesto_renta'] / 2,
      'total_retenciones' => $retenciones_mensuales['total_retenciones'] / 2
    ];
    $salario_neto_quincenal = ($retenciones_mensuales['salario_base'] / 2) - $retenciones_quincenales['total_retenciones'];

    $query_check = "SELECT id_planilla FROM Planilla WHERE id_usuario = ?";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bind_param("i", $id_usuario);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows == 0) {
      $query = "INSERT INTO Planilla (id_usuario, salario_base, fechacreacion) VALUES (?, ?, CURDATE())";
      $stmt = $conn->prepare($query);
      $stmt->bind_param("id", $id_usuario, $retenciones_quincenales['salario_base']);
      $stmt->execute();
      $stmt->close();
    }

    $query_deduccion = "INSERT INTO deducciones 
      (id_usuario, razon, deudor, concepto, lugar, monto_quincenal, monto_mensual, aportes, saldo_pendiente, deuda_total, saldo_pendiente_dolares, fechacreacion, usuariocreacion, fechamodificacion, usuariomodificacion)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), 'admin', CURDATE(), 'admin')";
    $stmt_deduccion = $conn->prepare($query_deduccion);

    $deudor = "Trabajador";
    $concepto = "Retenciones Quincenales de Ley";
    $lugar = "Entidades Gubernamentales de Costa Rica";

    // Seguro Social
    $razon = "Seguro Social";
    $aporte_quincenal = $retenciones_mensuales['seguro_social'] / 2;
    $monto_quincenal = $aporte_quincenal;
    $monto_mensual = $aporte_quincenal * 2;
    $saldo_pendiente = 0;
    $deuda_total = 0;
    $saldo_pendiente_dolares = 0;
    $stmt_deduccion->bind_param("issssdddddd", $id_usuario, $razon, $deudor, $concepto, $lugar, $monto_quincenal, $monto_mensual, $aporte_quincenal, $saldo_pendiente, $deuda_total, $saldo_pendiente_dolares);
    $stmt_deduccion->execute();

    // Impuesto sobre la Renta
    $razon = "Impuesto sobre la Renta";
    $aporte_quincenal = $retenciones_mensuales['impuesto_renta'] / 2;
    $monto_quincenal = $aporte_quincenal;
    $monto_mensual = $aporte_quincenal * 2;
    $saldo_pendiente = 0;
    $deuda_total = 0;
    $saldo_pendiente_dolares = 0;
    $stmt_deduccion->bind_param("issssdddddd", $id_usuario, $razon, $deudor, $concepto, $lugar, $monto_quincenal, $monto_mensual, $aporte_quincenal, $saldo_pendiente, $deuda_total, $saldo_pendiente_dolares);
    $stmt_deduccion->execute();

    $stmt_deduccion->close();
    $stmt_check->close();
    $stmt_salario->close();

    // Guardar mensaje en sesión para mostrar luego
    $_SESSION['mensaje_exito'] = "Retenciones quincenales aplicadas correctamente.<br>Salario Neto quincenal actualizado: ₡" . number_format($salario_neto_quincenal, 2);

    // Opcional: hacer refresh vía JS para evitar reenvío POST, o solo mostrar mensaje
    // Aquí NO se hace header Location ni exit
  } else {
    $_SESSION['mensaje_error'] = "No se encontró salario base para el usuario seleccionado.";
  }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <title>Aplicar Deducciones</title>
  <!-- Aquí tus links CSS -->
</head>

<body>
  <section id="container">
    <section id="main-content">
      <section class="wrapper site-min-height">

        <div class="container-fluid">
          <?php 
          if (!empty($mensaje)) {
            echo $mensaje;
          } elseif (isset($_SESSION['mensaje_exito'])) {
            echo "<div class='alert alert-success text-center'>" . $_SESSION['mensaje_exito'] . "</div>";
            unset($_SESSION['mensaje_exito']);
          } elseif (isset($_SESSION['mensaje_error'])) {
            echo "<div class='alert alert-danger text-center'>" . $_SESSION['mensaje_error'] . "</div>";
            unset($_SESSION['mensaje_error']);
          }
          ?>

          <!-- Aquí tu formulario -->
          <form action="" method="POST" class="form-horizontal">
            <!-- tu select de usuario y campos -->

            <!-- resto de tu formulario -->
          </form>
        </div>
      </section>
    </section>
  </section>

  <script>
    // Opcional:evitar que al refrescar se reenvíe el formulario,
    if (window.history.replaceState) {
      window.history.replaceState(null, null, window.location.href);
    }
  </script>
</body>

</html>
