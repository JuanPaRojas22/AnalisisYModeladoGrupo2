<?php

session_start();
require 'template.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

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



<!-- **********************************************************************************************************************************************************
      MAIN CONTENT
      *********************************************************************************************************************************************************** -->
<!--main content start-->
<!-- Tabla de usuarios -->
<section id="main-content">
    <section class="wrapper site-min-height">
        <div class="container">
            <h1>Listado de Usuarios</h1>

            <div class="container" style="display: flex; justify-content: center; align-items: center;">
                <div class="row">
                    <!-- Formulario 1 con select -->
                    <form action="generar_reporte.php" method="GET" style="margin-bottom: 20px;">
                        <div style="display: flex; align-items: center; gap: 10px;"> <!-- Agrupa select y botón -->
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
                            <button class="btn" style="font-size: 2rem; color: black;">
                                <i class="bi bi-filetype-pdf"></i>
                            </button>
                        </div>
                    </form>

                    <!-- Formulario 2 con select -->
                    <form method="GET" action="MostrarUsuarios.php">
                        <div style="display: flex; align-items: center; gap: 10px;"> <!-- Agrupa select y botón -->
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
                            <button class="btn" style="font-size: 1.5rem; color: black;">
                                <i class="bi bi-funnel-fill"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Formulario con select alineado a la derecha -->


        </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width:8%">Departamento</th>
                    <th style="width:8%">Rol</th>
                    <th style="width:8%">Nombre</th>
                    <th style="width:8%">Apellido</th>
                    <th style="width:5%">Ocupacion</th>
                    <th style="width:8%">Nacionalidad</th>
                    <th style="width:8%">Correo Electrónico</th>
                    <th style="width:10%">Teléfono</th>
                    <th style="width:10%">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Recorre los usuarios y los muestra en la tabla
                foreach ($users as $user) {
                    echo "<tr>";
                    echo "<td><b>{$user['departamento_nombre']}</b></td>";
                    echo "<td><b>{$user['rol_nombre']}</b></td>";
                    echo "<td><b>{$user['nombre']}</b></td>";
                    echo "<td><b>{$user['apellido']}</b></td>";
                    echo "<td><b>{$user['Nombre_Ocupacion']}</b></td>";
                    echo "<td><b>{$user['Nombre_Pais']}</b></td>";
                    echo "<td><b>{$user['correo_electronico']}</b></td>";
                    echo "<td><b>{$user['numero_telefonico']}</b></td>";
                    echo "<td>";
                    echo "<div class='d-flex gap-2 justify-content-center'>  
                    <a href='editar.php?id={$user['id_usuario']}' class='btn '>
                        <i class='bi bi-pencil-square'></i> 
                    </a>
                    <a href='detalle.php?id={$user['id_usuario']}' class='btn '>
                        <i class='bi bi-file-earmark-person'></i> 
                    </a>
                    <a href='eliminar.php?id={$user['id_usuario']}' class='btn ' onclick='return confirm(\"¿Estás seguro de eliminar este usuario?\")'>
                        <i class='bi bi-trash'></i>
                    </a>
                </div>";



                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </section>





    <!--main content end-->
    <!--footer start-->

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
        background-color: #f7f7f7;
        margin: 0;
        padding: 0;
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
    border-color: #a88c4a;
}

select:focus {
    outline: none;
    border-color: #805d24;
    box-shadow: 0 0 5px rgba(200, 150, 60, 0.6);
}

    .container {
        width: 80%;
        margin: 50px auto;
        padding: 20px;
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.8);
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
        background-color: #c9aa5f;
        color: white;
        padding: 10px 20px;
        font-size: 15px;
        font-weight: bold;
        text-align: center;
        text-decoration: none;
        border-radius: 5px;
        margin-bottom: 20px;
        transition: background-color 0.3s;
    }



    .btn:hover {
        background-color: #c9aa5f;
    }

    .btn:active {
        background-color: #c9aa5f;
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
        background-color: #c9aa5f;
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
        background-color: #c9aa5f;
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