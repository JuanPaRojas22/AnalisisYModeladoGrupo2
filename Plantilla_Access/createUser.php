createUser.php:
<?php
// Se hace la conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "GestionEmpleados");

// Se valida la conexión a la base de datos
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Mostrar todos los usuarios activos

// Se Verifica si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Se capturar datos del formulario
    $id_departamento = $_POST['id_departamento'];
    $id_rol = 3; // Valor fijo que hace referencia al que el rol prederteminado es de usuario (si se quiere ser admin, se debe notificar a un admin)
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $fecha_ingreso = date("Y-m-d");
    $cargo = $_POST['cargo'];
    $correo_electronico = $_POST['correo_electronico'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $numero_telefonico = $_POST['numero_telefonico'];
    $direccion_imagen = null; // Por defecto sera null
    $sexo = $_POST['sexo'];
    $estado_civil = $_POST['estado_civil'];
    $fechacreacion = date("Y-m-d H:i:s");
    $usuariocreacion = "admin"; //Esto debe mostrar el nombree de usuario no el rol
    $fechamodificacion = date("Y-m-d H:i:s");
    $usuariomodificacion = "admin";

    // Se validan los datos por si estan vacios
    if (empty($id_departamento) || empty($nombre) || empty($apellido) || empty($correo_electronico) || empty($username) || empty($password)) {
        echo "<script>alert('Por favor, complete todos los campos obligatorios.');</script>";
    } else {

        // Se manejara este atributo para la subida de una imagen

        // Manejo de la imagen
        if (isset($_FILES['direccion_imagen']) && $_FILES['direccion_imagen']['error'] === UPLOAD_ERR_OK) {
            // Leer el contenido del archivo y convertirlo en binario
            $direccion_imagen = file_get_contents($_FILES['direccion_imagen']['tmp_name']);
        } else {
            echo "<script>alert('No se subió ningún archivo o ocurrió un error.');</script>";
        }



        // Se prepara la consulta a la base de datos
        $stmt = $conn->prepare("INSERT INTO Usuario 
            (id_departamento, id_rol, nombre, apellido, 
            fecha_nacimiento, fecha_ingreso, cargo, correo_electronico, 
            username, password, numero_telefonico, direccion_imagen, 
            sexo, estado_civil, fechacreacion, usuariocreacion, 
            fechamodificacion, usuariomodificacion) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if ($stmt) {
            //  Se asignan los parametros recibidos por el formulario
            $stmt->bind_param(
                "iissssssssssssssss",
                $id_departamento,
                $id_rol,
                $nombre,
                $apellido,
                $fecha_nacimiento,
                $fecha_ingreso,
                $cargo,
                $correo_electronico,
                $username,
                $password,
                $numero_telefonico,
                $direccion_imagen,
                $sexo,
                $estado_civil,
                $fechacreacion,
                $usuariocreacion,
                $fechamodificacion,
                $usuariomodificacion
            );

            // Se ejecuta la consulta
            if ($stmt->execute()) {
                echo "<script>alert('Usuario registrado exitosamente.');</script>";
            } else {
                echo "<script>alert('Error al registrar usuario: " . $stmt->error . "');</script>";
            }

            // Se cierra la declaración
            $stmt->close();
        } else {
            echo "<script>alert('Error en la preparación de la consulta: " . $conn->error . "');</script>";
        }
    }
}

// Se obtienen los departamentos actuales para el formulario
$departamentos = $conn->query("SELECT id_departamento, Nombre FROM Departamento");
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
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/style-responsive.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>

      <!-- **********************************************************************************************************************************************************
      MAIN CONTENT
      *********************************************************************************************************************************************************** -->

	  <div id="login-page">
	  	<div class="container">
	  	
              <form class="form-login" action="createUser.php" method="POST" enctype="multipart/form-data">
		        <h2 class="form-login-heading">Registro</h2>
		        <div class="login-wrap">

	                
				    <br>
                    <label for="id_departamento">Departamento:</label>
					<select id="id_departamento" name="id_departamento" class="form-control">
						<option value="" disabled selected>Seleccione su departamento</option>
						<?php
						if ($departamentos->num_rows > 0) {
							while ($row = $departamentos->fetch_assoc()) {
								echo '<option value="' . $row['id_departamento'] . '">' . $row['Nombre'] . '</option>';
							}
						} else {
							echo '<option value="">No hay departamentos disponibles</option>';
						}
						?>
					</select>
					<br>
                    <label for="nombre">Nombre:</label>
		            <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Ingrese su nombre" autofocus>
                    <br>
                    <label for="apellido">Apellidos:</label>
		            <input type="text" id="apellido" name="apellido" class="form-control" placeholder="Ingrese sus apellidos" autofocus>
                    <br>
                    <label for="fecha_nacimiento">Nacimiento:</label>
		            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-control" placeholder="Ingrese su fecha de nacimiento" autofocus>
                    <br>
                    <label for="cargo">Cargo:</label>
		            <input type="text" id="cargo" name="cargo" class="form-control" placeholder="User ID" autofocus>
                    <br>
                    <label for="correo_electronico">Correo electronico:</label>
		            <input type="text" id="correo_electronico" name="correo_electronico" class="form-control" placeholder="Ingrese su correo electronico" autofocus>
                    <br>
                    <label for="username">Username:</label>
		            <input type="text" id="username" name="username" class="form-control" placeholder="Ingrese su nombre su username" autofocus>
                    <br>
                    <label for="password">Contraseña:</label>
		            <input type="password" id="password" name="password" class="form-control" placeholder="Ingrese su contraseña" autofocus>
                    <br>
                    <label for="numero_telefonico">Número telefónico:</label>
		            <input type="text" id="numero_telefonico" name="numero_telefonico" class="form-control" placeholder="Ingrese su numero telefonico" autofocus>
                    <br>
                    <label for="direccion_imagen">Foto Perfil:</label>
		            <input type="file" id="direccion_imagen" name="direccion_imagen" class="form-control" placeholder="Ingrese su foto de perfil" autofocus>
                    
                    <br>
                    <label for="sexo">Sexo:</label>
		            <input type="text" id="sexo" name="sexo" class="form-control" placeholder="Ingresu su sexo" autofocus>
                    <br>
                    <label for="estado_civil">Estado Civil:</label>
		            <input type="text" id="estado_civil" name="estado_civil" class="form-control" placeholder="Ingrese su estado civil" autofocus>
                    <br>
					
		            
		            <button class="btn btn-theme btn-block" type="submit"><i class="fa fa-unlock"></i> REGISTRARTE</button>
		            <hr>
		            
		
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
        $.backstretch("assets/img/login-bg.jpg", {speed: 500});
    </script>


  </body>
</html>
