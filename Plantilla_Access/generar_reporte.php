<?php
require_once __DIR__ . '/Impl/UsuarioDAOSImpl.php'; // Incluye la clase UsuarioDAOSImpl
require_once 'fpdf/fpdf.php'; // Incluye la librería FPDF

class PDF extends FPDF
{
    // Encabezado personalizado
    function Header()
    {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'Access Personnel Report', 0, 1, 'C');
        $this->SetFont('Arial', 'I', 12);
        $this->Cell(0, 10, 'Generado el ' . date('d/m/Y'), 0, 1, 'C');
        $this->Ln(10);
    }

    // Pie de página
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 10, 'Página ' . $this->PageNo(), 0, 0, 'C');
    }
}

class GenerarReporteController
{
    private $usuarioDAO;

    public function __construct()
    {
        $this->usuarioDAO = new UsuarioDAOSImpl();
    }

    // Función para obtener el nombre del departamento
    public function getDepartmentName($id_departamento)
    {
        // Realiza una consulta para obtener el nombre del departamento
        // Asegúrate de que este método esté implementado correctamente en tu DAO
        $nombreDepartamento = $this->usuarioDAO->getDepartmentNameById($id_departamento);
        return $nombreDepartamento;
    }

    public function generarPDF($id_departamento)
    {
        $usuarios = $this->usuarioDAO->getUsersByDepartment($id_departamento);

        if (empty($usuarios)) {
            echo "<script>
                alert('No hay usuarios para el departamento seleccionado.');
                window.location.href = 'MostrarUsuarios.php';
            </script>";
            exit;
        }

        // Obtener el nombre del departamento
        $nombreDepartamento = $this->getDepartmentName($id_departamento);

        $pdf = new PDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);

        // Colores de tabla
        $pdf->SetFillColor(0, 102, 204); // Azul
        $pdf->SetTextColor(255); // Blanco
        $pdf->SetDrawColor(0, 0, 0); // Bordes negros
        $pdf->SetLineWidth(0.3);

        // Centrar la tabla
        $tableWidth = 200; // Ancho total de la tabla (suma de celdas)
        $pdf->SetX((200 - $tableWidth) / 2); // Centrar la tabla

        // Encabezado de la tabla
        $pdf->Cell(25, 10, 'Nombre', 1, 0, 'C', true);
        $pdf->Cell(25, 10, 'Apellido', 1, 0, 'C', true);
        $pdf->Cell(60, 10, 'Correo', 1, 0, 'C', true);
        $pdf->Cell(40, 10, 'Telefono', 1, 0, 'C', true);
        $pdf->Cell(60, 10, 'Departamento', 1, 1, 'C', true); // Modificado para mostrar "Departamento"

        // Reset de colores para contenido
        $pdf->SetFillColor(224, 235, 255);
        $pdf->SetTextColor(0);
        $pdf->SetFont('Arial', '', 12);
        $fill = false;

        // Contenido de la tabla
        foreach ($usuarios as $usuario) {
            $pdf->SetX((200 - $tableWidth) / 2); // Asegurar que las filas también estén centradas
            $pdf->Cell(25, 10, mb_convert_encoding($usuario['nombre'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', $fill);
            $pdf->Cell(25, 10, mb_convert_encoding($usuario['apellido'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', $fill);
            $pdf->Cell(60, 10, mb_convert_encoding($usuario['correo_electronico'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', $fill);
            $pdf->Cell(40, 10, mb_convert_encoding($usuario['numero_telefonico'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', $fill);
            // Mostrar el nombre del departamento en vez del ID
            $pdf->Cell(60, 10, mb_convert_encoding($nombreDepartamento, 'ISO-8859-1', 'UTF-8'), 1, 1, 'C', $fill);
            $fill = !$fill; // Alternar colores
        }

        // Total de usuarios
        $totalUsuarios = count($usuarios); // Total de usuarios
        $pdf->SetX((200 - $tableWidth) / 2); // Centrar la fila de total
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetFillColor(102, 204, 255); // Azul claro para el total
        $pdf->Cell(50, 10, 'Total de Usuarios', 1, 0, 'C', true); // Celda "Total"
        $pdf->Cell(160, 10, $totalUsuarios . ' Usuarios', 1, 1, 'C', true); // Total de usuarios

        // Enviar el PDF al navegador
        $pdf->Output('I', 'Reporte_Usuarios.pdf');
    }
}

// Generar PDF si hay un ID de departamento
if (isset($_GET['id_departamento'])) {
    $id_departamento = $_GET['id_departamento'];
    $controller = new GenerarReporteController();
    $controller->generarPDF($id_departamento);
} else {
    die('No se ha proporcionado un ID de departamento.');
}
?>
