<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "gestionempleados");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Consulta para obtener los datos de reporte_ins
$sql = "SELECT * FROM reporte_ins";
$resultado = $conexion->query($sql);

session_start();
include 'template.php';
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Invitado';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte INS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container-fluid {
            padding-left: 250px; /* Ajuste para evitar que el sidebar cubra la tabla */
        }
        .table-container {
            margin-top: 20px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        td, th {
            text-align: center;
            vertical-align: middle;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .table-responsive {
            max-width: 100%;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="container table-container">
            <h2 class="text-center">📋 Reporte INS</h2>
            <p class="text-center">Bienvenido, <?php echo htmlspecialchars($username); ?></p>
            <a href="exportar_excel.php" class="btn btn-success mb-3">📥 Exportar a Excel</a>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-nowrap">ID</th>
                            <th class="text-nowrap">Nombre</th>
                            <th class="text-nowrap">Apellido</th>
                            <th class="text-nowrap">Fecha Nacimiento</th>
                            <th class="text-nowrap">Teléfono</th>
                            <th class="text-nowrap">Correo</th>
                            <th class="text-nowrap">Sexo</th>
                            <th class="text-nowrap">Estado Civil</th>
                            <th class="text-nowrap">Nacionalidad</th>
                            <th class="text-nowrap">Jornada</th>
                            <th class="text-nowrap">Días</th>
                            <th class="text-nowrap">Horas</th>
                            <th class="text-nowrap">Salario</th>
                            <th class="text-nowrap">Ocupación</th>
                            <th class="text-nowrap">Descripción Ocupación</th>
                            <th class="text-nowrap">Dirección</th>
                            <th class="text-nowrap">Salario Base</th>
                            <th class="text-nowrap">Salario Neto</th>
                            <th class="text-nowrap">Tipo de Quincena</th>
                            <th class="text-nowrap">Mes</th>
                            <th class="text-nowrap">Año</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($fila = $resultado->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $fila['id_usuario']; ?></td>
                            <td><?php echo $fila['nombre']; ?></td>
                            <td><?php echo $fila['apellido']; ?></td>
                            <td><?php echo $fila['fecha_nacimiento']; ?></td>
                            <td><?php echo $fila['telefono']; ?></td>
                            <td><?php echo $fila['correo']; ?></td>
                            <td><?php echo $fila['sexo']; ?></td>
                            <td><?php echo $fila['estado_civil']; ?></td>
                            <td><?php echo $fila['nacionalidad']; ?></td>
                            <td><?php echo $fila['jornada']; ?></td>
                            <td><?php echo $fila['dias']; ?></td>
                            <td><?php echo $fila['hrs']; ?></td>
                            <td><?php echo number_format($fila['salario'], 2); ?></td>
                            <td><?php echo $fila['ocupacion']; ?></td>
                            <td><?php echo $fila['descripcion_ocupacion']; ?></td>
                            <td><?php echo $fila['direccion_domicilio']; ?></td>
                            <td><?php echo number_format($fila['salario_base'], 2); ?></td>
                            <td><?php echo number_format($fila['salario_neto'], 2); ?></td>
                            <td><?php echo $fila['tipo_quincena']; ?></td>
                            <td><?php echo $fila['mes']; ?></td>
                            <td><?php echo $fila['anio']; ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

<?php
// Cerrar la conexión
$conexion->close();
?>

