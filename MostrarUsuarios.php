<?php

session_start();
// Verificar si el usuario está logueado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
// Verificar si el usuario es administrador (id_rol == 2)
if ($_SESSION['id_rol'] == 3 OR $_SESSION['id_rol'] == 1) { // Verificar si el usuario es un empleado
    header("Location: index.php"); // Redirigir a la página de inicio si no es administrador
    exit;
}
require 'template.php';



// Incluye el archivo donde tienes definida la clase UsuarioDAOSImpl
require_once __DIR__ . '/Impl/UsuarioDAOSImpl.php';

// Instancia el DAO
$UsuarioDAO = new UsuarioDAOSImpl();

// Obtiene todos los usuarios
$id_departamento = isset($_GET['id_departamento']) ? $_GET['id_departamento'] : null;

$departmento = $UsuarioDAO->getAllDepartments();


// Obtiene los usuarios filtrados por departamento si es necesario
if ($id_departamento == 'all') {
    // Obtiene todos los usuarios sin filtrar
    $users = $UsuarioDAO->getAllUsers();
} elseif ($id_departamento) {
    // Si se seleccionó un departamento específico, obtiene los usuarios filtrados
    $users = $UsuarioDAO->getUsersByDepartment($id_departamento);
} else {
    // Si no se seleccionó ningún filtro, muestra todos los usuarios
    $users = $UsuarioDAO->getAllUsers();
}

?>


<!DOCTYPE html>
<html lang="en">

<head>


  <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>


<!-- **********************************************************************************************************************************************************
      MAIN CONTENT
      *********************************************************************************************************************************************************** -->
<!--main content start-->
<!-- Tabla de usuarios -->
<section id="main-content">
    <section class="wrapper site-min-height">
        <div class="container">
            <h1>Listado de Usuarios</h1>

            <div class="specific-container"
                style="display: flex; flex-direction: column; gap: 20px; width: 100%; align-items: center;">
                <div class="row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                     <button onclick="window.location.href='registroEmpleado.php'">
                                Registrar Usuario
                     </button>
                </div>
                <!-- Añadido flex-direction: column para apilar los formularios -->
                <div class="row">
                    <!-- Formulario 2 con select -->
                    <form method="GET" action="MostrarUsuarios.php">
                        <div style="display: flex; justify-content: center; align-items: center; gap: 10px;">
                            <select name="id_departamento" id="id_departamento"
                                style="font-size: 14px; width: 240px; height: 40px;">
                                <option value="all">Seleccione un departamento</option>
                                <?php
                                foreach ($departmento as $department) {
                                    $selected = (isset($id_departamento) && $id_departamento == $department['id_departamento']) ? 'selected' : '';
                                    echo "<option value='{$department['id_departamento']}' {$selected}>{$department['Nombre']}</option>";
                                }
                                ?>
                            </select>
                            <button class="btn" style="font-size: 1.5rem; color: #f1f1f1;">
                                <i class="bi bi-funnel-fill"></i>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="row">
                    <!-- Formulario 1 con select -->
                    <form action="generar_reporte.php" method="GET" style="margin-bottom: 20px;">
                        <div style="display: flex; justify-content: center; align-items: center; gap: 10px;">
                            <select class="form-select" name="id_departamento" id="id_departamento"
                                style="font-size: 14px; width: 240px; height: 40px;" required>
                                <option>Seleccione un departamento</option>
                                <?php
                                foreach ($departmento as $department) {
                                    $selected = (isset($id_departamento) && $id_departamento == $department['id_departamento']) ? 'selected' : '';
                                    echo "<option value='{$department['id_departamento']}' {$selected}>{$department['Nombre']}</option>";
                                }
                                ?>
                            </select>
                            <button class="btn" style="font-size: 2rem; color: #f1f1f1;">
                                <i class="bi bi-filetype-pdf"></i>
                            </button>
                        </div>
                    </form>
                </div>


            </div>
        </div>




        <!-- Contenedor principal con clase row -->
        <div class="row px-8">
            <?php foreach ($users as $user): ?>
                <!-- Columna para cada tarjeta -->
                <div class="col-12 col-sm-6 col-md-8 col-lg-4 mb-4">
                    <div class="card shadow-lg border rounded-25" style="background-color: #fdfdfd;">
                        <div class="card-body">
                            <h5 class="card-title ">
                                <?= htmlspecialchars($user['nombre']) . " " . htmlspecialchars($user['apellido']) ?>
                            </h5>
                            <p class="card-text mb-2">
                                <strong>Departamento:</strong> <?= htmlspecialchars($user['departamento_nombre']) ?><br>
                                <strong>Rol:</strong> <?= htmlspecialchars($user['rol_nombre']) ?><br>
                                <strong>Ocupación:</strong> <?= htmlspecialchars($user['Nombre_Ocupacion']) ?><br>
                                <strong>Nacionalidad:</strong> <?= htmlspecialchars($user['Nombre_Pais']) ?><br>
                                <strong>Correo:</strong> <?= htmlspecialchars($user['correo_electronico']) ?><br>
                                <strong>Teléfono:</strong> <?= htmlspecialchars($user['numero_telefonico']) ?>
                            </p>
                            <!-- Botones dentro del card-body -->
                            <div class="d-flex justify-content-center gap-4">
                                <a href="profileUser.php?id=<?= $user['id_usuario'] ?>"
                                    class="btn btn-outline-primary btn-sm rounded-pill">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="detalle.php?id=<?= $user['id_usuario'] ?>"
                                    class="btn btn-outline-info btn-sm rounded-pill">
                                    <i class="bi bi-file-earmark-person"></i>
                                </a>
                                <a href="eliminar.php?id=<?= $user['id_usuario'] ?>"
                                    class="btn btn-outline-danger btn-sm rounded-pill"
                                    onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>


    </section>

