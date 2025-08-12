<?php
ob_start(); // <-- esto antes de cualquier salida
require 'conexion.php';
$conn = obtenerConexion();
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  header("Location: login.php");
  exit;
}

require "template.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// Procesamiento del formulario POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
      if ($stmt)
        $stmt->close();
    }

    // Insertar deducciones en la tabla deducciones
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

    if ($stmt_deduccion)
      $stmt_deduccion->close();
    if ($stmt_check)
      $stmt_check->close();
    if ($stmt_salario)
      $stmt_salario->close();

    $_SESSION['mensaje_exito'] = "Retenciones quincenales aplicadas correctamente.<br>Salario Neto quincenal actualizado: ₡" . number_format($salario_neto_quincenal, 2);

    // Redirigir para evitar reenvío de formulario y limpiar POST
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;

  } else {
    $_SESSION['mensaje_error'] = "No se encontró salario base para el usuario seleccionado.";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
  }
}

// Mostrar mensajes si existen y luego borrarlos
$mensaje = "";
if (isset($_SESSION['mensaje_exito'])) {
  $mensaje = "<div class='alert alert-success text-center'>" . $_SESSION['mensaje_exito'] . "</div>";
  unset($_SESSION['mensaje_exito']);
}
if (isset($_SESSION['mensaje_error'])) {
  $mensaje = "<div class='alert alert-danger text-center'>" . $_SESSION['mensaje_error'] . "</div>";
  unset($_SESSION['mensaje_error']);
}

// Consulta para obtener las planillas existentes con información del usuario
$query_planilla = "SELECT p.id_planilla, p.id_usuario, u.nombre, u.apellido, p.salario_base 
FROM Planilla p 
JOIN Usuario u ON p.id_usuario = u.id_usuario";
$result_planilla = $conn->query($query_planilla);
?>

<!DOCTYPE html>
<html lang="en">



<title>Aplicar Deducciones</title>






<body>

  <section id="container">
    <section id="main-content">
      <section class="wrapper site-min-height">


        <style>
          body {
            background-color: #f4f7f8;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
          }

          .container {
            max-width: 600px;
            background: #fff;
            padding: 30px;
            margin: 40px auto;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            color: #222;
          }

          h2 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: 700;
            color: #147964;
          }

          label {
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
            color: #444;
          }

          select,
          input[type="text"] {
            width: 100%;
            padding: 10px 12px;
            font-size: 16px;
            border: 1.5px solid #ccc;
            border-radius: 8px;
            margin-bottom: 20px;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
          }

          select:focus,
          input[type="text"]:focus {
            border-color: #147964;
            outline: none;
          }

          .btn {
            background-color: #147964;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: inline-block;
            text-decoration: none;
            margin-right: 10px;
          }

          .btn:hover {
            background-color: #0f5f4d;
          }

          .text-center {
            text-align: center;
          }

          .alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 600;
          }

          .alert-success {
            background-color: #d4edda;
            color: #155724;
          }

          .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
          }
        </style>

        <div class="container">
          <h2>Aplicar Deducción Salarial</h2>

          <!-- Mensajes -->
          <?php echo $mensaje; ?>

          <a href="AgregarDeduccionesextra.php" class="btn">Agregar Deducción Extra</a>

          <form action="" method="POST" class="form-horizontal" style="margin-top: 20px;">
            <label for="id_usuario">Seleccione un Usuario:</label>
            <select id="id_usuario" name="id_usuario" required onchange="actualizarSalario()">
              <option value="">Seleccione un usuario</option>
              <?php
              if ($result_planilla->num_rows > 0) {
                while ($row = $result_planilla->fetch_assoc()) {
                  echo '<option value="' . $row["id_usuario"] . '" data-salario="' . $row["salario_base"] . '">' . htmlspecialchars($row["nombre"]) . ' ' . htmlspecialchars($row["apellido"]) . '</option>';
                }
              }
              ?>
            </select>

            <label for="salario_base">Salario Actual:</label>
            <input type="text" id="salario_actual" name="salario_actual" readonly>
            <input type="hidden" id="salario_base" name="salario_base">
            <input type="hidden" id="id_planilla" name="id_planilla">

            <div class="text-center">
              <button type="submit" class="btn">Aplicar Deducción</button>
              <a href="VerPlanilla.php" class="btn">Volver</a>
            </div>
          </form>
        </div>

        <script>
          function actualizarSalario() {
            const select = document.getElementById("id_usuario");
            const salario = select.options[select.selectedIndex].getAttribute("data-salario") || "";
            document.getElementById("salario_actual").value = salario;
            document.getElementById("salario_base").value = salario;
          }
        </script>
</body>

</html>

<?php
$conn->close();
ob_end_flush(); // <-- esto asegura que la salida se libere correctamente
?>
