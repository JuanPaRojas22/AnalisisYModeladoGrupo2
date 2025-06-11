<?php
ob_start();
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <title>Registar Horas Extra</title>

    <link href="assets/css/bootstrap.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Bootstrap core CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!--external css-->
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="assets/css/zabuto_calendar.css">
    <link rel="stylesheet" type="text/css" href="assets/js/gritter/css/jquery.gritter.css" />
    <link rel="stylesheet" type="text/css" href="assets/lineicons/style.css">

    <!-- Custom styles for this template -->
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/style-responsive.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>
    <!-- **********************************************************************************************************************************************************
      MAIN CONTENT
      *********************************************************************************************************************************************************** -->
    <!--main content start-->
    <section id="main-content">
        <section class="wrapper site-min-height">
            <html lang="es">

            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Registrar Horas Extras</title>

            </head>

            <div class="container">
                <a href="VerPlanilla.php" class="button"><i class="bi bi-arrow-return-left"></i></a>
                <h1 class="text-center" style="margin-left: 10%; color: black;" >Calcular Horas Extras</h1>

                <!-- Formulario con un botón para calcular horas extras -->
                <form class='form' method="post" enctype="multipart/form-data">
                    <label for="archivo_excel">Seleccionar archivo Excel:</label>
                    <input type="file" name="archivo_excel" id="archivo_excel" accept=".xlsx, .xls" required> <button
                        type="submit" class="btn">
                        <i class="bi bi-upload"></i>
                    </button>
                </form>


            </div>



        </section>


</body>

</html>

