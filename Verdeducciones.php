<?php
require 'conexion.php';
session_start();
require 'template.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Dashboard">
    <meta name="keyword" content="Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">

    <title>Ver Deducciones</title>

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
        td, div {
            color: black !important;
        }
    </style>
</head>

<body>

    
        <!-- **********************************************************************************************************************************************************
      MAIN CONTENT
      *********************************************************************************************************************************************************** -->
        <!--main content start-->
        <section id="main-content">
            <section class="wrapper site-min-height">
                
                <!-- /MAIN CONTENT -->
                <?php
                // Obtener el ID de usuario logueado
                $id_usuario_logueado = $_SESSION['id_usuario'];

                // Obtener el ID de usuario seleccionado (si existe)
                $id_usuario_seleccionado = isset($_POST['id_usuario']) ? $_POST['id_usuario'] : null;

                // Si no se selecciona un usuario, mostramos todos
                if ($id_usuario_seleccionado === null || $id_usuario_seleccionado === '') {
                    $sql_deducciones = "
        SELECT 
            d.id_deduccion, 
            d.id_usuario, 
            u.nombre, 
            u.apellido, 
            d.razon, 
            d.deudor, 
            d.concepto, 
            d.lugar, 
            d.deuda_total, 
            d.monto_mensual
            d.aportes, 
            d.saldo_pendiente, 
            d.saldo_pendiente_dolares, 
            d.fechacreacion
        FROM 
            Deducciones d
        INNER JOIN 
            Usuario u ON d.id_usuario = u.id_usuario";
                } else {
                    // Si se selecciona un usuario específico
                    $sql_deducciones = "
        SELECT 
            d.id_deduccion, 
            d.id_usuario, 
            u.nombre, 
            u.apellido, 
            d.razon, 
            d.deudor, 
            d.concepto, 
            d.lugar, 
            d.deuda_total, 
            d.aportes, 
            d.saldo_pendiente, 
            d.saldo_pendiente_dolares, 
            d.fechacreacion
        FROM 
            Deducciones d
        INNER JOIN 
            Usuario u ON d.id_usuario = u.id_usuario
        WHERE 
            d.id_usuario = ?";
                }

                // Preparar la consulta
                $stmt_deducciones = $conn->prepare($sql_deducciones);

                // Si se selecciona un usuario específico, bind_param para el id_usuario
                if ($id_usuario_seleccionado !== null && $id_usuario_seleccionado !== '') {
                    $stmt_deducciones->bind_param("i", $id_usuario_seleccionado);
                }

                // Ejecutar la consulta
                $stmt_deducciones->execute();
                $result_deducciones = $stmt_deducciones->get_result();

                // Obtener todos los usuarios para el dropdown
                $sql_usuarios = "SELECT id_usuario, nombre, apellido FROM Usuario";
                $result_usuarios = $conn->query($sql_usuarios);
                ?>

                <!DOCTYPE html>
                <html lang="es">

                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Listado de Deducciones</title>
                    <style>
    /* Body and overall layout */
    body {
        font-family: 'Ruda', sans-serif;
        background-color: #f5f6fa;
        margin: 0;
        padding: 0;
    }

    /* Main content section */
    .container {
        width: 80%;
        margin: 40px auto;
        padding: 20px;
        background-color: white;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
    }

    /* Form styling */
    .form-label {
        font-weight: bold;
        margin-bottom: 10px;
    }

    .form-control {
        padding: 10px;
        margin-bottom: 15px;
        width: 100%;
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    .form-select {
        padding: 10px;
        margin-bottom: 15px;
        width: 100%;
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    /* Buttons styling */
    .btn {
        padding: 12px 20px;
        font-size: 16px;
        font-weight: bold;
        text-align: center;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 10px;
        display: inline-block;
        text-decoration: none;
    }

    .btn-primary {
        background-color: #007bff;
        color: white;
    }

    .btn-primary:hover {
        background-color: #0056b3;
    }

    .btn-secondary {
        background-color: #28a745;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #218838;
    }

    /* Table styling */
    .table {
        width: 100%;
        margin-top: 30px;
        border-collapse: collapse;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    }

    .table th, .table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    .table th {
        background-color: #116B67;
        color: white;
    }

    .table tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .table tr:hover {
        background-color: #f1f1f1;
    }

    /* Responsive table */
    .table-container {
        overflow-x: auto;
        margin-top: 20px;
    }
    .filter-container {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 20px;
    }

    .filter-container select,
    .filter-container button {
        margin: 0 10px;
        padding: 10px;
        font-size: 16px;
    }
</style>
                </head>

                <body>
    <div class="container">
        <div class="title-container">
            <h2 class="fw-bold">Listado de Deducciones</h2>
        </div>

        <div class="container">
    <div class="filter-container">
        <form method="POST" action="">
            <label for="id_usuario">Seleccionar usuario:</label>
            <select name="id_usuario" id="id_usuario">
                <option value="">Ver todos</option>
                <?php while ($row_usuario = $result_usuarios->fetch_assoc()) { ?>
                    <option value="<?= $row_usuario['id_usuario']; ?>" 
                        <?= ($id_usuario_seleccionado == $row_usuario['id_usuario']) ? 'selected' : ''; ?>>
                        <?= $row_usuario['nombre'] . " " . $row_usuario['apellido']; ?>
                    </option>
                <?php } ?>
            </select>
            <button type="submit" style="background-color: #0B4F6C; color: white; padding: 10px 20px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;">Filtrar</button>
            </form>
    </div>
</div>

        <!-- Mostrar los resultados -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        
                        <th>Nombre del Usuario</th>
                        <th>Razón</th>
                        <th>Deudor</th>
                        <th>Concepto</th>
                        <th>Lugar</th>
                        <th>Monto Mensual</th>
                        <th>Aportes</th>
                        <th>Saldo Pendiente</th>
                        <th>Saldo Pendiente (USD)</th>
                        <th>Fecha de Creación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result_deducciones->num_rows > 0) {
                        while ($row_deduccion = $result_deducciones->fetch_assoc()) {
                            echo "<tr>";
                            
                            echo "<td>" . $row_deduccion['nombre'] . " " . $row_deduccion['apellido'] . "</td>";
                            echo "<td>" . $row_deduccion['razon'] . "</td>";
                            echo "<td>" . $row_deduccion['deudor'] . "</td>";
                            echo "<td>" . $row_deduccion['concepto'] . "</td>";
                            echo "<td>" . $row_deduccion['lugar'] . "</td>";
                            echo "<td>" . $row_deduccion['monto_mensual'] . "</td>";
                            echo "<td>" . $row_deduccion['aportes'] . "</td>";
                            echo "<td>" . $row_deduccion['saldo_pendiente'] . "</td>";
                            echo "<td>" . $row_deduccion['saldo_pendiente_dolares'] . "</td>";
                            echo "<td>" . $row_deduccion['fechacreacion'] . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='11'>No hay deducciones para este usuario.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="form-group text-center mt-3">
            <a href="VerPlanilla.php" class="btn btn-secondary">Volver</a>
        </div>
    </div>
</body>

                </html>

            </section>
        </section>
        <!--main content end-->

        

</body>
<style>
  body {
    font-family: 'Arial', sans-serif;
    background-color: #f4f4f9;
    margin: 0;
    padding: 20px;
}
#id_usuario {
    font-size: 13px; /* Aumenta el tamaño de la fuente */
    padding: 13px 13px; /* Aumenta el espacio interno */
    width: auto; /* Ajusta el tamaño automáticamente */
    height: 50px; /* Aumenta la altura del campo */

}


.container {
    max-width: 1000px;
    margin: auto;
}

.title-container {
    text-align: center;
    margin-top: 50px;
    margin-bottom: 30px;
}

.card {
    border-radius: 10px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    padding: 20px;
    text-align: center; /* Centra el contenido dentro de la card */
}

.form-group {
    display: flex;
    justify-content: center;  /* Centra el contenido */
    align-items: center;  /* Alinea verticalmente */
    gap: 10px;  /* Espacio entre el select y el botón */
    width: 100%;
}

.form-select, .btn {
    font-size: 16px;
    padding: 10px;
    width: 30%;  /* Ajusta el tamaño según lo necesario */
}

.btn {
    background-color: #0B4F6C;
    color: white;
    border: none;
    cursor: pointer;
    font-weight: bold;
    border-radius: 5px;
}

.btn:hover {
    background-color: #0a3c2c;
}
.table-container {
    overflow-x: auto;
    margin-top: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    background-color: white;
    margin-top: 20px;
    border-radius: 8px;
    overflow: hidden;
}

th, td {
    padding: 12px;
    text-align: center;
    border: 1px solid #ddd;
}

th {
    background-color: #116B67;
    color: white;
}

td {
    background-color: #f9f9f9;
}

tr:nth-child(even) td {
    background-color: #f1f1f1;
}

tr:hover {
    background-color: #e9f7fc;
}

.form-group {
    margin-top: 30px;
    text-align: center;
}

.btn-secondary {
    padding: 10px 20px;
    background-color: #0B4F6C;
    color: white;
    border-radius: 5px;
    text-decoration: none;
    transition: background-color 0.3s ease;
}

.btn-secondary:hover {
    background-color: #0B4F6C;
}


    .filter-container {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 20px;
    }

    .filter-container select,
    .filter-container button {
        margin: 0 10px;
        padding: 10px;
        font-size: 16px;
    }
</style>

</html>