<?php
require_once __DIR__ . '/Impl/UsuarioDAOSImpl.php';  

// Verifica si el parámetro id está presente en la URL
if (isset($_GET['id'])) {
    $userId = $_GET['id'];//Se obtiene el ID del usuario desde la URL

    // Instanciamos el DAO para interactuar con la base de datos
    $UsuarioDAO = new UsuarioDAOSImpl();
    
    // se llama al método que realiza el borrado lógico
    $result = $UsuarioDAO->deleteUser($userId);

    // revisa el resultado y muestra un mensaje
    if ($result) {
        echo "<script>
                alert('Usuario desactivado exitosamente.');
                window.location.href = 'index.php';  // Redirige de vuelta a la página principal
              </script>";
    } else {
        echo "<script>
                alert('Error al desactivar el usuario.');
                window.location.href = 'index.php';  // Redirige de vuelta a la página principal
              </script>";
    }
} else {
    echo "<script>
            alert('No se especificó un ID de usuario.');
            window.location.href = 'index.php';  // Redirige de vuelta a la página principal
          </script>";
}
?>
