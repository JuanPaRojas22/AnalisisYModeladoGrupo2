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
    $tipo_deduccion = $_POST["tipo_deduccion"];
    $monto = $_POST["monto"];
    $descripcion = $_POST["descripcion"];

    //Insertar deducción

    $smt = $conn->prepare("INSERT INTO deducciones(id_usuario, tipo_deduccion, monto, descripcion) VALUES (?,?,?,?)");
    $smt->bind_param("isds", $id_usuario, $tipo_deduccion, $monto, $descripcion);

    if ($smt->execute()) {
        // 2. Obtener salario base del usuario
        $stmt_salario = $conn->prepare("SELECT salario_base FROM Planilla WHERE id_usuario = ?");
        $stmt_salario->bind_param("i", $id_usuario);
        $stmt_salario->execute();
        $result_salario = $stmt_salario->get_result();
        $salario_base = $result_salario->fetch_assoc()['salario_base'];
        $salario_quincenal = $salario_base / 2;

        //Sumar todas las deducciones quincenales
        $stmt_ded = $conn->prepare("SELECT SUM(monto) AS Total_Deduciones FROM deducciones WHERE id_usuario = ?");
        $stmt_ded->bind_param("i", $id_usuario);
        $stmt_ded->execute();
        $total_deducciones = $stmt_ded->get_result()->fetch_assoc()['Total_Deduciones'] ?? 0;

        //Calcular salario neto
        $salario_neto_quincenal = $salario_quincenal - $total_deducciones;

        //Actualizar o insertar planilla
        $check_planilla = $conn->prepare("SELECT * FROM planilla WHERE id_usuario = ?");
        $check_planilla->bind_param("i", $id_usuario);
        $check_planilla->execute();
        $result_check = $check_planilla->get_result();

        if ($result_check->num_rows > 0) {
            //UPDATE
            $stmt_upd = $conn->prepare("UPDATE planilla SET salario_neto_quincenal = ?, retenciones_quincenales = ? WHERE id_usuario = ?");
            $stmt_upd->bind_param("ddi", $salario_neto_quincenal, $total_deducciones, $id_usuario);
            $stmt_upd->execute();
        } else {
            //INSERT
            $stmt_ins = $conn->prepare("INSERT INTO planilla (id_usuario, salario_neto_quincenal, retenciones_quincenales) VALUES (?, ?, ?)");
            $stmt_ins->bind_param("idd", $id_usuario, $salario_neto_quincenal, $total_deducciones);
            $stmt_ins->execute();
        }

        $mensaje = "Deducción registrada y planilla actualizada.";
    } else {
        $mensaje = "Error al registrar la deducción.";

    }
}
// Obtener lista de empleados
$result_empleados = $conn->query("SELECT id_usuario, nombre FROM usuario");
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
                <h3 class="text-center mb-4">Agregar Deducción Manual</h3>
                <form method="POST">
                    <div class="form-group">
                        <label for="id_usuario">Empleado</label>
                        <select name="id_usuario" id="id_usuario" class="form-control" required>
                            <option value="">Seleccione un empleado</option>
                            <?php while ($row = $result_empleados->fetch_assoc()): ?>
                                <option value="<?= $row['id_usuario'] ?>"><?= $row['nombre'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tipo_deduccion">Tipo de Deducción</label>
                        <input type="text" name="tipo_deduccion" id="tipo_deduccion" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="monto">Monto</label>
                        <input type="number" name="monto" id="monto" class="form-control" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción (opcional)</label>
                        <textarea name="descripcion" id="descripcion" class="form-control"></textarea>
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
            max-width: 75%;
            /* o usa 1200px si preferís fijo */
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
            font-size: 18px;
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