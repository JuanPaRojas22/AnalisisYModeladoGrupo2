<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'fpdf/fpdf.php';  // Para exportar a PDF

// Guardar filtros en sesión solo si viene POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['filtro_usuario'] = $_POST['usuario'] ?? '';
    $_SESSION['filtro_departamento'] = $_POST['departamento'] ?? '';
}

// Recuperar filtros desde sesión (no desde POST)
$usuario = $_SESSION['filtro_usuario'] ?? '';
$departamento = $_SESSION['filtro_departamento'] ?? '';

// DEBUG para verificar filtros (comenta cuando esté listo)
// echo "<pre>"; print_r(['usuario'=>$usuario,'departamento'=>$departamento]); echo "</pre>"; exit;

// Parámetros de conexión
$host = "accespersoneldb.mysql.database.azure.com";
$user = "adminUser";
$password = "admin123+";
$dbname = "gestionEmpleados";
$port = 3306;
$ssl_ca = '/home/site/wwwroot/certs/BaltimoreCyberTrustRoot.crt.pem';

// Inicializar mysqli con SSL
$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
if (!$conn->real_connect($host, $user, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("Error de conexión: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

// Construir consulta
$query = "SELECT u.nombre, u.apellido, d.Nombre AS departamento, 
          SUM(he.horas) AS total_horas_extras, SUM(he.monto_pago) AS monto_pago
          FROM horas_extra he
          JOIN usuario u ON he.id_usuario = u.id_usuario
          JOIN departamento d ON u.id_departamento = d.id_departamento
          WHERE 1";

if ($usuario !== '') {
    $usuario = mysqli_real_escape_string($conn, $usuario);
    $query .= " AND u.id_usuario = '$usuario'";
}
if ($departamento !== '') {
    $departamento = mysqli_real_escape_string($conn, $departamento);
    $query .= " AND d.id_departamento = '$departamento'";
}

$query .= " GROUP BY u.nombre, u.apellido, d.Nombre";

$result = mysqli_query($conn, $query);

$data = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
}

// Crear PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetMargins(15, 15, 15);
$pdf->SetFont('Arial', 'B', 16);

// Logo centrado
$pdf->Image('assets/img/logo_acces_perssonel.jpeg', 75, 10, 60);
$pdf->Ln(45);

$pdf->Cell(0, 10, 'Reporte de Horas Extras', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Acces Personnel', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Fecha: ' . date('d/m/Y'), 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Total de Horas Extras del Empleado', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(200, 200, 200);
$pdf->Cell(70, 10, 'Nombre', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Departamento', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Horas Extras', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Monto Pago', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 12);
foreach ($data as $row) {
    $pdf->Cell(70, 10, utf8_decode($row['nombre'] . ' ' . $row['apellido']), 1, 0, 'C');
    $pdf->Cell(50, 10, utf8_decode($row['departamento']), 1, 0, 'C');
    $pdf->Cell(40, 10, number_format($row['total_horas_extras'], 2), 1, 0, 'C');
    $pdf->Cell(30, 10, number_format($row['monto_pago'], 2), 1, 1, 'C');
}

$pdf->SetY(-50);
$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(0, 10, 'Generado por Sistema de Reportes - Acces Personnel', 0, 0, 'C');

$pdf->Output('I', 'reporte_horas_extras.pdf');
exit;
?>
