<?php
session_start();
require_once __DIR__ . '/Impl/VacacionDAOSImpl.php';
// Verifica si se recicbio el id del usuario y la accion a realizar
if(isset($_GET['id']) && isset($_GET['accion'])){
    $id_vacacion = $_GET['id'];
    $accion = $_GET['accion'];
    $VacacionDAO = new VacacionDAOSImpl();

    // Logica para mandar el mensaje por correo

    // Verifica si la accion es aprobar o rechazar
    if($accion == 'aprobar'){
        // Conectar a la base de datos
        $conn = new mysqli("localhost", "root", "", "GestionEmpleados");

        // Verificar conexión
        if ($conn->connect_error) {
            die("Error de conexión: " . $conn->connect_error);
        }

        $query = "SELECT diasTomado, id_usuario FROM vacacion WHERE id_vacacion = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_vacacion);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $diasTomado = $row['diasTomado'];
        $id_usuario = $row['id_usuario'];

        $stmt->close();
        $conn->close();

        $VacacionDAO->aprobarSolicitud($id_vacacion, $diasTomado, $id_usuario);
    }else if($accion == 'rechazar'){
        // Conectar a la base de datos
        $conn = new mysqli("localhost", "root", "", "GestionEmpleados");

        // Verificar conexión
        if ($conn->connect_error) {
            die("Error de conexión: " . $conn->connect_error);
        }

        $query = "SELECT diasTomado, id_usuario FROM vacacion WHERE id_vacacion = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_vacacion);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $diasTomado = $row['diasTomado'];
        $id_usuario = $row['id_usuario'];

        $stmt->close();
        $conn->close();

        $VacacionDAO->rechazarSolicitud($id_vacacion, $diasTomado, $id_usuario);
    }
    
    // Se redirije de nuevo a la pagina de detalle de vacaciones
    header('Location: vacaciones.php?id='.$id_vacacion);
    exit();
} else {
    echo "Parametros incorrectos";
}

?>