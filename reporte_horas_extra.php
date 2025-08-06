<?php

$usuario = $_POST['usuario'] ?? '';
$departamento = $_POST['departamento'] ?? '';

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
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DEBUG: Mostrar qué datos se están recibiendo
// Puedes comentar esto después de hacer la prueba
echo "<pre>";
print_r($_POST);
echo "</pre>";
exit;

require 'fpdf/fpdf.php';  // Para exportar a PDF


// Verificar si se recibieron los filtros
$usuario = isset($_POST['usuario']) ? $_POST['usuario'] : "";
$departamento = isset($_POST['departamento']) ? $_POST['departamento'] : "";

// Realizar la consulta
$query = "SELECT u.nombre,u.apellido , d.Nombre, SUM(he.horas) AS total_horas_extras, SUM(he.monto_pago) AS monto_pago
          FROM horas_extra he
          JOIN usuario u ON he.id_usuario = u.id_usuario
          JOIN departamento d ON u.id_departamento = d.id_departamento
          WHERE 1";

// Agregar condiciones según los filtros
if ($usuario != "") {
    $query .= " AND u.id_usuario = '$usuario'";  // Filtro de usuario
}
if ($departamento != "") {
    $query .= " AND d.id_departamento = '$departamento'";  // Filtro de departamento
}

// Agregar GROUP BY para las columnas no agregadas
$query .= " GROUP BY u.nombre, u.apellido, d.Nombre";

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
$pdf->SetMargins(15, 15, 15);
$pdf->SetFont('Arial', 'B', 16);

// Agregar logo y centrarlo
$pdf->Image('assets/img/logo_acces_perssonel.jpeg', 75, 10, 60);  // Posición centrada
$pdf->Ln(45); // Espacio después del logo

// Título del reporte
$pdf->Cell(0, 10, 'Reporte de Horas Extras', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Acces Personnel', 0, 1, 'C');
$pdf->Ln(5);

// Fecha
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Fecha: ' . date('d/m/Y'), 0, 1, 'C');
$pdf->Ln(5); 

// Subtítulo de la tabla
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Total de Horas Extras del Empleado', 0, 1, 'C');
$pdf->Ln(5);

// Encabezados de la tabla
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(200, 200, 200);  // Color gris para encabezados
$pdf->Cell(70, 10, 'Nombre', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Departamento', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Horas Extras', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Monto Pago', 1, 1, 'C', true);

// Cuerpo de la tabla
$pdf->SetFont('Arial', '', 12);
foreach ($data as $row) {
    $pdf->Cell(70, 10, utf8_decode($row['nombre'].' '. $row['apellido']), 1, 0, 'C');
    $pdf->Cell(50, 10, utf8_decode($row['Nombre']), 1, 0, 'C');
    $pdf->Cell(40, 10, number_format($row['total_horas_extras'], 2), 1, 0, 'C');
    $pdf->Cell(30, 10, number_format($row['monto_pago'], 2), 1, 1, 'C');
}

// Pie de página
$pdf->SetY(-50);
$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(0, 10, 'Generado por Sistema de Reportes - Acces Personnel', 0, 0, 'C');

$pdf->Output('I', 'reporte_horas_extras.pdf');
exit;
?>
