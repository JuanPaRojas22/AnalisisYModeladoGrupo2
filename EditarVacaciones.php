<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
require_once __DIR__ . '/Impl/UsuarioDAOSImpl.php';
require_once __DIR__ . '/Impl/Historial_Solicitud_Modificacion_VacacionesDAOSImpl.php';
include "template.php";

$UsuarioDAO = new UsuarioDAOSImpl();
$Historial_Solicitud_Modificacion_VacacionesDAO = new Historial_Solicitud_Modificacion_VacacionesDAOSImpl();
$user_id = $_SESSION['id_usuario'];

// Obtiene los detalles del usuario por id
$userDepartmentData = $UsuarioDAO->getUserDepartmentById($user_id);
$userDepartment = $userDepartmentData ? $userDepartmentData['id_departamento'] : null;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Dashboard">
    <meta name="keyword" content="Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">

    <title>Gestión de Vacaciones</title>



    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style>
        .profile-container {
            margin-left: 250px;
            padding: 60px;
        }
    </style>
</head>

<body>

    <section id="container">

        <section id="main-content">
            <section class="wrapper site-min-height">

                <!-- /MAIN CONTENT -->
                <?php
                // Verificar si el usuario está logueado
                // Conexión a la base de datos
                $host = "localhost";
                $usuario = "root";
                $clave = "";
                $bd = "gestionempleados";
                $conn = new mysqli($host, $usuario, $clave, $bd);
                if ($conn->connect_error) {
                    die("Error de conexión: " . $conn->connect_error);
                }
                //$search = isset($_GET['search']) ? (int) $_GET['search'] : null;
                $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
                $limit = 5;
                $offset = ($page - 1) * $limit;

               

                

                // Consulta para obtener el departamento del usuario              
                $result = $Historial_Solicitud_Modificacion_VacacionesDAO->getSolicitudesEditarPendientes_O_Aprobadas($userDepartment, $limit, $offset);

                ?>
                <html lang="es">

                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">


                    <style>
                        /* Estilos específicos para la página editarvacaciones */
                        /* Modal */
                        .modal-contenido {
                            padding: 20px;
                            width: 80%;
                            max-width: 600px;
                            margin: 10% auto;
                            background-color: #fff;
                            border-radius: 8px;
                            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                        }

                        /* Agrupar los elementos del formulario */
                        .form-group {
                            margin-bottom: 15px;
                            /* Espacio entre cada sección del formulario */
                        }

                        /* Etiquetas de formulario */
                        .form-group label {
                            display: block;
                            /* Asegura que las etiquetas estén en su propia línea */
                            font-weight: bold;
                            margin-bottom: 5px;
                        }

                        /* Campos de texto */
                        .form-group input {
                            width: 100%;
                            /* Los inputs ocupan todo el ancho disponible */
                            padding: 10px;
                            font-size: 16px;
                            border-radius: 5px;
                            border: 1px solid #ddd;
                        }

                        /* Botón */
                        .form-group button {
                            width: 100%;
                            padding: 10px;
                            background-color: #0D566B;
                            color: white;
                            font-size: 18px;
                            font-weight: bold;
                            border-radius: 5px;
                            border: none;
                            cursor: pointer;
                        }

                        .form-group button:hover {
                            background-color: #084d59;
                        }

                        body {
                            font-family: 'Ruda', sans-serif;
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

                        /* Títulos */
                        h1,
                        h3,
                        h4 {
                            text-align: center;
                            color: black;
                            font-weight: bold;
                            margin-bottom: 20px;
                        }

                        /* Estilos del botón */
                        .btn {
                            display: inline-block;
                            background-color: #0D566B;
                            color: white;
                            padding: 10px 20px;
                            font-size: 25px;
                            font-weight: bold;
                            text-align: center;
                            text-decoration: none;
                            border-radius: 5px;
                            margin-bottom: 20px;
                            transition: background-color 0.3s;
                        }

                        .btn:hover {
                            background-color: #0D566B;
                        }

                        .btn:active {
                            background-color: #0D566B;
                        }

                        /* Tabla */
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-top: 20px;
                            border-radius: 8px;
                            overflow: hidden;
                        }

                        th,
                        td {
                            padding: 12px;
                            text-align: left;
                            font-size: 16px;
                            color: #555;
                            border-bottom: 1px solid #ddd;
                        }

                        th {
                            background-color: #116B67;
                            color: #fff;
                        }

                        tr:hover {
                            background-color: #f1f1f1;
                        }

                        td {
                            background-color: #f9f9f9;
                        }

                        .no-records {
                            text-align: center;
                            font-style: italic;
                            color: #888;
                        }

                        /* Contenedor para los botones y la búsqueda */
                        .d-flex {
                            display: flex;
                            justify-content: space-between;
                            /* o try 'start' si están muy separados */
                            align-items: center;
                            gap: 20px;
                            /* espacio entre botón y form */
                            flex-wrap: wrap;
                            /* para que no se rompa en pantallas pequeñas */
                        }

                        /* Ajustes del botón "Volver" */
                        .btn-back {
                            background-color: #0B4F6C;
                            color: white;
                            padding: 10px 20px;
                            font-size: 16px;
                            font-weight: bold;
                            text-align: center;
                            text-decoration: none;
                            border-radius: 5px;
                            border: none;
                            cursor: pointer;
                            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
                            transition: background-color 0.3s;
                        }

                        .btn-back:hover {
                            background-color: #0A3D55;
                        }

                        /* Formulario de búsqueda */
                        form {
                            display: flex;
                            align-items: center;
                            gap: 10px;
                            /* Espacio entre los elementos */
                            margin-right: 50%;
                            /* Hace que el formulario ocupe el espacio disponible */
                        }

                        .input-group {
                            min-width: 220px;
                            flex-grow: 1;
                            /* Hace que el grupo de entrada crezca para ocupar espacio */
                        }

                        .input-group input {
                            min-width: 150px;
                            width: 100%;
                            /* Asegura que el input ocupe todo el espacio disponible */
                        }

                        .btn-primary {
                            background-color: #0D566B;
                            color: white;
                            font-size: 18px;
                            font-weight: bold;
                            padding: 10px 15px;
                            border-radius: 5px;
                            border: none;
                            cursor: pointer;

                        }

                        .btn-primary:hover {
                            background-color: #084d59;
                        }
                    </style>
                </head>

                <body class="editarvacaciones">
                    <?php
                    $solicitudesCount = $result->num_rows;
                    ?>
                    <div class="container">
                        <h1>Editar Vacaciones</h1>
                        <h4>Bienvenido, <?php echo $_SESSION['username']; ?> tienes <?php echo $solicitudesCount; ?>
                            solicitudes de edición de vacaciones.</h4>

                        <!-- Contenedor para los botones y búsqueda -->
                        <div class="d-flex" style="gap: 20px; align-items: center;">
                            <button class="btn btn-back" onclick="window.history.back()">Volver</button>

                            <div class="mb-3" style="margin-left: 30%;">
                                <input type="date" id="buscarFecha" class="form-control" style="width: 400px;" />
                            </div>
                        </div>


                        <!-- Mostrar tabla con los cambios de puesto -->
                        <table>
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Apellido</th>
                                    <th>Departamento</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Fin</th>
                                    <th>Dias Tomados</th>
                                    <th>Dias Restantes</th>
                                    <th>Estado</th>
                                    <th>Accion</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php

                                // Se inicializa un contador
                                $contador = $offset + 1;

                                // Mostrar los resultados de la consulta
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>     
                                <td>" . $row['Nombre'] . "</td>
                                <td>" . $row['Apellido'] . "</td>
                                <td>" . $row['Departamento'] . "</td>
                                <td>" . $row['NuevaFechaInicio'] . "</td>
                                <td>" . $row['NuevaFechaFin'] . "</td>
                                <td>" . $row['dias_solicitados'] . "</td>
                                <td>" . $row['DiasRestantes'] . "</td>
                                <td>" . $row['estado'] . "</td>
                                <td>
                                    <a class='btn btn-success' style='font-size: 2.5rem;' href='detalleEditarVacacion.php?id=" . $row['id_historial_solicitud_modificacion'] . "' >
                                        <i class='bi bi-file-earmark-person'></i> 
                                    </a>
                                </td>
                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='9' class='no-records'>No se encontraron registros.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                        <?php
                        if (empty($search)) {
                            $total_sql = "SELECT COUNT(*) as total
                            FROM Historial_Solicitud_Modificacion_Vacaciones HSMV
                            INNER JOIN usuario U ON HSMV.id_usuario = U.id_usuario
                            WHERE HSMV.estado = 'Pendiente' AND U.id_departamento = ?";
                            $stmt_total = $conn->prepare($total_sql);
                            $stmt_total->bind_param("i", $userDepartment);
                            $stmt_total->execute();
                            $total_result = $stmt_total->get_result();
                            $total_rows = $total_result->fetch_assoc()['total'];
                            $total_pages = ceil($total_rows / $limit);
                        } else {
                            $total_pages = 0;
                        }
                        ?>

                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Page navigation" class="mt-4">
                                <ul class="pagination justify-content-end"
                                    style="width: 80%; margin: auto; padding-right: 20px;">
                                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $page - 1 ?>">Anterior</a>
                                    </li>
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $page + 1 ?>">Siguiente</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>


                    </div>
            </section>

            <script>
                document.getElementById('buscarFecha').addEventListener('input', function () {
                        const fechaBuscada = this.value;
                        const filas = document.querySelectorAll('table tbody tr');

                        filas.forEach(fila => {
                            const fechaInicio = fila.children[3].textContent.trim();
                            if (fechaInicio.includes(fechaBuscada) || fechaBuscada === "") {
                                fila.style.display = "";
                            } else {
                                fila.style.display = "none";
                            }
                        });
                    });
            </script>

</body>

</html>