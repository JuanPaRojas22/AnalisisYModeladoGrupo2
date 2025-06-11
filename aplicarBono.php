<?php
require 'conexion.php';
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
require 'template.php';
?>


<!DOCTYPE html>
<html lang="en">

<head>


    <title>Aplicar Bonos</title>




</head>

<body>

    <section id="container">

        <style>
            .form-container {
                background-color: white;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                /* Sombra para el efecto de profundidad */
                width: 60%;
                /* Ajusta el ancho del contenedor */
                margin: 20px auto;
                /* Centra el contenedor */
                color: black;
                font-weight: bold;
            }

            .container {
                width: 80%;
                /* Ajusta el ancho del contenedor */
                margin: 0 auto;
                /* Centra el contenedor */
            }

            h2 {
                font-size: 28px;
                color: black;
                font-weight: bold;
            }

            .form-group label {
                font-weight: bold;
            }

            .form-control {
                border-radius: 5px;
                padding: 10px;
                font-size: 14px;
                margin-bottom: 10px;
                border: 1px solid #ddd;
                height: 38px;
                text-align: center;
            }

            .btn {
                width: 150px;
                padding: 10px;
            }

            .btn-success {
                background-color: #147964;
                border-color: #147964;
            }

            .btn-secondary {
                background-color: #6c757d;
                border-color: #6c757d;
            }

            .btn:hover {
                opacity: 0.9;
            }
        </style>
        <section id="main-content">
            <section class="wrapper site-min-height">

                <?php
                $mensaje = "";

                // Obtener lista de usuarios con su salario
                $query_usuarios = "SELECT u.id_usuario, u.nombre, p.salario_base 
                                   FROM Usuario u 
                                   LEFT JOIN Planilla p ON u.id_usuario = p.id_usuario";
                $result_usuarios = $conn->query($query_usuarios);

                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    $id_usuario = $_POST["id_usuario"];
                    $razon = $_POST["razon"];
                    $monto_total = $_POST["monto_total"];
                    $fecha_aplicacion = date("Y-m-d");
                    $usuariocreacion = "admin";
                    $fechacreacion = date("Y-m-d");

                    // Verificar si el usuario tiene un salario registrado en Planilla
                    $query_verificar = "SELECT salario_base FROM Planilla WHERE id_usuario = ?";
                    $stmt_verificar = $conn->prepare($query_verificar);
                    $stmt_verificar->bind_param("i", $id_usuario);
                    $stmt_verificar->execute();
                    $result_verificar = $stmt_verificar->get_result();

                    if ($result_verificar->num_rows == 0) {
                        $mensaje = "Error: El usuario no tiene un salario registrado en Planilla.";
                    } else {
                        // Insertar el bono en la tabla Bonos
                        $query_bono = "INSERT INTO Bonos (id_usuario, razon, monto_total, fecha_aplicacion, fechacreacion, usuariocreacion)
                                       VALUES (?, ?, ?, ?, ?, ?)";
                        $stmt_bono = $conn->prepare($query_bono);
                        $stmt_bono->bind_param("isssss", $id_usuario, $razon, $monto_total, $fecha_aplicacion, $fechacreacion, $usuariocreacion);

                        if ($stmt_bono->execute()) {
                            // Actualizar el salario en la tabla Planilla 
                            $query_salario = "UPDATE Planilla 
                                              SET salario_base = salario_base + ?,
                                              salario_neto = salario_neto + ?  
                                              WHERE id_usuario = ?";
                            $stmt_salario = $conn->prepare($query_salario);

                            if (!$stmt_salario) {
                                die("Error en la consulta: " . $conn->error);
                            }

                            $stmt_salario->bind_param("ddi", $monto_total,$monto_total, $id_usuario);

                            if ($stmt_salario->execute()) {
                                $mensaje = "Bono aplicado correctamente. El salario se ha actualizado.";
                            } else {
                                $mensaje = "Error al actualizar el salario en Planilla: " . $stmt_salario->error;
                            }

                            $stmt_salario->close();
                        } else {
                            $mensaje = "Error al registrar el bono.";
                        }

                        $stmt_bono->close();
                    }

                    $stmt_verificar->close();
                    $conn->close();
                }
                ?>

                <script>
                    function actualizarSalario() {
                        var select = document.getElementById("id_usuario");
                        var salario = select.options[select.selectedIndex].getAttribute("data-salario");
                        document.getElementById("salario_actual").value = salario ? salario : "No registrado";
                    }
                </script>


                <body>
                    <div class="container mt-5 form-container">
                        <h2 class="text-center mb-4">Aplicar Bono Salarial</h2>
                        <form action="" method="POST">
                            <div class="form-group">
                                <label for="id_usuario">Seleccione un Usuario:</label>
                                <select name="id_usuario" id="id_usuario" class="form-control" required
                                    onchange="actualizarSalario()">
                                    <option value="">Seleccione un usuario</option>
                                    <?php while ($row = $result_usuarios->fetch_assoc()): ?>
                                        <option value="<?= $row['id_usuario']; ?>"
                                            data-salario="<?= $row['salario_base']; ?>">
                                            <?= $row['nombre']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="salario_actual">Salario Actual:</label>
                                <input type="text" id="salario_actual" class="form-control" disabled>
                            </div>

                            <div class="form-group">
                                <label for="razon">Razón del Bono:</label>
                                <input type="text" name="razon" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="monto_total">Monto del Bono:</label>
                                <input type="number" step="0.01" name="monto_total" class="form-control" required>
                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" class="btn"
                                    style="background-color: #147964; border-color: #147964; color: white;">Aplicar
                                    Bono</button>
                                <a href="VerPlanilla.php" class="btn"
                                    style="background-color: #0B4F6C; border-color: #0B4F6C; color: white;">Volver</a>
                            </div>
                        </form>

                        <?php if (!empty($mensaje)): ?>
                            <div class="alert alert-info mt-3"><?= $mensaje; ?></div>
                        <?php endif; ?>


                    <script src="assets/js/jquery.js"></script>
                    <script src="assets/js/bootstrap.min.js"></script>
                </body>

</html>