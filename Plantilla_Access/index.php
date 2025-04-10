<?php 
    session_start();
    require "template.php";
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: login.php");
        exit;
    }
   
    $username = $_SESSION['username'];
    $direccion = isset($_SESSION['direccion_imagen']) ? $_SESSION['direccion_imagen'] : 'assets/img/default-profile.png'; // Imagen por defecto si no existe
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Dashboard">
    <title>Creative Design</title>

    <!-- Bootstrap core CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f7f8fa;
            background-image: url('assets/progra/img6.jpg'); 
            background-size: cover;
            background-position: center;
            

        }

        .hero-section {
            height: 100vh;
            
           
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            position: relative;
        }

        .hero-overlay {
            z-index:2;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4); /* Sombra para hacer el texto más legible */
        }

        .hero-content {
            z-index:2;
            Position:relative;
        }

        .hero-title {
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 20px;
            letter-spacing: 2px;
        }

        .hero-description {
            font-size: 1.3rem;
            margin-bottom: 30px;
        }

        .hero-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .btn-hero {
            padding: 15px 30px;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 50px;
            transition: background-color 0.3s ease, transform 0.3s ease;
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
        }
    </style>
</head>
<body>


    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1 class="hero-title">CREATIVE DESIGN</h1>
            <p class="hero-description">Enigma es un diseño creativo y minimalista. Es completamente adaptable y listo para retina. Adquiere esta increíble plantilla ahora.</p>
            <div class="hero-buttons">
                <a href="tutorial.php" class="btn-hero learn-more">Tutorial</a>
                <a href="#purchase" class="btn-hero purchase">PURCHASE IT</a>
            </div>
        </div>
    </section>

    
    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
