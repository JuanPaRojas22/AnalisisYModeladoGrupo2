
<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

include 'template.php'; // Incluir el encabezado de la plantilla

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Dashboard">
    <meta name="keyword" content="Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">

    <title>Cambio de Puesto</title>

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
</head>

<body>

    <section id="container">

        <section id="main-content">
    <section class="wrapper site-min-height">
        <h1>Cambio</h1>
        <!-- /MAIN CONTENT -->
        <?php
        // Conexión a la base de datos
        $host = "localhost"; 
        $usuario = "root"; 
        $clave = ""; 
        $bd = "gestionempleados"; 

        $conn = new mysqli($host, $usuario, $clave, $bd);

        // Verificar si la conexión fue exitosa
        if ($conn->connect_error) {
            die("Error de conexión: " . $conn->connect_error);
        }

        // Variables para almacenar los mensajes de error y éxito
        $mensaje = "";

        // Obtener los usuarios de la base de datos
$query = "SELECT id_usuario, nombre, apellido FROM Usuario";
$result = $conn->query($query);

// Verificar si se obtuvieron resultados
if ($result->num_rows > 0) {
    $usuarios = [];
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
} else {
    $usuarios = [];
}

// Si el formulario se ha enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos del formulario
    $id_usuario = $_POST['id_usuario'];
    $puesto_anterior = $_POST['puesto_anterior'];
    $nuevo_puesto = $_POST['nuevo_puesto'];
    $sueldo_anterior = $_POST['sueldo_anterior'];
    $sueldo_nuevo = $_POST['sueldo_nuevo'];
    $motivo = $_POST['motivo'];
    $fecha_cambio = $_POST['fecha_cambio'];

    // Validar que los sueldos sean valores numéricos válidos
    if (!is_numeric($sueldo_anterior) || $sueldo_anterior <= 0) {
        $mensaje = "El sueldo anterior debe ser un valor numérico mayor que 0.";
    } elseif (!is_numeric($sueldo_nuevo) || $sueldo_nuevo <= 0) {
        $mensaje = "El sueldo nuevo debe ser un valor numérico mayor que 0.";
    } else {
        // Insertar los datos en la base de datos
        $query = "INSERT INTO historial_cargos (id_usuario, puesto_anterior, nuevo_puesto, sueldo_anterior, sueldo_nuevo, motivo, fecha_cambio, fechacreacion, usuariocreacion) 
          VALUES ('$id_usuario', '$puesto_anterior', '$nuevo_puesto', '$sueldo_anterior', '$sueldo_nuevo', '$motivo', '$fecha_cambio', CURDATE(), 'usuario_logueado')";

        if ($conn->query($query) === TRUE) {
            $mensaje = "Cambio de puesto registrado con éxito.";
        } else {
            $mensaje = "Error al registrar el cambio de puesto: " . $conn->error;
        }
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Cambio de Puesto</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 50px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        .button {
        display: inline-block;
        background-color: #147964;
        color: #000;
        padding: 10px 20px;
        font-size: 16px;
        font-weight: bold;
         text-align: center;
        text-decoration: none;
        border-radius: 5px;
        margin-bottom: 20px;
        transition: background-color 0.3s;
        }

        
        /* Estilo del select */
        select {
         width: 100%;  
        padding: 10px;  
        font-size: 16px; 
        margin-bottom: 20px;  
        border: 1px solid #ccc;  
        border-radius: 5px;  
        background-color: #fff;  
        color: #333;  
        }


        form {
            width: 100%;
        }

        label {
            font-size: 16px;
            color: #333;
            margin-bottom: 8px;
            display: block;
        }

        input, textarea, button {
            padding: 10px;
            font-size: 16px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #147964;
            color: #000;
            border: none;
            cursor: pointer;
        }

        
    </style>
</head>
<body>
    <div class="container">
    <a href="ver_historial_cambios.php" class="button" style="background-color: #147964; color: black; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">Historial de Cambios</a>

        <h1>Registrar Cambio de Puesto</h1>

        <!-- Mostrar mensaje de éxito o error -->
        <?php if ($mensaje): ?>
            <p><?php echo $mensaje; ?></p>
        <?php endif; ?>

        <!-- Formulario para registrar el cambio de puesto -->
<form action="registrar_cambio_puesto.php" method="POST">
    <!-- Campo para seleccionar el usuario -->
    <label for="id_usuario">Seleccione el Usuario:</label>
    <select name="id_usuario" required>
        <option value="">Seleccione un usuario</option>
        <?php foreach ($usuarios as $usuario): ?>
            <option value="<?php echo $usuario['id_usuario']; ?>">
                <?php echo $usuario['nombre'] . ' ' . $usuario['apellido']; ?>
            </option>
        <?php endforeach; ?>
    </select>

    <!-- Campo para Puesto Anterior -->
    <label for="puesto_anterior">Puesto Anterior:</label>
    <input type="text" name="puesto_anterior" required>

    <!-- Campo para Nuevo Puesto -->
    <label for="nuevo_puesto">Nuevo Puesto:</label>
    <input type="text" name="nuevo_puesto" required>

    <!-- Campo para Sueldo Anterior -->
    <label for="sueldo_anterior">Sueldo Anterior:</label>
    <input type="number" name="sueldo_anterior" step="any" required>

    <!-- Campo para Nuevo Sueldo -->
    <label for="sueldo_nuevo">Nuevo Sueldo:</label>
    <input type="number" name="sueldo_nuevo" step="any" required>

    <!-- Campo para Motivo del Cambio -->
    <label for="motivo">Motivo del Cambio:</label>
    <textarea name="motivo" required></textarea>

    <!-- Campo para Fecha de Cambio -->
    <label for="fecha_cambio">Fecha de Cambio:</label>
    <input type="date" name="fecha_cambio" required>

    <!-- Botón para enviar el formulario -->
    <button type="submit">Registrar Cambio</button>
</form>
</div>
</body>
</html>




</section>
</section>

   


