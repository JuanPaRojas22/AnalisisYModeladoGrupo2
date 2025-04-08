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
    <!--main content start-->
    <section id="main-content">
        <section class="wrapper site-min-height">
            <!-- Botón para generar el PDF -->


            <div class="container">
                <h1>Información del Empleado</h1>
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
                <div class="user-img">
                    <?php if (!empty($user['direccion_imagen'])): ?>
                        <img src="<?php echo htmlspecialchars($user['direccion_imagen']); ?>" alt="Imagen del usuario">
                    <?php else: ?>
                        <p>No hay imagen disponible</p>
                    <?php endif; ?>
                </div>

                <table class="user-details">
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
            </div>

        </section>
    </section>

    <style>
        body {
            font-family: 'Ruda', sans-serif;
            background-color: #f7f7f7;  /* Blanco cremoso */
            /* Gris suave */
            margin: 0;
            padding: 0;
        }

        .container {
            width: 50%;
            max-width: 40%;
            /* Limitar el ancho máximo */
            margin: 5px auto;
            padding: 20px;
            background-color: #f7f7f7;  /* Blanco cremoso */
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.6);
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-weight: bold;
        }

        .user-img {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .user-img img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #c9aa5f;
        }

        table {
            width: 50%;
            border-collapse: separate;
            /* Cambiar a 'separate' para que los bordes se muestren correctamente */
            border-spacing: 0;
            /* Eliminar el espacio entre celdas */
            margin-top: 20px;
            margin-left: 25%;
            border-radius: 10px;
            /* Borde redondeado en la tabla */
            overflow: hidden;
            /* Para que los bordes redondeados se vean en las celdas */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.6);
            /* Agregar sombra ligera */
        }

        th,
        td {
            padding: 8px 8px;
            /* Reducir el espacio dentro de las celdas */
            text-align: center;
            font-size: 12px;
            /* Reducir el tamaño de la fuente */
            color: #fff;
            border-bottom: 1px solid #ddd;

        }

        th {
            background-color: #bea66a;
            color: #fff;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        td {
            background-color: #bea66a;
        }

        .btn-container-wrapper {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .btn-container {
            background-color: #c9aa5f;
            color: white;
            padding: 8px 12px;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .btn-container:hover {}
    </style>


    <!--footer end-->
    </section>
</body>

</html>