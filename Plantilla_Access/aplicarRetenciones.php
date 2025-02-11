<?php
// Conexión a la base de datos
require 'conexion.php';
session_start();

function calcularRetenciones($salario_base) {
    // Cálculo del seguro social (10.5% del salario base)
    $seguro_social = $salario_base * 0.105;
    
    // Cálculo del impuesto sobre la renta según los tramos de salario
    if ($salario_base <= 941000) {
        $impuesto_renta = 0; // No se aplica impuesto si el salario es menor o igual a 941,000 CRC
    } elseif ($salario_base <= 1385000) {
        $impuesto_renta = ($salario_base - 941000) * 0.10; //10% sobre el excedente de 941,000 CRC
    } else {
        // 10% sobre el primer tramo y 15% sobre el excedente
        $impuesto_renta = ((1385000 - 941000) * 0.10) + (($salario_base - 1385000) * 0.15);
    }

    // Retenciones totales (Seguro Social + Impuesto sobre la Renta)
    $total_retenciones = $seguro_social + $impuesto_renta;
    
    return [
        'salario_base'      => $salario_base,
        'seguro_social'     => $seguro_social,
        'impuesto_renta'    => $impuesto_renta,
        'total_retenciones' => $total_retenciones
    ];
}

$resultado = null;
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario   = $_POST["id_usuario"];
    $salario_base = $_POST["salario_base"];

    // Calcular retenciones y el salario neto
    $retenciones = calcularRetenciones($salario_base);
    $salario_neto = $salario_base - $retenciones['total_retenciones'];

    // Verificar si existe un registro en la tabla Planilla para el usuario dado
    $query_check = "SELECT id_planilla FROM Planilla WHERE id_usuario = ?";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bind_param("i", $id_usuario);
    $stmt_check->execute();
    $stmt_check->store_result();
    $num_rows = $stmt_check->num_rows;
    $stmt_check->close();

    // Preparar la consulta para insertar o actualizar la tabla Planilla
    if ($num_rows > 0) {
        // Si existe, actualizamos el registro
        $query = "UPDATE Planilla SET retenciones = ?, salario_neto = ? WHERE id_usuario = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ddi", $retenciones['total_retenciones'], $salario_neto, $id_usuario);
    } else {
        // Si no existe, insertamos un nuevo registro
        $query = "INSERT INTO Planilla (id_usuario, salario_base, retenciones, salario_neto, fechacreacion) VALUES (?, ?, ?, ?, CURDATE())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iddd", $id_usuario, $salario_base, $retenciones['total_retenciones'], $salario_neto);
    }

    // Ejecutar la consulta Planilla
    if ($stmt->execute()) {
        // Insertar las deducciones en la tabla deducciones
        // Insertamos deducción por Seguro Social
        $query_deduccion = "INSERT INTO deducciones (id_usuario, razon, aportes) VALUES (?, 'Seguro Social', ?)";
        $stmt_deduccion = $conn->prepare($query_deduccion);
        $stmt_deduccion->bind_param("id", $id_usuario, $retenciones['seguro_social']);
        $stmt_deduccion->execute();

        // Insertamos deducción por Impuesto sobre la Renta
        $query_deduccion = "INSERT INTO deducciones (id_usuario, razon, aportes) VALUES (?, 'Impuesto sobre la Renta', ?)";
        $stmt_deduccion = $conn->prepare($query_deduccion);
        $stmt_deduccion->bind_param("id", $id_usuario, $retenciones['impuesto_renta']);
        $stmt_deduccion->execute();

        // Si todo fue exitoso
        $resultado = $retenciones;
        $mensaje = "Retenciones aplicadas correctamente. <br>" .
                   "Salario Neto actualizado: ₡" . number_format($salario_neto, 2);
    } else {
        $mensaje = "Error al aplicar retenciones: " . $stmt->error;
    }

    // Cerrar sentencias y conexión
    $stmt->close();
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
      <input type="text" id="id_usuario" name="id_usuario" class="form-control" placeholder="Ingrese el ID de usuario" required>
    </div>
    <div class="form-group">
      <label for="salario_base" class="control-label">Salario Base:</label>
      <input type="number" id="salario_base" name="salario_base" class="form-control" placeholder="Ingrese el salario base" required>
    </div>
    <div class="form-group text-center">
      <button type="submit" class="btn btn-primary">Calcular Retenciones</button>
    </div>
  </form>

  <?php if ($resultado !== null): ?>
    <div class="alert alert-info mt-3">
      <h4>Detalles de Retenciones</h4>
      <p><strong>Salario Base:</strong> ₡<?php echo number_format($resultado['salario_base'], 2); ?></p>
      <p><strong>Seguro Social:</strong> ₡<?php echo number_format($resultado['seguro_social'], 2); ?></p>
      <p><strong>Impuesto sobre la Renta:</strong> ₡<?php echo number_format($resultado['impuesto_renta'], 2); ?></p>
      <p><strong>Total Retenciones:</strong> ₡<?php echo number_format($resultado['total_retenciones'], 2); ?></p>
      <p><strong>Salario Neto:</strong> ₡<?php echo number_format($salario_neto, 2); ?></p>
    </div>
    <div class="alert alert-success"><?php echo $mensaje; ?></div>
  <?php endif; ?>

  <div class="form-group text-center mt-3">
    <a href="index.php" class="btn btn-secondary">Volver al Inicio</a>
  </div>
</div>

<script src="assets/js/jquery.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
</body>
</html>
