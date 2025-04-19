<?php
session_start();
include 'template.php';
// Conexión a la base de datos
$mysqli = new mysqli("localhost", "root", "", "gestionempleados");

if ($mysqli->connect_error) {
    die("Conexión fallida: " . $mysqli->connect_error);
}

// Obtener los aportes fijas (últimas 5)
$query_aport = "SELECT * FROM aportes ORDER BY id_usuario DESC LIMIT 5";
$result_aport = $mysqli->query($query_aport);

// Obtener las aportes de los usuarios
$query_aportes_usuario = "
    SELECT aportes.aporte, usuario.nombre, usuario.apellido
    FROM aportes 
    JOIN usuario ON aportes.id_usuario = usuario.id_usuario
    ORDER BY aportes.id_usuario DESC
";
$result_aportes_usuario = $mysqli->query($query_aportes_usuario);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preguntas Frecuentes</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/style-responsive.css" rel="stylesheet">

    <style>
       body {
            font-family: 'Ruda', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding-top: 30px;
        }

        .accordion-button {
            background-color: #147964; /* Pine green */
            color: white;
            border: none;
            border-radius: 8px;
        }

        .accordion-button:not(.collapsed) {
            background-color: #147964; /* Pine Green */
            color: white;  /* Text color for the opened state */
        }

        .accordion-button:not(.collapsed) .accordion-icon {
            color: #000000;  /* Black color for the arrow when opened */
        }

        .accordion-item {
            margin-bottom: 15px;
            border-radius: 10px;
            background-color: #ffffff;
            border: 1px solid #ddd;
        }

        .accordion-body {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
        }

        .btn-primary {
            background-color: #147964; /* Pine green */
            border-color: #147964;
            font-size: 16px;
            padding: 10px 20px;
            border-radius: 5px;
        }

        .btn-primary:hover {
            background-color: #116B57; /* Slightly darker for hover */
        }

        .form-container {
            margin-top: 30px;
        }

        .form-control {
            border-radius: 5px;
            border-color: #ddd;
        }

        .list-group-item {
            background-color: #f8f9fa;
            border: none;
            border-radius: 10px;
            margin-top: 10px;
            font-size: 16px;
        }

        .list-group-item:hover {
            background-color: #e9ecef;
        }

        .mt-5 {
            margin-top: 40px;
        }

        .modal-body textarea {
            resize: vertical;
            border-radius: 5px;
        }

        h3 {
            font-size: 32px;
            font-weight: bold;
            color: #116B57; /* Midnight Green for titles */
        }

        .preguntas-titulo {
            font-size: 28px;
            font-weight: bold;
            color: #137266; /* Pine Green */
        }
    </style>
</head>
<body>

<div class="container">
    <h3>Aportes de los Usuarios</h3>

    <div class="accordion" id="faqAccordion">
        <?php if ($result_aportes_usuario->num_rows > 0): ?>
            <?php $i = 0; ?>
            <?php while ($row = $result_aportes_usuario->fetch_assoc()): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading<?php echo $i; ?>">
                        <button class="accordion-button <?php echo ($i === 0) ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $i; ?>" aria-expanded="true" aria-controls="collapse<?php echo $i; ?>">
                        <?php echo $row['nombre'] . ' ' . $row['apellido']; ?>
                        </button>
                    </h2>
                    <div id="collapse<?php echo $i; ?>" class="accordion-collapse collapse <?php echo ($i === 0) ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $i; ?>" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <?php echo $row['aporte']; ?> <!-- Aquí puedes agregar el contenido del aporte -->
                        </div>
                    </div>
                </div>
                <?php $i++; ?>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No hay Aportes disponibles.</p>
        <?php endif; ?>
    </div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php
// Cerrar la conexión a la base de datos
$mysqli->close();
?>
