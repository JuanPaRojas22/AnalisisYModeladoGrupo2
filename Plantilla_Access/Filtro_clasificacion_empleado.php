<?php
session_start();
require 'conexion.php';
require 'template.php';

// Verificar si se han enviado los filtros
if (isset($_POST['filtrar'])) {
    $usuario = isset($_POST['usuario']) && !empty($_POST['usuario']) ? $_POST['usuario'] : null;
    $clasificacion = isset($_POST['clasificacion']) && !empty($_POST['clasificacion']) ? $_POST['clasificacion'] : null;

    $query = "SELECT 
                u.nombre,
                d.nombre AS departamento,
                p.salario_base,     
                p.salario_neto,
                COALESCE(GROUP_CONCAT(DISTINCT te.descripcion SEPARATOR ', '), 'Sin clasificación') AS clasificaciones
            FROM planilla p
            JOIN Usuario u ON p.id_usuario = u.id_usuario
            JOIN departamento d ON u.id_departamento = d.id_departamento
            LEFT JOIN empleado_tipo_empleado ete ON p.id_usuario = ete.id_empleado
            LEFT JOIN tipo_empleado te ON ete.id_tipo_empleado = te.id_tipo_empleado
            WHERE 1=1"; // Para permitir agregar filtros dinámicos

    // Filtro por usuario
    if ($usuario) {
        $query .= " AND u.id_usuario = '$usuario'";
    }

    // Filtro por clasificación (corregido)
    if ($clasificacion) {
        $query .= " AND te.id_tipo_empleado = '$clasificacion'";
    }

    $query .= " GROUP BY u.nombre, d.nombre, p.salario_base, p.salario_neto ORDER BY u.nombre DESC";

    // Debug: Mostrar la consulta generada
    //echo "<pre>" . $query . "</pre>";

    // Ejecutar la consulta
    $result = mysqli_query($conn, $query);

    // Verificar si hay resultados
    if ($result && mysqli_num_rows($result) > 0) {
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
    } else {
        $data = null;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filtrar Clasificación Empleados</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Filtrar Clasificación Empleados</h1>

        <!-- Formulario de filtros -->
        <form action="Filtro_clasificacion_empleado.php" method="post" class="filter-form">
            <label for="usuario">Usuario:</label>
            <select name="usuario" id="usuario">
                <option value="">Selecciona un Usuario</option>
                <?php
                // Cargar usuarios desde la base de datos
                $query = "SELECT id_usuario, nombre FROM usuario";
                $result = mysqli_query($conn, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<option value='" . $row['id_usuario'] . "'>" . $row['nombre'] . "</option>";
                }
                ?>
            </select>

            <label for="clasificacion">Clasificación:</label>
            <select name="clasificacion" id="clasificacion">
                <option value="">Selecciona una Clasificación</option>
                <?php
                // Cargar clasificaciones desde la base de datos
                $query = "SELECT id_tipo_empleado, descripcion FROM tipo_empleado";
                $result = mysqli_query($conn, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<option value='" . $row['id_tipo_empleado'] . "'>" . $row['descripcion'] . "</option>";
                }
                ?>
            </select>

            <form action="#" method="post">
         <div class="button-group">
        <!-- Botón Filtrar -->
        <a href="VerPlanilla.php" class="btn btn-secondary">
            <i ></i> Devolver
        </a>
        <button class="btn" type="submit" name="filtrar">
            <i class="bi bi-funnel"></i> Filtrar
        </button>

        <!-- Botón Devolver -->
        
    </div>
    </form>

    <?php if (!empty($data)): ?>
        <form action="reporte_clasificacion_empleado.php" method="post">
            <input type="hidden" name="usuario" value="<?php echo $usuario; ?>">
            <input type="hidden" name="clasificacion" value="<?php echo $clasificacion; ?>">
            <button class="btn" type="submit" name="exportar_pdf">
                <i class="bi bi-file-earmark-arrow-down-fill"></i> Exportar PDF
            </button>
        </form>
    <?php endif; ?>
        </form>

        <!-- Mostrar los resultados -->
        <table>
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Departamento</th>
                    <th>Salario Base</th>
                    <th>Salario Neto</th>
                    <th>Clasificación</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($data) && $data !== null) {
                    foreach ($data as $row) {
                        echo "<tr>
                            <td>" . $row['nombre'] . "</td>
                            <td>" . $row['departamento'] . "</td>
                            <td>" . number_format($row['salario_base'], 2) . "</td>
                            <td>" . number_format($row['salario_neto'], 2) . "</td>
                            <td>" . $row['clasificaciones'] . "</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' class='no-records'>No se encontraron registros.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>


<style>
    body {
        font-family: 'Ruda', sans-serif;
        background-color: #f7f7f7;
        margin: 0;
        padding: 0;

    }

    .resultado {
        text-align: center;
        /* Asegura que el texto se centre */
        font-size: 18px;
        color: #333;
        margin-top: 20px;
        background-color: rgb(160, 255, 180);
        /* Fondo verde */
        padding: 15px;
        /* Espaciado interno */
        border-radius: 8px;
        /* Bordes redondeados */
        max-width: 600px;
        /* Limita el ancho máximo */
        margin-left: auto;
        /* Centra el bloque */
        margin-right: auto;
        /* Centra el bloque */

    }



    .container {
    width: 40%; /* Ajusta el ancho para hacer la card más pequeña */
    max-width: 800px; /* Limita el ancho máximo */
    margin-top: 100px;
    padding: 20px 40px;
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.6);
    margin-left: auto;
    margin-right: auto;
}



    h1 {
        text-align: center;
        color: #333;
        margin-bottom: 50px;
        margin-right: 10%;
        font-weight: bold;
        font-size: 24px;

    }

    h3 {
        text-align: center;
        color: black;
        margin-bottom: 50px;
        margin-right: 10%;
        font-weight: bold;
    }

    .btn {
    background-color: #0B4F6C;
    color: white;
    padding: 10px 20px;
    font-size: 16px;
    border-radius: 5px;
    margin-right: 10px; /* Espacio entre los botones */
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

.btn-secondary {
    background-color: #555; /* Gris para el botón "Devolver" */
    color: white;
}

.btn:hover {
    background-color: #0E5D6A;
}

.btn-secondary:hover {
    background-color: #444; /* Gris más oscuro para el hover */
}

    .btn:active {
        background-color: #0E5D6A;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.6);

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
        background-color: #116B67;
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
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.6);

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
        background-color: #0E5D6A;
    }

    .modal-content a:hover {
        background-color: darkgray;
    }



.button-group {
    display: flex;
    justify-content: space-between;  /* Alinea a la izquierda, usa center o space-between si prefieres */
    align-items: center;  /* Alineación vertical */
    gap: 15px;  /* Espaciado entre los botones */
    margin-bottom: 20px;
}

.button-group form {
    display: inline-block;
}


.filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 20px; /* Espacio entre los elementos */
    align-items: center;
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.8);
    max-width: 600px;
    margin: auto;
    justify-content: center;
    
}

.form-group {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-width: 200px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.8);
}

label {
    font-size: 16px;
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
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
    border-color: #147665;
}

select:focus {
    outline: none;
    border-color: #147665;
    box-shadow: #147665;
}

/* Botón estilizado */
.btn {
    background-color: #0E5D6A;
    color: white;
    padding: 10px 15px;
    font-size: 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn:hover {
    background-color: #0E5D6A;
}


.form-container {
    display: flex;
    justify-content: start;
     /* Centra el contenido horizontalmente */
    align-items: center;
        /* Centra el contenido verticalmente */
     width: 100%;
    /* Asegura que el contenedor ocupe todo el ancho disponible */
}
.filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 15px; /* Espacio entre los elementos */
    align-items: center;
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.8);
    max-width: 600px; /* Tamaño máximo para la card */
    margin: auto;
    justify-content: center;
    width: 80%; /* Reducción de ancho para que la card sea más pequeña */
}
</style>


</html>