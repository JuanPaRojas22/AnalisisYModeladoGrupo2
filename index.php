<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

include 'template.php';

$username = $_SESSION['username'];
$nombre = $_SESSION['nombre'];
$direccion = isset($_SESSION['direccion_imagen']) ? $_SESSION['direccion_imagen'] : 'assets/img/default-profile.png'; // Imagen por defecto si no existe
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Dashboard">
    <title>Home</title>



    <style>
        body {

            margin: 0;
            padding: 0;
            font-family: 'Ruda', sans-serif;
            background-color: #f7f8fa;
            background-image: url('assets/progra/img9.webp');
            background-size: cover;
            background-position: center;



        }

        .hero-section {
            height: 100vh;
            z-index: 1;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            pointer-events: none;
            /* <- permite hacer clics a través de esta capa */

        }

        .hero-overlay {
            z-index: 0;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            pointer-events: none;
            /* <- permite hacer clics a través de esta capa */

            /* Sombra para hacer el texto más legible */
        }

        .hero-content {
            position: relative;
            z-index: 2;
            color: white;
            padding: 20px;
            width: 80%;
            /* Asegura que el contenido no ocupe todo el ancho */
        }

        .mission-vision {
            display: flex;
            justify-content: center;
            /* Centra las columnas horizontalmente */
            gap: 40px;
            /* Espacio entre columnas */
            width: 100%;
            margin-left: 15%;
            max-width: 1200px;
            text-align: center;
            /* Asegura que los títulos y descripciones se centren dentro de las columnas */
            margin-top: 20px;
            /* Ajuste para separar del borde superior */
        }

        .column {
            width: 45%;
            /* Establece el tamaño de cada columna */
            display: flex;
            flex-direction: column;
            align-items: center;
            max-width: 500px;
            text-align: center;
            /* Centra el contenido del texto dentro de la columna */
        }

        .hero-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .hero-description {
            font-size: 1.3rem;
            margin-bottom: 30px;
        }



        .buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            position: absolute;
            /* Posiciona los botones fuera del hero-content */
            top: 60%;
            /* Ajusta la posición vertical de los botones */
            left: 50%;
            /* Centra los botones horizontalmente */
            transform: translateX(-50%);
            /* Asegura que los botones estén perfectamente centrados */
            width: 100%;
            /* Asegura que ocupe todo el ancho */
            margin-top: 20px;
            /* Espacio adicional si es necesario */
        }

        .btn-hero {
            padding: 15px 30px;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 50px;
            transition: background-color 0.3s ease, transform 0.3s ease;
            text-decoration: none;
            /* Elimina el subrayado del enlace */
        }

        .btn-hero:hover {
            background-color: #1abc9c;
            transform: translateY(-5px);
        }

        .btn-hero:focus {
            outline: none;
        }

        .btn-hero.learn-more {
            background-color: #333;
            color: white;
        }

        .btn-hero.purchase {
            background-color: #1abc9c;
            color: white;
            margin-left: 8%;
            
        }
    </style>
</head>

<body>


    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <div class="mission-vision">
                <div class="column">
                    <h1 class="hero-title">Misión</h1>
                    <p class="hero-description">Brindar un entorno laboral organizado y justo para los médicos,
                        ofreciendo herramientas modernas y fáciles de usar para la administración de sus horarios y
                        beneficios.</p>
                </div>

                <div class="column">
                    <h1 class="hero-title">Visión</h1>
                    <p class="hero-description">Ser el aliado tecnológico líder en el sector salud, ofreciendo
                        soluciones que mejoren la experiencia de trabajo para los médicos y optimicen la gestión
                        administrativa para las instituciones de salud.</p>
                </div>
            </div>
        </div>
    </section>

    <div class="buttons">

        <a href="tutorial.php" class="btn-hero purchase">Tutorial de la Pagina</a>
    </div>


    <?php if (isset($_SESSION['mensaje_exito'])): ?>
        <script>
            // Usamos alert(), o puedes usar una librería o crear tu propio estilo
            alert("<?php echo addslashes($_SESSION['mensaje_exito']); ?>");
        </script>
        <?php
        unset($_SESSION['mensaje_exito']);
    endif;
    ?>
</body>

</html>