</section>


<script>
    $(document).ready(function () {
        $.ajax({
            url: '/Impl/UsuarioDAOSImpl.php', // El archivo PHP que recupera los usuarios
            type: 'GET',
            success: function (response) {
                var users = JSON.parse(response);
                var tableContent = '';
                users.forEach(function (user) {
                    tableContent += `<tr>
              
                        </tr>`;
                });
                $('#userTable').html(tableContent); // Insertar las filas en la tabla
            },
            error: function (xhr, status, error) {
                console.error('Error al cargar los usuarios:', error);
            }
        });
    });
</script>





</body>
<style>
    body {
        font-family: 'Ruda', sans-serif;
        background-color: #f7f7f7;  /* Blanco cremoso */
        margin: 0;
        padding: 0;
    }

    .card-body {
        padding: 27px;
        margin-bottom: 0;
        background-color: #f7f7f7;  /* Blanco cremoso */

        /* Eliminar margen inferior */
        padding-bottom: 0;
        /* Eliminar padding inferior */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.6);
        color: black;
    }

    .card-footer {
        margin-top: 0;
        /* Si tienes una sección card-footer, asegúrate de que no tenga márgenes */
    }

    select {
        width: 70%;
        padding: 10px;
        font-size: 16px;
        border: 2px solid rgb(15, 15, 15);
        border-radius: 5px;
        background: #f9f9f9;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        color: black;
    }

    select:hover {
        border-color: #106469;
    }

    select:focus {
        outline: none;
        border-color: #106469;
        box-shadow: 106469;
    }

    .container {
        display: flex;
        flex-direction: column;
        background-color: #f7f7f7;  /* Blanco cremoso */

        justify-content: flex-start;
        /* Alinea hacia la parte superior */
        align-items: center;
        /* Centra los elementos horizontalmente */
        padding: 10px;
        max-width: 100%;
    }


    .row {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 30px;
       
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
    h5{
        color: black;
        font-weight: bold;
    }

    .btn {
        display: inline-block;
                        background-color: #0B4F6C;
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



    .btn:hover {
        background-color: #0B4F6C;
    }

    .btn:active {
        background-color: #0D566B;
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
        background-color: #f7f7f7;  /* Blanco cremoso */
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
        background-color: #106469;
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

    .close-button {
        border: none;
        display: inline-block;
        padding: 8px 16px;
        vertical-align: middle;
        overflow: hidden;
        text-decoration: none;
        color: inherit;
        background-color: inherit;
        text-align: center;
        cursor: pointer;
        white-space: nowrap
    }

    .topright {
        position: absolute;
        right: 0;
        top: 0
    }
</style>

</html>
