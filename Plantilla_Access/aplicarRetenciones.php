<?php
// Conexión a la base de datos
require 'conexion.php';
session_start();

function calcularRetenciones($salario_base)
{
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

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $id_usuario = $_POST["id_usuario"];
  $salario_base = $_POST["salario_base"];

  $retenciones = calcularRetenciones($salario_base);
  $salario_neto = $salario_base - $retenciones['total_retenciones'];

  // Verificar si el usuario ya tiene una planilla
  $query_check = "SELECT id_planilla FROM Planilla WHERE id_usuario = ?";
  $stmt_check = $conn->prepare($query_check);
  $stmt_check->bind_param("i", $id_usuario);
  $stmt_check->execute();
  $stmt_check->store_result();

  if ($stmt_check->num_rows > 0) {
    // Si existe, actualizar planilla
    $stmt_check->bind_result($id_planilla);
    $stmt_check->fetch();
    $query = "UPDATE Planilla SET salario_base = ?, retenciones = ?, salario_neto = ?, fechamodificacion = CURDATE() WHERE id_planilla = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("dddi", $salario_base, $retenciones['total_retenciones'], $salario_neto, $id_planilla);
  } else {
    // Si no existe, insertar nueva planilla
    $query = "INSERT INTO Planilla (id_usuario, salario_base, retenciones, salario_neto, fechacreacion) VALUES (?, ?, ?, ?, CURDATE())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iddd", $id_usuario, $salario_base, $retenciones['total_retenciones'], $salario_neto);
    $stmt->execute();
    $id_planilla = $stmt->insert_id;
  }
  $stmt->execute();

  // Insertar deducciones en la tabla deducciones
  $query_deduccion = "INSERT INTO deducciones 
                        (id_usuario, razon, monto_mensual, monto_quincenal, aportes, saldo_pendiente, deuda_total) 
                        VALUES (?,?,?, ?, ?, ?, ?)";
  $stmt_deduccion = $conn->prepare($query_deduccion);
  $stmt_deduccion->bind_param("isddddd", $id_usuario, $razon, $monto_mensual, $monto_quincenal, $aporte, $saldo_pendiente, $deuda_total);

  // Insertar Seguro Social
  $razon = "Seguro Social";
  $aporte = $retenciones['seguro_social'];
  $monto_mensual = $aporte;
  $monto_quincenal = $aporte / 2;
  $deuda_total = 0;  // No hay deuda
  $saldo_pendiente = 0; // No hay saldo pendiente
  $stmt_deduccion->execute();

  // Insertar Impuesto sobre la Renta
  $razon = "Impuesto sobre la Renta";
  $aporte = $retenciones['impuesto_renta'];
  $monto_mensual = $aporte;
  $monto_quincenal = $aporte / 2;
  $deuda_total = 0;
  $saldo_pendiente = 0;
  $stmt_deduccion->execute();

  $mensaje = "Retenciones aplicadas correctamente. <br> 
               Salario Neto actualizado: ₡" . number_format($salario_neto, 2);

  // Cerrar sentencias y conexión
  $stmt_check->close();
  $stmt->close();
  $stmt_deduccion->close();
  $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Calcular Retenciones</title>
  <link href="assets/css/bootstrap.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>
  <div class="container">
    <h2 class="text-center mt-4">Calcular Retenciones</h2>

    <form action="" method="POST" class="form-horizontal">
      <div class="form-group">
        <label for="id_usuario" class="control-label">ID Usuario:</label>
        <input type="text" id="id_usuario" name="id_usuario" class="form-control" placeholder="Ingrese el ID de usuario"
          required>
      </div>

      <div class="form-group">
        <label for="salario_base" class="control-label">Salario Base:</label>
        <input type="number" id="salario_base" name="salario_base" class="form-control"
          placeholder="Ingrese el salario base" required>
      </div>

      <div class="form-group text-center">
        <button type="submit" class="btn btn-primary">Calcular Retenciones</button>
      </div>
    </form>

    <?php if (isset($retenciones) && isset($salario_neto)): ?>
      <div class="alert alert-info mt-3">
        <h4>Detalles de Retenciones</h4>
        <p><strong>Salario Base:</strong> ₡<?php echo number_format($retenciones['salario_base'], 2); ?></p>
        <p><strong>Seguro Social:</strong> ₡<?php echo number_format($retenciones['seguro_social'], 2); ?></p>
        <p><strong>Impuesto sobre la Renta:</strong> ₡<?php echo number_format($retenciones['impuesto_renta'], 2); ?></p>
        <p><strong>Total Retenciones:</strong> ₡<?php echo number_format($retenciones['total_retenciones'], 2); ?></p>
        <p><strong>Salario Neto:</strong> ₡<?php echo number_format($salario_neto, 2); ?></p>
      </div>
    <?php endif; ?>

    <?php if (isset($mensaje)): ?>
      <div class="alert alert-success mt-3"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <div class="form-group text-center mt-3">
      <a href="index.php" class="btn btn-secondary">Volver al Inicio</a>
    </div>
  </div>

  <script src="assets/js/jquery.js"></script>
  <script src="assets/js/bootstrap.min.js"></script>
</body>

</html>