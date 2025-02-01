<?php


// Conexión a la base de datos
require 'conexion.php';
session_start();

//validación para que solo el administrador master acceda
// if (!isset($_SESSION['id_rol']) || (int)$_SESSION['id_rol'] !== 3) {
//     header("Location: index.php");
//     exit;
// }

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capturamos los datos del formulario
    $id_usuario = $_POST["id_usuario"];
    $nuevo_salario_base = $_POST["nuevo_salario_base"];
    $ajuste = $_POST["ajuste"];  // Ajuste salarial (puede ser positivo o negativo)
    
    // Calculamos el nuevo salario neto: nuevo salario base + ajuste
    $nuevo_salario_neto = $nuevo_salario_base + $ajuste;
    
    // Verificar que el empleado esté activo:
    // Se asume que un empleado está activo si existe en Usuario y no tiene un registro en eliminacion_usuario.
    $query_check = "SELECT COUNT(*) AS count 
                    FROM Usuario u 
                    LEFT JOIN eliminacion_usuario e ON u.id_usuario = e.id_usuario 
                    WHERE u.id_usuario = ? AND e.id_usuario IS NULL";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bind_param("i", $id_usuario);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row_check = $result_check->fetch_assoc();
    $count = $row_check['count'];
    $stmt_check->close();
    
    if ($count == 0) {
        $mensaje = "El usuario con ID $id_usuario no existe o no está activo.";
    } else {
        // Recuperar el salario base actual para guardar en el historial.
        // Si el registro ya existe en Planilla, obtenemos el salario actual.
        $query_select = "SELECT salario_base FROM Planilla WHERE id_usuario = ?";
        $stmt_select = $conn->prepare($query_select);
        $stmt_select->bind_param("i", $id_usuario);
        $stmt_select->execute();
        $stmt_select->store_result();
        $old_salario_base = null;
        if ($stmt_select->num_rows > 0) {
            $stmt_select->bind_result($old_salario_base);
            $stmt_select->fetch();
        }
        $stmt_select->close();
        
        // Actualizar o insertar en la tabla Planilla
        $query_planilla = "";
        if ($old_salario_base !== null) {
            // Registro existente: actualizamos Planilla
            $query_planilla = "UPDATE Planilla SET salario_base = ?, salario_neto = ? WHERE id_usuario = ?";
        } else {
            // Registro inexistente: insertamos un nuevo registro (asumiendo que otros campos pueden ser nulos o tener valor por defecto)
            $query_planilla = "INSERT INTO Planilla (id_usuario, salario_base, retenciones, salario_neto, fechacreacion) VALUES (?, ?, 0, ?, CURDATE())";
        }
        $stmt_planilla = $conn->prepare($query_planilla);
        if (!$stmt_planilla) {
            $mensaje = "Error en la preparación de la consulta: " . $conn->error;
        } else {
            if ($old_salario_base !== null) {
                $stmt_planilla->bind_param("ddi", $nuevo_salario_base, $nuevo_salario_neto, $id_usuario);
            } else {
                $stmt_planilla->bind_param("idd", $id_usuario, $nuevo_salario_base, $nuevo_salario_neto);
            }
            if ($stmt_planilla->execute()) {
                // Insertar registro en el Historial_Salarios para tener registro de los cambios.
                // Se guarda el salario anterior; si no había registro previo, lo dejamos como 0.
                $salario_anterior = ($old_salario_base !== null) ? $old_salario_base : 0;
                $fecha_cambio = date("Y-m-d");
                $usuariocreacion = "admin"; // Ajusta según la sesión o usuario logueado

                $query_historial = "INSERT INTO Historial_Salarios 
                    (id_usuario, salario_anterior, nuevo_salario_base, ajuste, nuevo_salario_neto, fecha_cambio, usuariocreacion)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt_historial = $conn->prepare($query_historial);
                if ($stmt_historial) {
                    $stmt_historial->bind_param("iddddss", $id_usuario, $salario_anterior, $nuevo_salario_base, $ajuste, $nuevo_salario_neto, $fecha_cambio, $usuariocreacion);
                    if ($stmt_historial->execute()) {
                        $mensaje = "✅ Salarios actualizados correctamente para el empleado con ID: $id_usuario.<br>" .
                                   "Nuevo Salario Base: ₡" . number_format($nuevo_salario_base, 2) . "<br>" .
                                   "Nuevo Salario Neto: ₡" . number_format($nuevo_salario_neto, 2) . "<br>" .
                                   "Historial guardado.";
                    } else {
                        $mensaje = "Salario actualizado, pero error al guardar el historial: " . $stmt_historial->error;
                    }
                    $stmt_historial->close();
                } else {
                    $mensaje = "Salario actualizado, pero error en la preparación del historial: " . $conn->error;
                }
            } else {
                $mensaje = "Error al actualizar salarios: " . $stmt_planilla->error;
            }
            $stmt_planilla->close();
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Actualizar Salarios</title>
  <link href="assets/css/bootstrap.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="container">
  <h2 class="text-center mt-4">Actualizar Salarios Base y Ajustes Salariales</h2>
  <form action="" method="POST" class="mt-4">
    <div class="form-group">
      <label for="id_usuario">ID del Empleado:</label>
      <input type="text" id="id_usuario" name="id_usuario" class="form-control" placeholder="Ingrese el ID del empleado" required>
    </div>
    <div class="form-group">
      <label for="nuevo_salario_base">Nuevo Salario Base:</label>
      <input type="number" step="0.01" id="nuevo_salario_base" name="nuevo_salario_base" class="form-control" placeholder="Ingrese el nuevo salario base" required>
    </div>
    <div class="form-group">
      <label for="ajuste">Ajuste Salarial:</label>
      <input type="number" step="0.01" id="ajuste" name="ajuste" class="form-control" placeholder="Ingrese el ajuste (positivo o negativo)" required>
    </div>
    <div class="form-group text-center mt-3">
      <button type="submit" class="btn btn-primary">Actualizar Salarios</button>
      <a href="index.php" class="btn btn-secondary">Volver al Inicio</a>
    </div>
  </form>
  <?php if (!empty($mensaje)): ?>
    <div class="alert alert-info mt-3"><?php echo $mensaje; ?></div>
  <?php endif; ?>
</div>
<script src="assets/js/jquery.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
</body>
</html>