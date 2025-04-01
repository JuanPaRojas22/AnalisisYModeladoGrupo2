<?php
ob_start(); // Inicia el búfer de salida para evitar que se envíen cabeceras prematuramente
session_start();
require "template.php";


header('Content-Type: text/html; charset=utf-8');


require_once __DIR__ . '/Impl/UsuarioDAOSImpl.php';

// Instancia el DAO
$UsuarioDAO = new UsuarioDAOSImpl();

// Verifica si el parámetro 'id' está presente en la URL
if (isset($_GET['id'])) {
    $id_usuario = $_GET['id'];

    // Obtiene los detalles del usuario por id
    $user = $UsuarioDAO->getUserById($id_usuario);

    // Si el usuario no existe
    if (!$user) {
        echo "Usuario no encontrado.";
        exit;
    }
} else {
    echo "ID de usuario no proporcionado.";
    exit;
}
?>

<!DOCTYPE html>


<body>

   
        <!-- **********************************************************************************************************************************************************
      MAIN CONTENT
      *********************************************************************************************************************************************************** -->
        <!--main content start-->
        <section id="main-content">
            <section class="wrapper site-min-height">
                <!-- Botón para generar el PDF -->
                <div class="btn-container-wrapper">
                    <form method="get" action="MostrarUsuarios.php" accept-charset="UTF-8">
                        <input type="hidden" name="id_usuario" value="<?php echo $user['id_usuario']; ?>">
                        <button type="submit" class="btn-container"><i class="bi bi-arrow-return-left"></i></button>
                    </form>

                    <form method="get" action="generar_reporte_usuario.php" accept-charset="UTF-8">
                        <input type="hidden" name="id_usuario" value="<?php echo $user['id_usuario']; ?>">
                        <button type="submit" class="btn-container"><i class="bi bi-box-arrow-down"></i></button>
                    </form>
                </div>
                <table class="user-details">

                    <h1 text-center>Información del Empleado</h1>

                    <div class="user-img">
                        <?php if (!empty($user['direccion_imagen'])): ?>
                            <img src="<?php echo htmlspecialchars($user['direccion_imagen']); ?>" alt="Imagen del usuario">
                        <?php else: ?>
                            <p>No hay imagen disponible</p>
                        <?php endif; ?>


                        <tr>
                            <th>Nombre</th>
                            <td><?php echo htmlspecialchars($user['nombre']); ?></td>
                        </tr>
                        <tr>
                            <th>Apellido</th>
                            <td><?php echo htmlspecialchars($user['apellido']); ?></td>
                        </tr>
                        <tr>
                            <th>Sexo</th>
                            <td><?php echo htmlspecialchars($user['sexo']); ?></td>
                        </tr>
                        <tr>
                            <th>Fecha de Nacimiento</th>
                            <td><?php echo htmlspecialchars($user['fecha_nacimiento']); ?></td>
                        </tr>
                        <tr>
                            <th>Estado Civil</th>
                            <td><?php echo htmlspecialchars($user['estado_civil']); ?></td>
                        </tr>
                        <tr>
                            <th>Ocupación</th>
                            <td><?php echo htmlspecialchars($user['id_ocupacion']); ?></td>
                        </tr>
                        <tr>
                            <th>Correo Electrónico</th>
                            <td><?php echo htmlspecialchars($user['correo_electronico']); ?></td>
                        </tr>
                        <tr>
                            <th>Teléfono</th>
                            <td><?php echo htmlspecialchars($user['numero_telefonico']); ?></td>
                        </tr>
                        <tr>
                            <th>Estado</th>
                            <td><?php echo htmlspecialchars($user['estado']); ?></td>
                        </tr>
                        <tr>
                            <th>Departamento</th>
                            <td><?php echo htmlspecialchars($user['departamento_nombre']); ?></td>
                        </tr>
                        <tr>
                            <th>Rol</th>
                            <td><?php echo htmlspecialchars($user['rol_nombre']); ?></td>
                        </tr>
                        <tr>
                            <th>Fecha de Ingreso</th>
                            <td><?php echo htmlspecialchars($user['fecha_ingreso']); ?></td>
                        </tr>
                </table>

                <!-- Enlace para volver a la lista de usuarios -->

            </section>
        </section>

        <!-- Estilos CSS -->
        <style>
            .profile-container {
                margin-left: 250px;
                padding: 60px;
            }

            body {
                font-family: 'Ruda', sans-serif;
                background-color: #f7f7f7;
                margin: 0;
                padding: 0;
            }

            .container {
                width: 80%;
                max-width: 2000px;
                margin: 50px auto 200px 250px;
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

            .user-img {
                display: flex;
                justify-content: center;
                margin-bottom: 20px;
            }

            .user-img img {
                width: 120px;
                height: 120px;
                border-radius: 50%;
                object-fit: cover;
                border: 2px solid #c9aa5f;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.6);
            }

            th,
            td {
                padding: 12px;
                text-align: center;
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

            .btn-container-wrapper {
                display: flex;
                /* Flexbox para alinear los formularios horizontalmente */
                gap: 90%;
                /* Espaciado entre los botones */
                margin-top: 20px;

            }

            .btn-container {
                background-color: #c9aa5f;
                color: white;
                padding: 10px;
                font-size: 25px;
                font-weight: bold;
                text-align: center;
                text-decoration: none;
                border-radius: 5px;
                transition: background-color 0.3s;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.6);

            }

            .btn {
                display: inline-block;
                background-color: #c9aa5f;
                color: white;
                padding: 10px;
                font-size: 25px;
                font-weight: bold;
                text-align: center;
                text-decoration: none;
                border-radius: 5px;
                transition: background-color 0.3s;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.6);
                margin-top: 20px;
            }

            .btn:hover {
                background-color: darkgray;
            }
        </style>
        <!--footer end-->
    </section>
</body>

</html>
