<?php
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/Impl/UsuarioDAOSImpl.php'; // Incluye la clase UsuarioDAOSImpl
require_once 'fpdf/fpdf.php'; // Incluye la librería FPDF

class PDF extends FPDF
{
    // Encabezado personalizado
    function Header()
    {
        $this->SetFont('Arial', 'B', 16);
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



    // Dibuja las filas de la tabla
    function TableRow($label, $value)
    {
        // Ancho de las celdas
        $cellWidth = 90;  // Ajusta el tamaño según lo necesites
    
        // Dibuja la celda con la etiqueta centrada
        $this->SetFont('Arial', 'B', 12);  // Negrita para las etiquetas
        $this->Cell($cellWidth, 10, $label, 1, 0, 'C');  // La primera celda (label) centrada
    
        // Dibuja la celda con el valor centrado
        $this->SetFont('Arial', '', 12);  // Fuente normal para los valores
        $this->Cell(0, 10, $value, 1, 1, 'C');  // La segunda celda (value) centrada
    }
    
}

class GenerarReporteController
{
    private $usuarioDAO;

    public function __construct()
    {
        $this->usuarioDAO = new UsuarioDAOSImpl();
    }

    // Función para obtener los datos del usuario
    public function getUserById($id_usuario)
    {
        // Realiza una consulta para obtener los datos del usuario
        $usuario = $this->usuarioDAO->getUserById($id_usuario);
        return $usuario;
    }

    
    

    public function generarPDF($id_usuario)
    {

        
        $usuario = $this->getUserById($id_usuario);

        if (empty($usuario)) {
            echo "<script>
                alert('No se encontró el usuario con el ID proporcionado.');
                window.location.href = 'MostrarUsuarios.php';
            </script>";
            exit;
        }

        $pdf = new PDF();
        $pdf->AddPage();
        
        // Título del PDF
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'Reporte de Usuario: ' . $usuario['nombre'] . ' ' . $usuario['apellido'], 0, 1, 'C');
        $pdf->Ln(10);
        
        // Diseño tipo tabla

        $pdf->TableRow('Nombre', mb_convert_encoding($usuario['nombre'], 'ISO-8859-1', 'UTF-8'));
        $pdf->TableRow('Apellido', mb_convert_encoding($usuario['apellido'], 'ISO-8859-1', 'UTF-8'));
        $pdf->TableRow('Sexo', mb_convert_encoding($usuario['sexo'], 'ISO-8859-1', 'UTF-8'));
        $pdf->TableRow('Fecha de Nacimiento', mb_convert_encoding($usuario['fecha_nacimiento'], 'ISO-8859-1', 'UTF-8'));
        $pdf->TableRow('Estado Civil', mb_convert_encoding($usuario['estado_civil'], 'ISO-8859-1', 'UTF-8'));
        $pdf->TableRow('Cargo', mb_convert_encoding($usuario['cargo'], 'ISO-8859-1', 'UTF-8'));
        $pdf->TableRow('Correo', mb_convert_encoding($usuario['correo_electronico'], 'ISO-8859-1', 'UTF-8'));
        $pdf->TableRow('Telefono', mb_convert_encoding($usuario['numero_telefonico'], 'ISO-8859-1', 'UTF-8'));
        $pdf->TableRow('Estado', mb_convert_encoding($usuario['estado'], 'ISO-8859-1', 'UTF-8'));
        $pdf->TableRow('Departamento', mb_convert_encoding($usuario['departamento_nombre'], 'ISO-8859-1', 'UTF-8'));
        $pdf->TableRow('Rol', mb_convert_encoding($usuario['rol_nombre'], 'ISO-8859-1', 'UTF-8'));
        $pdf->TableRow('Fecha de Ingreso', mb_convert_encoding($usuario['fecha_ingreso'], 'ISO-8859-1', 'UTF-8'));
        

        // Enviar el PDF al navegador
        $pdf->Output('I', 'Reporte_Usuario_' . $usuario['id_usuario'] . '.pdf');
        
    }

    
}

// Generar PDF si hay un ID de usuario
if (isset($_GET['id_usuario'])) {
    $id_usuario = $_GET['id_usuario'];
    $controller = new GenerarReporteController();
    $controller->generarPDF($id_usuario);
} else {
    die('No se ha proporcionado un ID de usuario.');
}
?>
