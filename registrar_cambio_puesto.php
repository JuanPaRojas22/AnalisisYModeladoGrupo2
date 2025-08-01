<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
// Parámetros de conexión
$host = "accespersoneldb.mysql.database.azure.com";
$user = "adminUser";
$password = "admin123+";
$dbname = "gestionEmpleados";
$port = 3306;

// Ruta al certificado CA para validar SSL
$ssl_ca = '/home/site/wwwroot/certs/BaltimoreCyberTrustRoot.crt.pem';

// Inicializamos mysqli
$conn = mysqli_init();

// Configuramos SSL
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);


// Intentamos conectar usando SSL (con la bandera MYSQLI_CLIENT_SSL)
if (!$conn->real_connect($host, $user, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Establecemos el charset
mysqli_set_charset($conn, "utf8mb4");

// Verificar autenticación del usuario
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Invitado';

// Incluir la plantilla
include 'template.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Registrar Cambio de Puesto</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/style-responsive.css" rel="stylesheet">

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 700px;
            margin: 50px auto;
            /* centrado */
            padding: 25px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            color: black;
        }

        h1 {
            text-align: center;
            color: black;
            margin-bottom: 30px;
        }

        .btn {
            display: inline-block;
            background-color: #147964;
            color: black;
            padding: 10px 20px;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            transition: background-color 0.3s;
        }

        form {
            width: 100%;
        }

        label {
            font-size: 16px;
            color: black;
            margin-bottom: 8px;
            display: block;
        }

        input,
        textarea,
        btn,
        select {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            color: black;
            /* for input text */
            background-color: white;
        }

        textarea {
            resize: vertical;
        }

        btn {
            background-color: #147964;
            color: black;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }

        p {
            font-weight: bold;
            color: #116B67;
        }
    </style>
</head>

<body>
    <div class="container">
        <a class='btn' href="ver_historial_cambios.php">Historial de Cambios</a>

        <h1>Registrar Cambio de Puesto</h1>

        <?php
        // Conexión
        $host = "accespersoneldb.mysql.database.azure.com";
        $user = "adminUser";
        $password = "admin123+";
        $dbname = "gestionEmpleados";
        $port = 3306;

        $ssl_ca = '/home/site/wwwroot/certs/BaltimoreCyberTrustRoot.crt.pem';

        $conn = mysqli_init();
        mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
        mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);

        if (!$conn->real_connect($host, $user, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
            die("Error de conexión: " . mysqli_connect_error());
        }

        mysqli_set_charset($conn, "utf8mb4");

        $mensaje = "";

        // Obtener lista de usuarios
        $query = "SELECT id_usuario, nombre, apellido FROM Usuario";
        $result = $conn->query($query);
        $usuarios = ($result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];

        // Obtener lista de ocupaciones
        $ocupaciones = [];
        $consultaOcupaciones = "SELECT id_ocupacion, nombre_ocupacion FROM Ocupaciones";
        $resOcupaciones = $conn->query($consultaOcupaciones);
        if ($resOcupaciones && $resOcupaciones->num_rows > 0) {
            $ocupaciones = $resOcupaciones->fetch_all(MYSQLI_ASSOC);
        }

        // Procesar formulario
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_usuario = intval($_POST['id_usuario']);
            $nuevo_puesto = intval($_POST['nuevo_puesto']); // id_ocupacion
            $sueldo_nuevo = floatval($_POST['sueldo_nuevo']);
            $motivo = $_POST['motivo'];
            $fecha_cambio = $_POST['fecha_cambio'];

            if ($sueldo_nuevo <= 0) {
                $mensaje = "❌ El sueldo nuevo debe ser un valor numérico mayor que 0.";
            } else {
                $conn->begin_transaction();
                try {
                    // Insertar en historial_cargos
                    $sql1 = "INSERT INTO historial_cargos (
                            id_usuario, nuevo_puesto, sueldo_nuevo, motivo, fecha_cambio, fechacreacion, usuariocreacion
                         ) VALUES (?, ?, ?, ?, ?, CURDATE(), 'usuario_logueado')";
                    $stmt1 = $conn->prepare($sql1);
                    $stmt1->bind_param("iidss", $id_usuario, $nuevo_puesto, $sueldo_nuevo, $motivo, $fecha_cambio);
                    $stmt1->execute();

                    // 2. Obtener bonos del usuario
                    $sqlBonos = "SELECT SUM(monto) AS total_bonos FROM bonos_usuario WHERE id_usuario = ?";
                    $stmtBonos = $conn->prepare($sqlBonos);
                    $stmtBonos->bind_param("i", $id_usuario);
                    $stmtBonos->execute();
                    $resultBonos = $stmtBonos->get_result()->fetch_assoc();
                    $bonos = $resultBonos['total_bonos'] ?? 0;

                    // 3. Obtener deducciones del usuario
        
                    $sqlDeducciones = "SELECT SUM(monto) AS total_deducciones FROM deducciones_usuario WHERE id_usuario = ?";
                    $stmtDeducciones = $conn->prepare($sqlDeducciones);
                    $stmtDeducciones->bind_param("i", $id_usuario);
                    $stmtDeducciones->execute();
                    $resultDeducciones = $stmtDeducciones->get_result()->fetch_assoc();
                    $deducciones = $resultDeducciones['total_deducciones'] ?? 0;

                    $salario_neto = $sueldo_nuevo + $bonos - $deducciones;
                    // 4. Actualizar la tabla planilla con el nuevo sueldo, puesto y salario neto
                    $sql2 = "UPDATE planilla SET salario_base = ?, id_ocupacion = ?, salario_neto = ? WHERE id_usuario = ?";
                    $stmt2 = $conn->prepare($sql2);
                    $stmt2->bind_param("didi", $sueldo_nuevo, $nuevo_puesto, $salario_neto, $id_usuario);
                    $stmt2->execute();
                    $conn->commit();
                    $mensaje = "Cambio de puesto registrado con éxito.";
                } catch (mysqli_sql_exception $e) {
                    $conn->rollback();
                    $mensaje = "Error al registrar el cambio: " . $e->getMessage();
                }
            }
        }
        ?>

        <?php if ($mensaje): ?>
            <p><?php echo $mensaje; ?></p>
        <?php endif; ?>

        <form action="registrar_cambio_puesto.php" method="POST">
            <label for="id_usuario">Seleccione el Usuario:</label>
            <select name="id_usuario" required>
                <option value="">Seleccione un usuario</option>
                <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?php echo $usuario['id_usuario']; ?>">
                        <?php echo $usuario['nombre'] . ' ' . $usuario['apellido']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="puesto_anterior">Puesto Anterior:</label>
            <input type="text" name="puesto_anterior" id="puesto_anterior" readonly>

            <label for="sueldo_anterior">Sueldo Anterior:</label>
            <input type="number" name="sueldo_anterior" id="sueldo_anterior" readonly>

            <label for="nuevo_puesto">Nuevo Puesto:</label>
            <select name="nuevo_puesto" required>
                <option value="">Seleccione un puesto</option>
                <?php foreach ($ocupaciones as $ocupacion): ?>
                    <option value="<?php echo $ocupacion['id_ocupacion']; ?>">
                        <?php echo $ocupacion['nombre_ocupacion']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="sueldo_nuevo">Nuevo Sueldo:</label>
            <input type="number" name="sueldo_nuevo" step="any" required>

            <label for="motivo">Motivo del Cambio:</label>
            <textarea name="motivo" required></textarea>

            <label for="fecha_cambio">Fecha de Cambio:</label>
            <input type="date" name="fecha_cambio" required>

            <button class='btn' type="submit">Registrar Cambio</button>
        </form>
    </div>


    <!-- Script para autocompletar datos -->
    <script>
        document.querySelector('select[name="id_usuario"]').addEventListener('change', function () {
            const id_usuario = this.value;

            if (id_usuario !== "") {
                fetch(`obtener_datos_usuario.php?id_usuario=${id_usuario}`)
                    .then(res => res.json())
                    .then(data => {
                        document.getElementById('puesto_anterior').value = data.puesto_anterior || '';
                        document.getElementById('sueldo_anterior').value = data.sueldo_anterior || '';
                    })
                    .catch(error => {
                        console.error('Error al obtener datos:', error);
                        document.getElementById('puesto_anterior').value = '';
                        document.getElementById('sueldo_anterior').value = '';
                    });
            } else {
                document.getElementById('puesto_anterior').value = '';
                document.getElementById('sueldo_anterior').value = '';
            }
        });
    </script>
</body>

</html>