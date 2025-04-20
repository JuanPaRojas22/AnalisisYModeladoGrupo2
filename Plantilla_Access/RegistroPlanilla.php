
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'conexion.php';
require "template.php";

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Dashboard">
    <meta name="keyword" content="Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">

    <title>Registro Planilla</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Bootstrap core CSS -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <!--external css-->
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />

    <!-- Custom styles for this template -->
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/style-responsive.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style>
        body, h1, h2, h3, h4, h5, h6, p, a, span, td, th, li, div {
    color: black !important;
}

    </style>
</head>

<body>
    <!-- **********************************************************************************************************************************************************
      MAIN CONTENT
      *********************************************************************************************************************************************************** -->
    <!--main content start-->
    <section id="main-content">
        <section class="wrapper site-min-height">
            <h1></h1>
            <!-- /MAIN CONTENT -->
            <?php
            // Conexión a la base de datos
            
            $query_beneficios = "SELECT id_beneficio, razon FROM beneficios";
            $result_beneficios = $conn->query($query_beneficios);
            $query_usuarios = "SELECT id_usuario, nombre FROM usuario";
            $result_usuarios = $conn->query($query_usuarios);

            // Verificar si la conexión fue exitosa
            if ($conn->connect_error) {
                die("Error de conexión: " . $conn->connect_error);
            }

            // Variables para almacenar los mensajes de error y éxito
            $mensaje = "";

            // Procesar el formulario cuando se envía
            // Procesar el formulario cuando se envía
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                echo "<p>Formulario enviado correctamente.</p>";
            
                $id_usuario = $_POST['id_usuario'];
                $salario_base = $_POST['salario_base'];
                $hora_entrada = $_POST['hora_entrada'];
                $hora_salida = $_POST['hora_salida'];
                $codigo_bac = $_POST['codigo_bac'];
                $codigo_caja = $_POST['codigo_caja'];
                $codigo_INS = $_POST['codigo_INS'];
            
                $checkQuery = "SELECT id_usuario FROM planilla WHERE id_usuario = ?";
                $stmtCheck = $conn->prepare($checkQuery);
            
                if (!$stmtCheck) {
                    echo "<p style='color:red;'><strong>Error al preparar la consulta de verificación:</strong> " . $conn->error . "</p>";
                } else {
                    $stmtCheck->bind_param("i", $id_usuario);
                    $stmtCheck->execute();
                    $stmtCheck->store_result();
            
                    if ($stmtCheck->num_rows > 0) {
                        echo "<p style='color:red;'><strong>Error: Este usuario ya está registrado en la planilla</strong></p>";
                        $mensaje = "Error: Este usuario ya está registrado en la planilla.";
                    } else {
                        $query = "INSERT INTO planilla (id_usuario, salario_base, hora_entrada, hora_salida, codigo_bac, Codigo_CCSS, codigo_INS) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $stmtInsert = $conn->prepare($query);
            
                        if (!$stmtInsert) {
                            echo "<p style='color:red;'><strong>Error al preparar el INSERT:</strong> " . $conn->error . "</p>";
                        } else {
                            $stmtInsert->bind_param("issssss", $id_usuario, $salario_base, $hora_entrada, $hora_salida, $codigo_bac, $codigo_caja, $codigo_INS);
            
                            if ($stmtInsert->execute()) {
                                $mensaje = "Empleado registrado con éxito.";
                            } else {
                                echo "<p style='color:red;'><strong>Error al ejecutar el INSERT:</strong> " . $stmtInsert->error . "</p>";
                                $mensaje = "Error al registrar al empleado.";
                            }
                        }
                    }
                }
            }
            
            ?>

            <body>
                <div class="container">

                    <h1>Registrar Empleado en Planilla</h1>
                    <a href="VerPlanilla.php" class="button"><i class="bi bi-arrow-return-left"></i>
                    </a> <!-- Botón para ir al historial -->
                    <!-- Mostrar mensaje de éxito o error -->
                    <?php if ($mensaje): ?>
                        <p><?php echo $mensaje; ?></p>
                    <?php endif; ?>

                    <!-- Formulario para registrar el empleado en planilla -->
                    <form action="RegistroPlanilla.php" method="POST" class="filter-form">

                        <label for="id_usuario">Usuario:</label>
                        <select name="id_usuario" required>
                            <option value="">Seleccione un usuario</option>
                            <?php
                            while ($row = mysqli_fetch_assoc($result_usuarios)) {
                                echo "<option value='{$row['id_usuario']}'>{$row['nombre']}</option>";
                            }
                            ?>
                        </select>

                        <div class="form-group" style="text-align: center;">
                            <label for="hora_entrada" class="control-label">Hora de Entrada:</label>
                            <input type="time" id="hora_entrada" name="hora_entrada" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="hora_salida" class="control-label">Hora de Salida:</label>
                            <input type="time" id="hora_salida" name="hora_salida" class="form-control" required>
                        </div>

                        <label for="codigo_bac">Código BAC:</label>
                        <input type="text" name="codigo_bac" required style="text-align: center">

                        <label for="codigo_caja">Código Caja:</label>
                        <input type="text" name="codigo_caja" required style="text-align: center">

                        <label for="codigo_INS">Código INS:</label>
                        <input type="text" name="codigo_INS" required style="text-align: center">

                        <label for="salario_base">Salario Base:</label>
                        <input type="number" name="salario_base" required style="text-align: center">

                        <button type="submit">Registrar Empleado</button>

                    </form>

                </div>
            </body>

            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f7f7f7;
                    margin: 0;
                    padding: 0;
                }

                .form-group {
                    text-align: center;
                    /* Centra el contenido del div */
                }

                .form-control {
                    display: block;
                    margin: 0 auto;
                    text-align: center;
                    width: 100%;
                    /* Asegura que el input ocupe todo el ancho disponible */
                    max-width: 200px;
                    /* Opcional: limita el ancho para evitar que se vea muy grande */
                }

                .container {
                    width: 80%;
                    margin: 50px auto;
                    padding: 20px;
                    background-color: #ffffff;
                    border-radius: 8px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.69);
                }

                h1 {
                    text-align: center;
                    color: #333;
                    margin-bottom: 30px;
                }

                .button {
                    display: inline-block;
                    background-color: #147964;
                    color: white;
                    padding: 10px 20px;
                    font-size: 16px;
                    font-weight: bold;
                    text-align: center;
                    text-decoration: none;
                    border-radius: 5px;
                    margin-bottom: 20px;
                    transition: background-color 0.3s;
                }

                .button:hover {
                    background-color: #147964;
                }

                form {
                    width: 100%;
                }

                label {
                    font-size: 16px;
                    color: #333;
                    margin-bottom: 8px;
                    display: block;
                    text-align: center;

                }

                input,
                textarea,
                button {
                    width: 100%;
                    padding: 10px;
                    font-size: 16px;
                    margin-bottom: 20px;
                    border: 1px solid #ccc;
                    border-radius: 5px;
                }

                button {
                    background-color: #147964;
                    color: white;
                    border: none;
                    cursor: pointer;
                }

                button:hover {
                    background-color: #147964;
                }

                select {
                    width: 100%;
                    padding: 10px;
                    font-size: 16px;
                    border: 2px solidrgb(15, 15, 15);
                    border-radius: 5px;
                    background: #f9f9f9;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    text-align: center;
                }

                select:hover {
                    border-color: #147964;
                }

                select:focus {
                    outline: none;
                    border-color: #147964;
                    box-shadow: #147964;

                }
            </style>

</html>


<?php
// Cerrar la conexión
$conn->close();
?>

</section>
</section>
<!--main content end-->

</section>

<!-- js placed at the end of the document so the pages load faster -->
<script src="assets/js/jquery.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="assets/js/jquery.ui.touch-punch.min.js"></script>
<script class="include" type="text/javascript" src="assets/js/jquery.dcjqaccordion.2.7.js"></script>
<script src="assets/js/jquery.scrollTo.min.js"></script>
<script src="assets/js/jquery.nicescroll.js" type="text/javascript"></script>