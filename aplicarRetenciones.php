<?php
require 'conexion.php';
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  header("Location: login.php");
  exit;
}

require 'template.php';


if (isset($_SESSION['mensaje_exito'])) {
  $mensaje = "<div class='alert alert-success text-center'>" . $_SESSION['mensaje_exito'] . "</div>";
  unset($_SESSION['mensaje_exito']);
} elseif (isset($_SESSION['mensaje_error'])) {
  $mensaje = "<div class='alert alert-danger text-center'>" . $_SESSION['mensaje_error'] . "</div>";
  unset($_SESSION['mensaje_error']);
}
?>


<!DOCTYPE html>
<html lang="en">



<title>Aplicar Deducciones</title>






<body>

  <section id="container">
    <section id="main-content">
      <section class="wrapper site-min-height">

        <?php
        // Consulta para obtener las planillas existentes con información del usuario
        $query_planilla = "SELECT p.id_planilla, p.id_usuario, u.nombre, u.apellido, p.salario_base 
FROM Planilla p 
JOIN Usuario u ON p.id_usuario = u.id_usuario";
        $result_planilla = $conn->query($query_planilla);

        // Función para calcular retenciones mensuales
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

        $mensaje = "";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
          // Datos recibidos del formulario
          $id_usuario = $_POST["id_usuario"];

          // Obtener salario base actual para el usuario
          $query_salario = "SELECT salario_base FROM Planilla WHERE id_usuario = ?";
          $stmt_salario = $conn->prepare($query_salario);
          $stmt_salario->bind_param("i", $id_usuario);
          $stmt_salario->execute();
          $result_salario = $stmt_salario->get_result();

          if ($result_salario->num_rows > 0) {
            $row = $result_salario->fetch_assoc();
            $salario_base = $row['salario_base'];

            // Calcular retenciones mensuales
            $retenciones_mensuales = calcularRetenciones($salario_base);

            // Calcular valores quincenales (dividiendo entre 2)
            $retenciones_quincenales = [
              'salario_base' => $retenciones_mensuales['salario_base'] / 2,
              'seguro_social' => $retenciones_mensuales['seguro_social'] / 2,
              'impuesto_renta' => $retenciones_mensuales['impuesto_renta'] / 2,
              'total_retenciones' => $retenciones_mensuales['total_retenciones'] / 2
            ];
            $salario_neto_quincenal = ($retenciones_mensuales['salario_base'] / 2) - $retenciones_quincenales['total_retenciones'];

            // Verificar si existe planilla para el usuario
            $query_check = "SELECT id_planilla FROM Planilla WHERE id_usuario = ?";
            $stmt_check = $conn->prepare($query_check);
            $stmt_check->bind_param("i", $id_usuario);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows == 0) {
              // Insertar solo salario_base si no existe planilla (el trigger actualizará el resto)
              $query = "INSERT INTO Planilla (id_usuario, salario_base, fechacreacion) 
            VALUES (?, ?, CURDATE())";
              $stmt = $conn->prepare($query);
              $stmt->bind_param("id", $id_usuario, $retenciones_quincenales['salario_base']);
              $stmt->execute();
            }



            // Insertar deducciones en la tabla deducciones (igual que antes)
            $query_deduccion = "INSERT INTO deducciones 
    (id_usuario, razon, deudor, concepto, lugar, monto_quincenal, monto_mensual, aportes, saldo_pendiente, deuda_total, saldo_pendiente_dolares, fechacreacion, usuariocreacion, fechamodificacion, usuariomodificacion)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), 'admin', CURDATE(), 'admin')";
            $stmt_deduccion = $conn->prepare($query_deduccion);

            $deudor = "Trabajador";
            $concepto = "Retenciones Quincenales de Ley";
            $lugar = "Entidades Gubernamentales de Costa Rica";

            // Seguro Social (quincenal)
            $razon = "Seguro Social";
            $aporte_quincenal = $retenciones_mensuales['seguro_social'] / 2;
            $monto_quincenal = $aporte_quincenal;
            $monto_mensual = $aporte_quincenal * 2;
            $saldo_pendiente = 0;
            $deuda_total = 0;
            $saldo_pendiente_dolares = 0;
            $stmt_deduccion->bind_param("issssdddddd", $id_usuario, $razon, $deudor, $concepto, $lugar, $monto_quincenal, $monto_mensual, $aporte_quincenal, $saldo_pendiente, $deuda_total, $saldo_pendiente_dolares);
            $stmt_deduccion->execute();

            // Impuesto sobre la Renta (quincenal)
            $razon = "Impuesto sobre la Renta";
            $aporte_quincenal = $retenciones_mensuales['impuesto_renta'] / 2;
            $monto_quincenal = $aporte_quincenal;
            $monto_mensual = $aporte_quincenal * 2;
            $saldo_pendiente = 0;
            $deuda_total = 0;
            $saldo_pendiente_dolares = 0;
            $stmt_deduccion->bind_param("issssdddddd", $id_usuario, $razon, $deudor, $concepto, $lugar, $monto_quincenal, $monto_mensual, $aporte_quincenal, $saldo_pendiente, $deuda_total, $saldo_pendiente_dolares);
            $stmt_deduccion->execute();

            $_SESSION['mensaje_exito'] = "Retenciones quincenales aplicadas correctamente.<br>Salario Neto quincenal actualizado: ₡" . number_format($salario_neto_quincenal, 2);

            $stmt->close();
            $stmt_deduccion->close();
            $stmt_check->close();
            $stmt_salario->close();
            $conn->close();
          } else {
            $mensaje = "No se encontró salario base para el usuario seleccionado.";
          }
        }
        ?>




        <style>
          /* Container Styles */
          .container-fluid {
            min-height: 600px;
            max-width: 1000px;
            /* Limit the container width */
            margin: 50px auto;
            /* Center the container */
            padding: 30px;
            /* Padding inside the container */
            background-color: white;
            /* White background */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            /* Add shadow */
            border-radius: 15px;
            /* Round the corners */
            height: 100%;
            color: black;
          }

          /* Card Body Styles */
          .card-body {
            padding: 20px;
          }

          /* Heading Style */
          h2 {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
          }

          /* Form Group Styles */
          .form-group {
            margin-bottom: 20px;
            /* Add space between form elements */
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

          .btn {
            background-color: #147964;
            /* Blue */
            color: white;
          }

          .btn:hover {
            background-color: #147964;
          }

          .btn-pequeno {
            padding: 4px 6px;
            font-size: 12px;
            line-height: 2;
            text-align: center;
            white-space: normal;
            /* Permite que el texto haga salto de línea */

          }
        </style>

        <body>
          <section id="container">
            <div class="container-fluid">
              <div class="card" style="border-radius: 15px; padding: 30px; box-shadow: 0 4px 10px rgb(255, 255, 255);">
                <div class="card-body">
                  <h2 class="text-center mb-4">Aplicar Deducción Salarial</h2>
                  <?php if (!empty($mensaje)) echo $mensaje; ?>

                  <a href="AgregarDeduccionesextra.php" class="btn">Agregar Deducción Extra</a>
                  <form action="" method="POST" class="form-horizontal">
                    <!-- Select Employee -->
                    <div class="form-group">
                      <label for="id_usuario">Seleccione un Usuario:</label>
                      <select id="id_usuario" name="id_usuario" class="form-control" required
                        onchange="actualizarSalario()">
                        <option value="">Seleccione un usuario</option>
                        <?php
                        if ($result_planilla->num_rows > 0) {
                          while ($row = $result_planilla->fetch_assoc()) {
                            echo '<option value="' . $row["id_usuario"] . '" data-salario="' . $row["salario_base"] . '">' . $row["nombre"] . ' ' . $row["apellido"] . '</option>';
                          }
                        }
                        ?>
                      </select>
                    </div>

                    <!-- Salary Field -->
                    <div class="form-group">
                      <label for="salario_base">Salario Actual:</label>
                      <input type="text" id="salario_actual" name="salario_actual" class="form-control" readonly>
                      <input type="hidden" id="salario_base" name="salario_base">
                      <input type="hidden" id="id_planilla" name="id_planilla">
                    </div>



                    <!-- Buttons -->
                    <div class="form-group text-center">
                      <button type="submit" class="btn btn-success">Aplicar Deducción</button>
                      <a href="VerPlanilla.php" class="btn">Volver</a>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </section>
          <script>
            // Función para abrir el modal
            function abrirModal(modalId) {
              document.getElementById(modalId).style.display = 'flex';
            }

            // Función para cerrar el modal
            function cerrarModal(modalId) {
              document.getElementById(modalId).style.display = 'none';
            }
          </script>
        </body>

        <script>
          function actualizarSalario() {
            const select = document.getElementById("id_usuario");
            const salario = select.options[select.selectedIndex].getAttribute("data-salario");
            const id_planilla = select.options[select.selectedIndex].getAttribute("data-id_planilla") || "";

            document.getElementById("salario_actual").value = salario;
            document.getElementById("salario_base").value = salario;
            document.getElementById("id_planilla").value = id_planilla;
          }
        </script>

        <script>
          // Si existe mensaje de éxito, recarga la página después de 3 segundos para limpiar POST y formulario
          document.addEventListener('DOMContentLoaded', function () {
            const mensaje = <?php echo json_encode($mensaje); ?>;
            if (mensaje && mensaje.trim() !== "") {
              setTimeout(() => {
                // Recarga la página limpiando el POST
                window.location.href = window.location.href.split('?')[0];
              }, 3000);
            }
          });
        </script>


</html>