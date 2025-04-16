<?php 
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
require_once __DIR__ . '/Impl/UsuarioDAOSImpl.php';
require_once __DIR__ . '/Impl/VacacionDAOSImpl.php';
include "template.php";


$UsuarioDAO = new UsuarioDAOSImpl();
$VacacionDAO = new VacacionDAOSImpl();
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

    <title>Gestión de Usuarios</title>

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
        .profile-container {
            margin-left: 250px;
            padding: 60px;
        }
    </style>
</head>
<body>

    <section id="container">
        
    <!--[if lt IE 9]>
      QUIERO HACER QUE SOLO MUESTRE LAS PENDIENTES DE VACACIONES DE UN USUARIO ADMINISTRADOR
    <![endif]-->
    
            
        
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

                // Consulta para obtener el departamento del usuario
                
                $result = $VacacionDAO->getSolicitudesPendientes($userDepartment);


                ?>

                <html lang="es">

                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Listado Vacaciones</title>
                    <style>
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

                        h1 {
                            text-align: center;
                            color: #333;
                            margin-bottom: 50px;
                            margin-right: 10%;
                            font-weight: bold;
                        }

                        h3 {
                            text-align: center;
                            color: black;
                            margin-bottom: 50px;
                            margin-right: 10%;
                            font-weight: bold;
                        }

                        .btn {
                            display: inline-block;
                            background-color: #106469;
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
                            background-color: #106469;
                        }

                        .btn:active {
                            background-color: #106469;
                        }

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

                        /* Estilos del fondo del modal */
                        .modal {
                            display: none;
                            position: fixed;
                            z-index: 1;
                            left: 0;
                            top: 0;
                            width: 100%;
                            height: 100%;
                            background-color: rgba(0, 0, 0, 0.5);
                            justify-content: center;
                            align-items: center;
                        }

                        /* Contenido del modal */
                        .modal-content {
                            background-color: white;
                            padding: 20px;
                            border-radius: 10px;
                            width: 300px;
                            text-align: center;
                            margin-bottom: 5%;

                        }

                        /* Botón de cerrar */
                        .close {
                            position: absolute;
                            top: 10px;
                            right: 20px;
                            font-size: 25px;
                            cursor: pointer;
                        }

                        /* Botones dentro del modal */
                        .modal-content a {
                            display: block;
                            margin: 10px 0;
                            padding: 10px;
                            text-decoration: none;
                            color: white;
                            background-color: gray;
                            border-radius: 5px;
                            background-color: #0B4F6C;
                        }

                        .modal-content a:hover {
                            background-color: darkgray;
                        }

                        /* Estilos para los botones alineados */
                        .button-container {
                            display: flex;
                            justify-content: space-between;
                            /* Distribuye el espacio entre los botones */
                            width: 100%;
                        }
                    </style>
                </head>

                <body>
                    <div class="container">
                        <h1>Listado Vacaciones</h1>

                        <div class="button-container">

                            <!-- Botón para abrir el segundo modal (resto de los botones) -->
                            <button class="btn" onclick="abrirModal('modal2')">
                                <i class="bi bi-journal-medical"></i>
                            </button>
                        </div>


                        <!-- Modal 2 con el resto de los botones -->
                        <div id="modal2" class="modal">
                            <div class="modal-content">
                                <span class="close" onclick="cerrarModal('modal2')">&times;</span>
                                <h3>Detalles Planilla</h3>
                                <a href="EditarVacaciones.php">Editar Vacaciones</a>
                                <a href="Consulta_permisos_vacaciones.php">Historial</a>
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
                                    <th>Dias Tomados</th>
                                    <th>Dias Restantes</th>
                                    <th>Estado</th>
                                    <th>Detalles</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Mostrar los resultados de la consulta
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>
                                <td>" . $row['Nombre'] . "</td>
                                <td>" . $row['Apellido'] . "</td>
                                <td>" . $row['Departamento'] . "</td>
                                <td>" . $row['fecha_inicio'] . "</td>
                                <td>" . $row['diasTomado'] . "</td>
                                <td>" . $row['DiasRestantes']. "</td>
                                <td>" . $row['descripcion'] . "</td>
                                <td><a class='btn btn-success' style='font-size: 2.5rem;' href='detalleVacacion.php?id=" . $row['id_vacacion'] . "' >
                                    <i class='bi bi-file-earmark-person'></i> 
                                </a></td>
                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='9' class='no-records'>No se encontraron registros.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
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

</html>
