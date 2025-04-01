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

<head>

        <!-- **********************************************************************************************************************************************************
      MAIN CONTENT
      *********************************************************************************************************************************************************** -->
        <!--main content start-->
        <section id="main-content">
                <!-- Tabla de usuarios -->
                <div class="container">
                    <h1>Listado de Usuarios</h1>
                    <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                        <div style="display: flex; justify-content: space-between; gap: 10px; width: 100%;">

                            <!-- Formulario con select alineado a la izquierda -->
                            <form action="generar_reporte.php" method="GET"
                                style=" gap: 10px; flex: 2; margin-left: -800px;">
                                <div style="display: flex; align-items: center;">
                                    <select class="form-select" name="id_departamento" id="id_departamento"
                                        style="font-size: 14px; width: 240px; height: 20px;" require>
                                        <option>Seleccione un departamento</option>
                                        <?php
                                        foreach ($departmento as $department) {
                                            $selected = (isset($id_departamento) && $id_departamento == $department['id_departamento']) ? 'selected' : '';
                                            echo "<option value='{$department['id_departamento']}' {$selected}>{$department['Nombre']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div style="display: flex;">
                                    <button class="btn-select" style="font-size: 2rem; color: black;">
                                        <i class="bi bi-filetype-pdf"></i>
                                    </button>
                                </div>
                            </form>

                            <!-- Formulario con select alineado a la derecha -->
                            <form method="GET" action="MostrarUsuarios.php"
                                style="display: flex; align-items: center; margin-left: 1000px;">
                                <div style="display: flex; align-items: center;">
                                    <select name="id_departamento" id="id_departamento"
                                        style="font-size: 14px; width: 240px; height: 20px ;">
                                        <option value="all">Seleccione un departamento</option>
                                        <?php
                                        foreach ($departmento as $department) {
                                            $selected = (isset($id_departamento) && $id_departamento == $department['id_departamento']) ? 'selected' : '';
                                            echo "<option value='{$department['id_departamento']}' {$selected}>{$department['Nombre']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div style="display: flex; align-items: center;">
                                    <button class="btn-select" style="font-size: 1.5rem; color: black;">
                                        <i class="bi bi-funnel-fill"></i>
                                    </button>
                                </div>
                            </form>

                        </div>
                </div>
                <thead>
                    <tr>
                        <th style="width:10%">Departamento</th>
                        <th style="width:10%">Rol</th>
                        <th style="width:10%">Nombre</th>
                        <th style="width:15%">Apellido</th>
                        <th style="width:10%">Ocupacion</th>
                        <th style="width:10%">Nacionalidad</th>
                        <th style="width:20%">Correo Electrónico</th>
                        <th style="width:11%">Teléfono</th>
                        <th style="width:10%">Imagen</th>
                        <th style="width:10%">Sexo</th>
                        <th style="width:10%">Estado</th>
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
                        //Carga la imagen del usuario
                        echo "<td><img src='{$user['direccion_imagen']}' alt='Imagen' style='width: 40px; height: 40px;'></td>";
                        echo "<td><b>{$user['sexo']}</b></td>";
                        echo "<td><b>{$user['estado']}</b></td>";
                        echo "<td>";
                        echo "<div class=' gap-2'>  
                                <a href='editar.php?id={$user['id_usuario']}' class='btn btn-primary' style='font-size: 2.5rem;'>
                                    <i class='bi bi-pencil-square'></i> 
                                </a>
                                <a href='detalle.php?id={$user['id_usuario']}' class='btn btn-success' style='font-size: 2.5rem;'>
                                    <i class='bi bi-file-earmark-person'></i> 
                                </a>
                                <a href='eliminar.php?id={$user['id_usuario']}' class='btn btn-danger' style='font-size: 2.5rem;' onclick='return confirm(\"¿Estás seguro de eliminar este usuario?\")'>
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
        </section><!-- /MAIN CONTENT -->




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

    .container {
        width: 100%;
        margin: 10px auto;
        padding: 20px;
        background-color: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.6);
    }

    h1 {
        text-align: center;
        color: #333;
        margin-bottom: 50px;
        margin-right: 10%;
        font-weight: bold;
    }


    .btn {
        background-color: #c9aa5f;
        color: white;
        padding: 5px;
        text-align: center;
        text-decoration: none;
        border-radius: 10px;
        transition: background-color 0.3s;
    }
.select{
    margin-right: 20px;
}
    .btn-select {
        background-color: #c9aa5f;
        color: white;
        padding: 10px 20px;
        font-size: 25px;
        text-align: center;
        text-decoration: none;
        border-radius: 10px;
        transition: background-color 0.3s;
    }

    .btn-select:hover {
        background-color: #b8a15a;
    }

    .btn-select:active {
        background-color: #c9aa5f;
    }

    table {
        width: 50%;
        border-collapse: collapse;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.8);
        margin: 0 auto; /* Centra la tabla en la página */
        border-collapse: collapse; /* Opcional: mejora la visualización */

    }


    th,
    td {
        padding: 2px;
        min-width: 300px;
        color: gray;
        font-weight: bold;
        max-width: 600px;
        word-wrap: break-word;
        white-space: normal;
        font-size: 13px;
        margin: 10px auto;
        text-align: center;

        vertical-align: middle;



    }

    select {
    width: 100%;
        color: black;
    font-size: 16px;
    border: 2px solidrgb(15, 15, 15);
    border-radius: 5px;
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

    th {
        background-color: #c9aa5f;
        color: #fff;
        text-align: center;

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
</style>

</html>