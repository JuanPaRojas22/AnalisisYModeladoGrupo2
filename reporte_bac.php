<?php

// Conexión a la base de datos
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
session_start();

// Consulta para obtener los datos de las tablas usuario, planilla, nacionalidades, ocupaciones y departamento
$sql = "
    SELECT 
        u.id_usuario,
        u.nombre,
        u.apellido,
        u.correo_electronico,
        u.numero_telefonico,
        u.fecha_nacimiento,
        u.sexo,
        u.estado_civil,
        n.pais AS nacionalidad,
        o.nombre_ocupacion,
        p.jornada,
        p.hrs,
        p.salario_base,
        p.salario_neto,
        p.Cuenta_Bac,
        u.direccion_domicilio,
        p.tipo_quincena,
        d.nombre AS departamento
    FROM usuario u
    JOIN planilla p ON u.id_usuario = p.id_usuario
    JOIN nacionalidades n ON u.id_nacionalidad = n.id_nacionalidad
    LEFT JOIN ocupaciones o ON u.id_ocupacion = o.id_ocupacion
    LEFT JOIN departamento d ON u.id_departamento = d.id_departamento
";

$resultado = $conn->query($sql);

if (!$resultado) {
    die("❌ Error en la consulta SQL: " . $conn->error);
}


include 'template.php';
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Invitado';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte BAC</title>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Ruda:wght@400;700&display=swap');

    body {
        font-family: 'Ruda', sans-serif;
        background-color: #f7f7f7;
        margin: 0;
        padding: 20px;
    }

    .container {
        max-width: 1000px;
        margin: auto;
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    h1 {
        color: #333;
        font-weight: bold;
        margin-bottom: 20px;
    }

    .btn-export {
        display: inline-block;
        background-color: #168761;
        color: white;
        padding: 12px 20px;
        font-size: 16px;
        font-weight: bold;
        text-decoration: none;
        border-radius: 5px;
        margin-bottom: 20px;
        transition: background-color 0.3s;
        cursor: pointer;
        border: none;
    }

    .btn-export:hover {
        background-color: #168761;
    }

    .table-container {
        overflow-x: auto;
    }

    table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    font-size: 12px; /* Reducir el tamaño de la fuente */
    margin-left: auto; /* Centrar la tabla */
    margin-right: auto; /* Centrar la tabla */
}

th, td {
    padding: 8px; /* Reducir el espaciado de las celdas */
    text-align: center;
    font-size: 12px; /* Reducir el tamaño de la fuente */
    color: #555;
    border-bottom: 1px solid #ddd;
}

th {
    background-color: #116B67;
    color: white;
}

tr:hover {
    background-color: #f7f7f7;
}

td {
    text-align: center;
    padding: 12px;
     /* Bordes dorados */
    background-color: #f9f9f9; /* Fondo blanco */
}

/* Redondear solo las esquinas superiores del encabezado */
th:first-child {
    border-radius: 8px 0 0 0; /* Redondear la esquina superior izquierda */
}

th:last-child {
    border-radius: 0 8px 0 0; /* Redondear la esquina superior derecha */
}



tr:nth-child(even) td {
    background-color: #f1f1f1; /* Filas alternas gris claro */
}


    .btn-more {
        background-color: #147964;
        color: white;
        padding: 6px 12px;
        font-size: 14px;
        font-weight: bold;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .btn-more:hover {
        background-color: #147964;
    }

    .details {
        display: none;
        background:rgb(231, 229, 227);
        padding: 15px;
        border-radius: 8px;
        margin-top: 5px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease-in-out;
    }

    .details.show {
        display: block;
        animation: fadeIn 0.3s ease-in-out;
    }
</style>
</head>
<body>

<div class="container">
    <h1>📋 Reporte BAC</h1>

    <!-- Botón para exportar el reporte -->
    <a href="exportar_reporte_bac.php" class="btn-export">
        📥 Descargar Reporte
    </a>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Codigo BAC</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Salario Base</th>
                    <th>Salario Neto</th>
                    <th>Nacionalidad</th>
                    <th>Ocupación</th>
                    <th>Departamento</th>
                    <th>Tipo de Quincena</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fila = $resultado->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $fila['Cuenta_Bac']; ?></td>
                    <td><?php echo $fila['nombre']; ?></td>
                    <td><?php echo $fila['apellido']; ?></td>
                    <td><?php echo $fila['correo_electronico']; ?></td>
                    <td><?php echo $fila['numero_telefonico']; ?></td>
                    <td>₡<?php echo number_format($fila['salario_base'], 2); ?></td>
                    <td>₡<?php echo number_format($fila['salario_neto'], 2); ?></td>
                    <td><?php echo $fila['nacionalidad']; ?></td>
                    <td><?php echo $fila['nombre_ocupacion']; ?></td>
                    <td><?php echo $fila['departamento']; ?></td>
                    <td><?php echo $fila['tipo_quincena']; ?></td>
                    <td>
                        <button class="btn-more" onclick="toggleDetails('details-<?php echo $fila['id_usuario']; ?>')">
                            Ver más
                        </button>
                    </td>
                </tr>
                <tr class="details" id="details-<?php echo $fila['id_usuario']; ?>">
                    <td colspan="12">
                        <p><strong>Fecha de Nacimiento:</strong> <?php echo $fila['fecha_nacimiento']; ?></p>
                        <p><strong>Sexo:</strong> <?php echo $fila['sexo']; ?></p>
                        <p><strong>Estado Civil:</strong> <?php echo $fila['estado_civil']; ?></p>
                        <p><strong>Jornada:</strong> <?php echo $fila['jornada']; ?></p>
                        <p><strong>Horas Trabajadas:</strong> <?php echo $fila['hrs']; ?></p>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function toggleDetails(id) {
        var element = document.getElementById(id);
        if (element.classList.contains("show")) {
            element.classList.remove("show");
        } else {
            element.classList.add("show");
        }
    }
</script>

</body>
</html>

<?php
// Cerrar la conexión
$conn->close();
?>
