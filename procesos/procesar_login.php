<?php 

session_start(); //abrimos la sesion 

// Si alguien entra a este archivo escribiendo la URL (sin enviar el formulario), lo devolvemos al inicio
//sin procesar nada 
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header('Location: ../index.php');
    exit;
}


$email = trim($_POST['email'] ?? ''); //el ?? '' para evitar el warning de clave de array indefinida algo asi
$clave = $_POST['clave'] ?? ''; //tmb

require_once __DIR__ . '/../conexiones/conexion.php';
require_once __DIR__ . '/../controles/ControlUsuario.php';

$controlUsuario = new ControlUsuario($conexion);
$usuario = $controlUsuario->buscarPorEmail($email);

if($usuario !== null && $usuario->verificarContrasenia($clave)){
    //todo bien: guardo quien es el q entro
    session_regenerate_id(true);
    $_SESSION['id_usuario'] = $usuario->getId_usuario(); //($_SESSION es un arreglo q PHP recuerda entre pagina y pagina)
    $_SESSION['nombre'] = $usuario->getNombre();
    $_SESSION['iniciales'] = $usuario->getIniciales();
    
    header('Location: ../vistas/principal.php');
    exit;
    
}


    //todo mal: mostramos error
    $_SESSION['error_login'] = 'El email o la contraseña no son correctos';
    $_SESSION['email_login'] = $email;
    header('Location: ../index.php');
    exit;
