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
    
    // Calculamos el nuevo salario neto
    $nuevo_salario_neto = $nuevo_salario_base + $ajuste;
    
    // Verificar que el empleado esté activo:
    // Se asume que un usuario es "activo" si existe en la tabla Usuario y NO tiene un registro en eliminacion_usuario.
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
        // Actualizamos la tabla Planilla con el nuevo salario base y el nuevo salario neto
        $query = "UPDATE Planilla SET salario_base = ?, salario_neto = ? WHERE id_usuario = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            $mensaje = "Error en la preparación de la consulta: " . $conn->error;
        } else {
            $stmt->bind_param("ddi", $nuevo_salario_base, $nuevo_salario_neto, $id_usuario);
            if ($stmt->execute()) {
                $mensaje = "Salarios actualizados correctamente para el empleado con ID: $id_usuario.<br>" .
                           "Nuevo Salario Base: ₡" . number_format($nuevo_salario_base, 2) . "<br>" .
                           "Nuevo Salario Neto: ₡" . number_format($nuevo_salario_neto, 2);
            } else {
                $mensaje = "Error al actualizar salarios: " . $stmt->error;
            }
            $stmt->close();
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