<style>
    body {
        font-family: 'Ruda', sans-serif;
        background-color: #f7f7f7;
        margin: 0;
        padding: 0;
    }

    .container {
        width: 80%;
        margin: 200px auto;
        padding: 20px;
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.48);


    }

    h1 {
        text-align: center;
        margin-bottom: 50px;
        margin-right: 10%;
        font-weight: bold;
        color: black !important;
        
    }

    h3 {
        text-align: center;
        color: black;
        margin-bottom: 50px;
        margin-right: 10%;
        font-weight: bold;
    }

    .button {
        display: inline-block;
        background-color: #147964;
        color: white;
        padding: 10px 20px;
        font-size: 16px;
        font-weight: bold;
        text-align: center;
        text-decoration: none;
        border-radius: 5px;
        margin-bottom: 20px;
        transition: background-color 0.3s;
    }

    .btn {
        display: inline-block;
        background-color: #147964;
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

    form {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-top: 20px;
    }

    /* Estilo para el input de archivo */
    input[type="file"] {
        margin-bottom: 15px;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        background-color: #f9f9f9;
    }

    /* Estilo para el botón de submit */
    input[type="submit"] {
        padding: 10px 20px;
        border: none;
        background-color: #147964;
        color: white;
        font-size: 16px;
        cursor: pointer;
        border-radius: 4px;
    }



    .btn:hover {
        background-color: #137266;
    }

    .btn:active {
        background-color: #137266;
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
        color: black !important;
    }

    label {
        color: black !important;
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
        background-color: #116B67;
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


<?php
require_once 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

date_default_timezone_set('America/Costa_Rica');
require 'conexion.php';

$id_admin = $_SESSION['id_usuario'] ?? null;

if (!$id_admin) {
    die("Usuario no autenticado.");
}

// Validar departamento del administrador
$query_depto = "SELECT id_departamento FROM usuario WHERE id_usuario = ?";
$stmt = $conn->prepare($query_depto);
$stmt->bind_param("i", $id_admin);
$stmt->execute();
$stmt->bind_result($departamento_admin);
$stmt->fetch();
$stmt->close();

// Procesar archivo
if (isset($_FILES['archivo_excel']) && $_FILES['archivo_excel']['error'] == 0) {
    $archivo = $_FILES['archivo_excel']['tmp_name'];

    // Cargar solo las primeras 3 columnas necesarias (Nombre Completo, Horas Extras, Horas Extras Domingo, Horas Extras Feriado)
    $reader = IOFactory::createReader('Xlsx');
    $reader->setReadDataOnly(true); // Solo leer datos, no estilos
    $spreadsheet = $reader->load($archivo);
    $hoja = $spreadsheet->getSheetByName('Findemes');

    // Procesar las filas en bloques (chunk)
    $maxRows = 1000; // Número de filas por bloque
    $rowStart = 2; // A partir de la fila 2 (salta encabezado)
    // Obtener la última fila
    // Procesar las filas en bloques
    $maxRows = 1000; // Número de filas por bloque
    $rowStart = 3; // A partir de la fila 3 (salta encabezado)
// Obtener la última fila
    $highestRow = $hoja->getHighestRow();

    while ($rowStart <= $highestRow) {
        // Leer un bloque de filas
        $fila = $hoja->rangeToArray('C' . $rowStart . ':I' . min($rowStart + $maxRows - 1, $highestRow), null, true, false);

        foreach ($fila as $i => $row) {
            // Verificar si la fila contiene el encabezado "Nombre Completo" o si está vacía
            if (empty($row[0]) || strtolower($row[0]) == 'nombre completo') {
                continue; // Saltar fila si está vacía o contiene el encabezado
            }

            // Extraer los datos de las 3 columnas necesarias
            $nombre_empleado = trim($row[0]); // Nombre Completo
            // Imprime la celda (columna) y la fila actual
            $celda_actual = 'G' . $rowStart; // 'G' es la columna de Horas Extras
            //echo "Leyendo celda: " . $celda_actual . "<br>"; // Imprimir la celda
            //echo "Valor crudo de la celda: " . $hoja->getCell($celda_actual)->getValue() . "<br>"; // Mostrar valor antes de convertir

            // Extraer el valor de la celda correspondiente
            $horas_extra = $hoja->getCell($celda_actual)->getValue();

            // Verificar el tipo de valor antes de convertir
            //echo "Tipo de valor antes de convertir: " . gettype($horas_extra) . "<br>";
            //echo "Valor crudo de horas extra: " . $horas_extra . "<br>"; // Ver el valor antes de convertir

            // Convertir el valor, reemplazando la coma por punto
            $horas_extra = floatval(str_replace(',', '.', $horas_extra));
            //echo "Horas Extra (convertido): " . $horas_extra . "<br>"; // Ver el valor después de convertir


            $horas_extra_domingo = floatval(str_replace(',', '.', $row[5])); // H
            $horas_extra_feriado = floatval(str_replace(',', '.', $row[6])); // I

            // Verificar si el nombre del empleado no está vacío
            if (!empty($nombre_empleado)) {
                // Dividir el nombre completo en nombre y apellido
                $nombre_partes = explode(' ', $nombre_empleado); // Dividir por espacio
                $nombre = $nombre_partes[0]; // El primer nombre
                $apellido = implode(' ', array_slice($nombre_partes, 1)); // El resto es el apellido

                // Buscar empleado en la base de datos por nombre y departamento
                $query_emp = "SELECT planilla.id_usuario, planilla.id_planilla, planilla.salario_base, planilla.salario_neto 
                FROM planilla 
                INNER JOIN usuario ON planilla.id_usuario = usuario.id_usuario
                WHERE usuario.nombre LIKE ? AND usuario.apellido LIKE ? AND usuario.id_departamento = ?";

                $stmt = $conn->prepare($query_emp);
                $stmt->bind_param("ssi", $nombre, $apellido, $departamento_admin);

                if (!$stmt->execute()) {
                    //echo "Error al ejecutar la consulta de empleado: " . $stmt->error . "<br>";
                    //echo "ID del empleado encontrado: $id_usuario <br>";

                }
                $stmt->bind_result($id_usuario, $id_planilla, $salario_base, $salario_neto);

                if ($stmt->fetch()) {
                    $stmt->close();

                    $salario_quincenal = round($salario_base / 2, 2);
                    $tarifa_hora = round($salario_quincenal / 8, 2);

                    // Mostrar valores de las horas extras y monto calculado para depuración
                    //echo "Empleado: $nombre_empleado, Horas Extras: $horas_extra, Domingo: $horas_extra_domingo, Feriado: $horas_extra_feriado <br>";

                    // Procesar horas extras (tanto normales como domingos y feriados)
                    $monto_hora_extra = round($horas_extra * $tarifa_hora, 2);
                    $monto_hora_extra_domingo = round($horas_extra_domingo * $tarifa_hora * 2, 2);
                    $monto_hora_extra_feriado = round($horas_extra_feriado * $tarifa_hora * 4, 2);

                    // Insertar horas extras (sumar las tres montos)
                    $monto_total = $monto_hora_extra + $monto_hora_extra_domingo + $monto_hora_extra_feriado;

                    // Validar que el monto total no sea 0
                    if ($monto_total > 0) {
                        $usuario_creacion = $_SESSION['usuario'] ?? 'Sistema';

                        // Inserción para las horas extras normales
                        if ($horas_extra > 0) {
                            $query_insert = "INSERT INTO horas_extra (id_usuario, fecha, horas, monto_pago, tipo) 
                         VALUES (?, NOW(), ?, ?, 'Horas Extra')";
                            $stmt = $conn->prepare($query_insert);
                            $stmt->bind_param("idd", $id_usuario, $horas_extra, $monto_hora_extra);
                            if (!$stmt->execute()) {
                                // Manejo de errores
                            }
                            $stmt->close();
                        }

                        // Inserción para las horas extras de domingo
                        if ($horas_extra_domingo > 0) {
                            $query_insert = "INSERT INTO horas_extra (id_usuario, fecha, horas, monto_pago, tipo) 
                         VALUES (?, NOW(), ?, ?, 'Horas Extra Domingo')";
                            $stmt = $conn->prepare($query_insert);
                            $stmt->bind_param("idd", $id_usuario, $horas_extra_domingo, $monto_hora_extra_domingo);
                            if (!$stmt->execute()) {
                                // Manejo de errores
                            }
                            $stmt->close();
                        }

                        // Inserción para las horas extras de feriado
                        if ($horas_extra_feriado > 0) {
                            $query_insert = "INSERT INTO horas_extra (id_usuario, fecha, horas, monto_pago, tipo) 
                         VALUES (?, NOW(), ?, ?, 'Horas Extra Feriado')";
                            $stmt = $conn->prepare($query_insert);
                            $stmt->bind_param("idd", $id_usuario, $horas_extra_feriado, $monto_hora_extra_feriado);
                            if (!$stmt->execute()) {
                                // Manejo de errores
                            }
                            $stmt->close();
                        }

                        // Actualizar salario neto
                        $nuevo_salario_neto = $salario_neto + $monto_total;
                        $query_update = "UPDATE planilla SET salario_neto = ? WHERE id_usuario = ?";
                        $stmt = $conn->prepare($query_update);
                        $stmt->bind_param("di", $nuevo_salario_neto, $id_usuario);
                        if (!$stmt->execute()) {
                            // Manejo de errores
                        }
                        $stmt->close();
                    }
                } else {
                    // echo "Empleado '$nombre_empleado' no encontrado o no pertenece a tu departamento.<br>";
                }
            }
        }
        $rowStart += $maxRows; // Moverse al siguiente bloque
    }

    //echo "Horas extras procesadas correctamente.";

}
?>


<?php
// Cerrar la conexión
$conn->close();
ob_end_flush(); // Liberar el búfer y enviar la salida al navegador
?>