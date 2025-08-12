<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
require 'conexion.php';
require 'template.php';
$conn = obtenerConexion();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario = $_POST["id_usuario"];
    $tipo_deduccion = $_POST["razon"];
    $monto_mensual = $_POST["monto_mensual"];
    $monto_quincenal = $monto_mensual / 2;
    $descripcion = $_POST["concepto"];

    //Insertar deducción sin preocuparse por actualizar planilla manualmente
    $deudor = "Trabajador";
    $lugar = "Entidades Gubernamentales de Costa Rica";

    $smt = $conn->prepare("INSERT INTO deducciones(id_usuario, aportes, deudor, razon, lugar, monto_quincenal, monto_mensual, concepto) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $smt->bind_param("idsssdds", $id_usuario, $monto_quincenal, $deudor, $tipo_deduccion, $lugar, $monto_quincenal, $monto_mensual, $descripcion);

    if ($smt->execute()) {
        $mensaje = "Deducción registrada correctamente. Salario neto se actualizó automáticamente.";
    } else {
        $mensaje = "Error al registrar la deducción.";
    }
}
// Obtener lista de empleados (igual)
$result_empleados = $conn->query("SELECT DISTINCT id_usuario, nombre, apellido FROM usuario");
?>

<!DOCTYPE html>
<html lang="es">


<head>
    <meta charset="UTF-8">
    <title>Agregar Deducción Manual</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Custom styles for this template -->
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/style-responsive.css" rel="stylesheet">

</head>

<body>
    <div class="contenedor-deduccion">
        <div>
            <div class="card-body">
                <h3 class="text-center mb-4">Agregar Deducción Extra</h3>
                <form method="POST">
                    <div class="form-group">
                        <label for="id_usuario">Empleado</label>
                        <select name="id_usuario" id="id_usuario" class="form-control" required>
                            <option value="">Seleccione un empleado</option>
                            <?php while ($row = $result_empleados->fetch_assoc()): ?>
                                <option value="<?= $row['id_usuario'] ?>"><?= $row['nombre'] . ' ' . $row['apellido'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="razon">Tipo de Deducción</label>
                        <input type="text" name="razon" id="razon" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="monto_mensual">monto Mensual</label>
                        <input type="number" name="monto_mensual" id="monto_mensual" class="form-control" step="0.01"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="concepto">Descripción (opcional)</label>
                        <textarea name="concepto" id="concepto" class="form-control"></textarea>
                    </div>

                    <div class="text-center mt-3">
                        <button class="btn btn-success" type="submit">Agregar Deducción</button>
                        <a href="VerPlanilla.php" class="btn btn-secondary">Volver</a>
                    </div>

                </form>
                <?php if (!empty($mensaje)): ?>
                    <div class="alert alert-info mt-3 text-center"><?= $mensaje ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <style>
        /* Container Styles */
        .contenedor-deduccion {
            max-height: 75%;
            max-width: 50%;
            margin: 50px auto;
            padding: 30px;
            background-color: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            color: black;
            text-align: center;
            font-weight: bold;
            font-size: 16px;
        }


        /* Card Body Styles */
        .card-body {
            padding: 20px;
        }

        /* Heading Style */
        h3 {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        option {
            height: 10%;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
        }


        select.form-control,
        input.form-control,
        textarea.form-control {
            font-size: 14px;
            padding: 12px;
            height: auto;
        }

        /* Button Styles */
        button[type="submit"],
        a.btn {
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            width: auto;
        }

        button[type="submit"] {
            background-color: #147964;
            /* Green */
            color: white;
            border: none;
        }

        button[type="submit"]:hover {
            background-color: #147964;
        }

        a.btn {
            background-color: #0B4F6C;
            /* Blue */
            color: white;
        }

        a.btn:hover {
            background-color: #0B4F6C;
        }
    </style>
</body>

</html>