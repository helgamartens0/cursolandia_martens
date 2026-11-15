<?php
require_once __DIR__ . '/../includes/verificar_sesion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vistas/editar_perfil.php');
    exit;
}

require_once __DIR__ . '/../conexiones/conexion.php';
require_once __DIR__ . '/../controles/ControlUsuario.php';

$actual = $_POST['actual'] ?? '';
$nueva = $_POST['nueva'] ?? '';
$nueva2 = $_POST['nueva2'] ?? '';

$controlUsuario = new ControlUsuario($conexion);
$usuario = $controlUsuario->buscarPorId($_SESSION['id_usuario']);

$errores = [];

//mismas reglas que en el registro
if (mb_strlen($nueva) < 8) {
    $errores[] = 'La contraseña nueva tiene que tener al menos 8 caracteres.';
} elseif ($nueva !== $nueva2) {
    $errores[] = 'Las contraseñas nuevas no coinciden.';
} elseif ($nueva === $actual) {
    $errores[] = 'La contraseña nueva tiene que ser distinta de la actual.';
}

//el usuario revisa que la actual sea la suya
if (count($errores) === 0 && !$usuario->cambiarContrasenia($actual, $nueva)) {
    $errores[] = 'La contraseña actual no es correcta.';
}

if (count($errores) > 0) {
    $_SESSION['errores_perfil'] = $errores;
    header('Location: ../vistas/editar_perfil.php');
    exit;
}

//el control la encripta y la guarda (igual que en el registro)
$controlUsuario->actualizarContrasenia($usuario->getId_usuario(), $nueva);

$_SESSION['mensaje_perfil'] = 'Contraseña actualizada.';
header('Location: ../vistas/editar_perfil.php');
exit;