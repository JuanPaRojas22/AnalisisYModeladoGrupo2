<?php
// Conexión a la base de datos
// Parámetros de conexión
$host = "accespersoneldb.mysql.database.azure.com";
$user = "adminUser";
$password = "admin123+";
$dbname = "gestionEmpleados";
$port = 3306;

// Ruta al certificado CA para validar SSL
$ssl_ca = '/home/site/wwwroot/certs/BaltimoreCyberTrustRoot.crt.pem';

// Inicializamos mysqli
$conn = mysqli_init();

// Configuramos SSL
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);


// Intentamos conectar usando SSL (con la bandera MYSQLI_CLIENT_SSL)
if (!$conn->real_connect($host, $user, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Establecemos el charset
mysqli_set_charset($conn, "utf8mb4");
session_start();
require 'fpdf/fpdf.php';  // Para exportar a PDF

// Verificar si se recibieron los filtros
$usuario = isset($_POST['usuario']) ? $_POST['usuario'] : "";
//$departamento = isset($_POST['departamento']) ? $_POST['departamento'] : "";
$clasificacion = isset($_POST['clasificacion']) && !empty($_POST['clasificacion']) ? $_POST['clasificacion'] : null;


// Realizar la consulta
$query = "SELECT 
                u.nombre,
                u.apellido,
                d.nombre AS departamento,
                p.salario_base,     
                p.salario_neto,
                COALESCE(GROUP_CONCAT(DISTINCT te.descripcion SEPARATOR ', '), 'Sin clasificación') AS clasificaciones
            FROM planilla p
            JOIN Usuario u ON p.id_usuario = u.id_usuario
            JOIN departamento d ON u.id_departamento = d.id_departamento
            LEFT JOIN empleado_tipo_empleado ete ON p.id_usuario = ete.id_empleado
            LEFT JOIN tipo_empleado te ON ete.id_tipo_empleado = te.id_tipo_empleado
            WHERE 1=1";

// Filtro por usuario
if ($usuario) {
    $query .= " AND u.id_usuario = " . intval($usuario);
}

// Filtro por clasificación (corregido)
if ($clasificacion) {
    $query .= " AND te.id_tipo_empleado = " . intval($clasificacion);
}

// Agregar GROUP BY después de los filtros
$query .= " GROUP BY u.id_usuario, u.nombre, u.apellido, d.nombre, p.salario_base, p.salario_neto";

/*
echo "<pre>";
print_r($usuario);
print_r($clasificacion);
echo "</pre>";
exit;
*/


$result = mysqli_query($conn, $query);
$data = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
}

// Crear el PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetMargins(10, 10, 10);
$pdf->SetFont('Arial', 'B', 16);

// Agregar logo y centrarlo
$pdf->Image('assets/img/logo_acces_perssonel.jpeg', 75, 10, 60);
$pdf->Ln(45);

// Título del reporte
$pdf->Cell(0, 10, 'Reporte de Clasificacion de Empleado', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Acces Personnel', 0, 1, 'C');
$pdf->Ln(5);

// Fecha
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Fecha: ' . date('d/m/Y'), 0, 1, 'C');
$pdf->Ln(5);

// Encabezados de la tabla
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(200, 200, 200);
$pdf->Cell(50, 10, 'Nombre', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Departamento', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Salario Base', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Salario Neto', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Clasificacion', 1, 1, 'C', true);

// Cuerpo de la tabla
$pdf->SetFont('Arial', '', 10);
foreach ($data as $row) {
    $pdf->Cell(50, 10, utf8_decode($row['nombre'] . ' ' . $row['apellido']), 1, 0, 'C');
    $pdf->Cell(40, 10, utf8_decode($row['departamento']), 1, 0, 'C');
    $pdf->Cell(30, 10, number_format($row['salario_base'], 2), 1, 0, 'C');
    $pdf->Cell(30, 10, number_format($row['salario_neto'], 2), 1, 0, 'C');
    $pdf->MultiCell(40, 10, utf8_decode($row['clasificaciones']), 1, 'C');
}

// Pie de página
$pdf->SetY(-30);
$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(0, 10, 'Generado por Sistema de Reportes - Acces Personnel', 0, 0, 'C');

$pdf->Output('D', 'reporte_clasificacion_empleado.pdf');
exit;
?> 
