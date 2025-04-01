<?php
// Se hace la conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "GestionEmpleados");
mysqli_set_charset($conn, "utf8mb4");


// Se valida la conexión a la base de datos
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}


// Inicializar variables de error y mensaje
session_start();
$error_message = ""; // Variable para manejar errores

// Se verifica si el método de solicitud es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    // Se verifica si los campos de usuario y contraseña no están vacíos
    if (!empty($username) && !empty($password)) {
        $sql = "SELECT * FROM USUARIO WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        // Se verifica si el usuario existe
        if ($result->num_rows > 0) {
            $usuario = $result->fetch_assoc();
            // Se verifica si la contraseña coincide con la de la base de datos
            if (isset($usuario['password']) && password_verify($password , $usuario['password'])) {
                $_SESSION['id_usuario'] = $usuario['id_usuario'];
                $_SESSION['username'] = $usuario['nombre'];
                $_SESSION['id_rol'] = $usuario['id_rol']; 
                $_SESSION['logged_in'] = true;

                header("Location: index.php?login=success&username=" . urlencode($usuario['nombre']));
                exit();
            } else {
                // Se muestra un mensaje de error si la contraseña no coincide
                $error_message = "Contraseña incorrecta.";
            }
        } else {
            // Se muestra un mensaje de error si el usuario no existe
            $error_message = "Usuario no registrado.";
        }
    } else {
        // Se muestra un mensaje de error si los campos de usuario y contraseña están vacíos, es decir que no se se hizo la solicitud POST
        $error_message = "Por favor, completa todos los campos.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Dashboard">
    <meta name="keyword" content="Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">

    <title>DASHGUM - Bootstrap Admin Template</title>

    <!-- Bootstrap core CSS -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <!--external css-->
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
        
    <!-- Custom styles for this template -->
    <link href="assets/css/style-responsive.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <style>
        body {
       
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            width: 100%;
            max-width: 400px;
        }

        .form-login {
            background: rgba(236, 231, 231, 0.2);
            backdrop-filter: blur(75px);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-left: 15%;
        }

        .form-login h2 {
            color: white;
            margin-bottom: 20px;
            font-weight: bold;

        }

        .form-control {
            background: rgba(255, 255, 255, 0.5);
            border: none;
            border-radius: 5px;
            color: black;
            font-weight: bold;
        }

        .form-control::placeholder {
            color: black;
            font-weight: bold;
        }

        .login-wrap label {
            color: white;
            font-weight: bold;
        }

        .btn-theme {
            background: rgba(95, 94, 94, 0.3);
            border: none;
            color: white;
            font-weight: bold;
        }

        .btn-theme:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        .login-social-link button {
            background: rgba(0, 0, 0, 0.3);
            border: none;
            color: white;
            font-weight: bold;
            
        }

        .login-social-link button:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        .registration a {
            color:rgb(63, 62, 62);
            font-weight: bold;
        }
    </style>

      <!-- **********************************************************************************************************************************************************
      MAIN CONTENT
      *********************************************************************************************************************************************************** -->

	  <div id="login-page">
	  	<div class="container">
	  	
		      <form class="form-login" action="login.php" method="post">
		        <h2 class="form-login-heading">SIGN IN</h2>
		        <div class="login-wrap">
                    
		            <input type="text" name="username" class="form-control" placeholder="User" autofocus>
		            <br>
		            <input type="password" name="password" class="form-control" placeholder="Password">
		            <label class="checkbox">
		                <span class="pull-right">
		                    <a data-toggle="modal" href="login.php#myModal"> Forgot Password?</a>
		
		                </span>
		            </label>
		            <button class="btn btn-theme btn-block" type="submit"><i class="fa fa-lock"></i> SIGN IN</button>
                    <?php if ($error_message): ?>
                        <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
                    <?php endif; ?>
		            <hr>
                
		            <div class="login-social-link centered">
		            <p style="font-weight: bold;">or you can sign in via your social network</p>
		                <button class="btn btn-facebook" type="submit"><i class="fa fa-facebook"></i> Facebook</button>
		                <button class="btn btn-twitter" type="submit"><i class="fa fa-twitter"></i> Twitter</button>
		            </div>
		            <div class="registration" style="font-weight: bold;">
		                Don't have an account yet?<br/>
		                <a class="" href="createUser.php" style="font-weight: bold;">
		                    Create an account
		                </a>
		            </div>
             </form>
		</div>
		
		          <!-- Modal -->
		          <div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal" class="modal fade">
		              <div class="modal-dialog">
		                  <div class="modal-content">
		                      <div class="modal-header">
		                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		                          <h4 class="modal-title">Forgot Password ?</h4>
		                      </div>
		                      <div class="modal-body">
		                          <p>Enter your e-mail address below to reset your password.</p>
		                          <input type="text" name="email" placeholder="Email" autocomplete="off" class="form-control placeholder-no-fix">
		
		                      </div>
		                      <div class="modal-footer">
		                          <button data-dismiss="modal" class="btn btn-default" type="button">Cancel</button>
		                          <button class="btn btn-theme" type="button">Submit</button>
		                      </div>
		                  </div>
		              </div>
		          </div>
		          <!-- modal -->
		
		      </form>	  	
	  	
	  	</div>
	  </div>

    <!-- js placed at the end of the document so the pages load faster -->
    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>

    <!--BACKSTRETCH-->
    <!-- You can use an image of whatever size. This script will stretch to fit in any screen size.-->
    <script type="text/javascript" src="assets/js/jquery.backstretch.min.js"></script>
    <script>
        $.backstretch("assets/img/loginbg.jpg", {speed: 500});
    </script>


  </body>
</html>
