<?php
session_start();
include 'template.php';

// Parámetros de conexión
$host = "accespersoneldb.mysql.database.azure.com";
$user = "adminUser";
$password = "admin123+";
$dbname = "gestionEmpleados";
$port = 3306;

// Ruta al certificado SSL (ajústala si es necesario)
$ssl_ca = '/home/site/wwwroot/certs/BaltimoreCyberTrustRoot.crt.pem';

// Inicializamos conexión
$conn = mysqli_init();

// Configuramos SSL
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);

// Intentamos conectar
if (!$conn->real_connect($host, $user, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("❌ Error de conexión: " . mysqli_connect_error());
}

// Establecemos charset
mysqli_set_charset($conn, "utf8mb4");

// Obtener preguntas frecuentes (últimas 5)
$query_faq = "SELECT * FROM preguntasfrecuentes ORDER BY fecha_creacion DESC LIMIT 5";
$result_faq = $conn->query($query_faq);

// Obtener preguntas de los usuarios
$query_preguntas_usuario = "SELECT * FROM preguntas_usuario ORDER BY fecha_creacion DESC";
$result_preguntas_usuario = $conn->query($query_preguntas_usuario);

// Procesar el formulario de agregar pregunta del usuario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pregunta_usuario'])) {
    $pregunta_usuario = $_POST['pregunta_usuario'];
    $usuario_creacion = $_SESSION['username'] ?? 'anónimo';
    $fecha_creacion = date('Y-m-d');

    $query = "INSERT INTO preguntas_usuario (pregunta, usuario_creacion, fecha_creacion) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $pregunta_usuario, $usuario_creacion, $fecha_creacion);
    $stmt->execute();
    $stmt->close();

    $result_preguntas_usuario = $conn->query($query_preguntas_usuario);
}

// Procesar el formulario de agregar pregunta frecuente
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pregunta_faq']) && isset($_POST['respuesta_faq'])) {
    $pregunta_faq = $_POST['pregunta_faq'];
    $respuesta_faq = $_POST['respuesta_faq'];
    $fecha_creacion = date('Y-m-d');

    $query = "INSERT INTO preguntasfrecuentes (pregunta, respuesta, fecha_creacion) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $pregunta_faq, $respuesta_faq, $fecha_creacion);
    $stmt->execute();
    $stmt->close();

    $result_faq = $conn->query($query_faq);
}
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

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
            background-color: #147964;
            /* Pine green */
            color: white;
            border: none;
            border-radius: 8px;
        }

        .accordion-button:not(.collapsed) {
            background-color: #147964;
            /* Pine Green */
            color: white;
            /* Text color for the opened state */
        }

        .accordion-button:not(.collapsed) .accordion-icon {
            color: #000000;
            /* Black color for the arrow when opened */
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
            background-color: #147964;
            /* Pine green */
            border-color: #147964;
            font-size: 16px;
            padding: 10px 20px;
            border-radius: 5px;
        }

        .btn-primary:hover {
            background-color: #116B57;
            /* Slightly darker for hover */
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
            color: #116B57;
            /* Midnight Green for titles */
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
        <h3>Preguntas Frecuentes</h3>

        <div class="accordion" id="faqAccordion">
            <?php if ($result_faq->num_rows > 0): ?>
                <?php $i = 0; ?>
                <?php while ($row = $result_faq->fetch_assoc()): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading<?php echo $i; ?>">
                            <button class="accordion-button <?php echo ($i === 0) ? '' : 'collapsed'; ?>" type="button"
                                data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $i; ?>" aria-expanded="true"
                                aria-controls="collapse<?php echo $i; ?>">
                                <?php echo $row['pregunta']; ?>
                            </button>
                        </h2>
                        <div id="collapse<?php echo $i; ?>"
                            class="accordion-collapse collapse <?php echo ($i === 0) ? 'show' : ''; ?>"
                            aria-labelledby="heading<?php echo $i; ?>" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <?php echo $row['respuesta']; ?>
                            </div>
                        </div>
                    </div>
                    <?php $i++; ?>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No hay preguntas frecuentes disponibles.</p>
            <?php endif; ?>
            <!-- Botón para abrir el modal de agregar FAQ -->
            <button id="openFaqModalBtn" class="btn btn-primary mt-3" style="background-color: #09354b;">Agregar Pregunta</button>

            <!-- Modal para agregar FAQ -->
            <div id="faqModal" class="modal">
                <div class="modal-content">
                    <span class="close" id="closeFaqModalBtn">&times;</span>
                    <h2>Agregar Pregunta Frecuente</h2>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="pregunta_faq" class="form-label">Pregunta:</label>
                            <textarea name="pregunta_faq" id="pregunta_faq" class="form-control" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="respuesta_faq" class="form-label">Respuesta:</label>
                            <textarea name="respuesta_faq" id="respuesta_faq" class="form-control" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </form>
                </div>
            </div>


        </div>
    </div>




</body>


</html>
<script>
    const faqModal = document.getElementById('faqModal');
    const openFaqBtn = document.getElementById('openFaqModalBtn');
    const closeFaqBtn = document.getElementById('closeFaqModalBtn');

    openFaqBtn.addEventListener('click', () => {
        faqModal.style.display = 'block';
    });

    closeFaqBtn.addEventListener('click', () => {
        faqModal.style.display = 'none';
    });

    window.addEventListener('click', (event) => {
        if (event.target === faqModal) {
            faqModal.style.display = 'none';
        }
    });
</script>
<?php
// Cerrar la conexión a la base de datos
$mysqli->close();
?>
