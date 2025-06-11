<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "gestionempleados");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Verificar autenticación del usuario
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Invitado';

// Incluir la plantilla
include 'template.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario    = $_POST['id_usuario'];
    $fecha         = $_POST['fecha'];
    $motivo        = $_POST['motivo'];
    $justificada   = $_POST['justificada'];    // ← Nuevo
    $registrado_por = $_SESSION['id_usuario'];

    // Ahora incluimos la columna justificada en el INSERT
    $query = "INSERT INTO Ausencias 
              (id_usuario, fecha, motivo, justificada, registrado_por) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    // Tipos: i = integer, s = string
    $stmt->bind_param("isssi", 
        $id_usuario, 
        $fecha, 
        $motivo, 
        $justificada, 
        $registrado_por
    );

    if ($stmt->execute()) {
        echo "<script>alert('Ausencia registrada correctamente.');</script>";
    } else {
        echo "<script>alert('Error al registrar la ausencia.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Ausencia</title>
</head>

<body>

    <div class="container">
    <h1>Registrar Ausencia</h1>
    <form method="POST" action="">
        <div class="form-group">
            <label for="id_usuario">Empleado:</label>
            <select name="id_usuario" required>
                <?php
                $result = $conn->query("SELECT id_usuario, nombre FROM Usuario");
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='{$row['id_usuario']}'>{$row['nombre']}</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="fecha">Fecha:</label>
            <input type="date" name="fecha" required>
        </div>
        <div class="form-group">
            <label for="motivo">Motivo:</label>
            <input type="text" name="motivo" required>
        </div>
        <div class="form-group">
    <label for="justificada">Justificada:</label>
    <select name="justificada" required>
        <option value="No">No</option>
        <option value="Sí">Sí</option>
    </select>
</div>
        <button type="submit" class="btn btn-register">Registrar Ausencia</button>
    </form>
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

.container {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    margin-top: 50px;
    background-color: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    max-width: 600px;
    width: 100%;
}

h1 {
    font-size: 2.5em;
    color: #333;
    margin-bottom: 30px;
    font-weight: bold;
}

.form-group {
    width: 100%;
    margin-bottom: 20px;
}

label {
    font-size: 1.2em;
    color: #333;
    font-weight: bold;
    margin-bottom: 10px;
}

input[type="text"], input[type="date"], select {
    width: 100%;
    padding: 12px;
    font-size: 1em;
    border-radius: 8px;
    border: 2px solid #ddd;
    background-color: #f9f9f9;
    margin-bottom: 15px;
    transition: all 0.3s ease;
}

input[type="text"]:focus, input[type="date"]:focus, select:focus {
    border-color: #147964;
    box-shadow: 0 0 8px rgba(20, 121, 100, 0.4);
    outline: none;
}

select {
    font-size: 1em;
}

.btn {
    display: inline-block;
    background-color: #147964;
    color: white;
    padding: 12px 20px;
    font-size: 1.1em;
    font-weight: bold;
    text-align: center;
    border-radius: 5px;
    cursor: pointer;
    border: none;
    transition: background-color 0.3s;
}

.btn:hover {
    background-color: #147964;
}

.btn:active {
    background-color: #147964;
}

.btn-container {
    display: flex;
    justify-content: flex-start;
    width: 100%;
}

select, input[type="text"] {
    margin-left: 10px;
}

@media (max-width: 768px) {
    .container {
        margin-top: 30px;
        padding: 15px;
    }

    h1 {
        font-size: 1.8em;
    }

    .form-group {
        margin-bottom: 12px;
    }

    .btn {
        padding: 10px 18px;
        font-size: 1em;
    }
}
td, div {
            color: black !important;
        }


   
</style>