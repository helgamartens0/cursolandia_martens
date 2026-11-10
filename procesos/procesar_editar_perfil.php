<?php
require_once __DIR__ . '/../includes/verificar_sesion.php';
require_once __DIR__ . '/../conexiones/conexion.php';
require_once __DIR__ . '/../controles/ControlUsuario.php';
$controlUsuario = new ControlUsuario($conexion);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vistas/editar_perfil.php');
    exit;
}


$email           = trim($_POST['email'] ?? '');
$fechaNacimiento = $_POST['fecha_nacimiento'] ?? '';
$telefono        = trim($_POST['telefono'] ?? '');
$antecedentes    = trim($_POST['antecedentes'] ?? '');

$errores = [];

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {
    $errores[] = 'El email no es válido.';
}

$fecha = DateTime::createFromFormat('Y-m-d', $fechaNacimiento);
if (!$fecha || $fecha->format('Y-m-d') !== $fechaNacimiento
    || $fechaNacimiento > '2010-12-31' || $fechaNacimiento < '1920-01-01') {
    $errores[] = 'La fecha de nacimiento tiene que estar entre 1920 y 2010.';
}


if ($telefono !== '' && !preg_match('/^[0-9+()\- ]{6,20}$/', $telefono)) {
    $errores[] = 'El teléfono solo puede tener números, espacios y + - ( ), entre 6 y 20 caracteres.';
}

if (mb_strlen($antecedentes) > 1000) {
    $errores[] = 'El texto de "Sobre mí" puede tener hasta 1000 caracteres.';
}


//si no hubo errores, me fijo un error extra del email:
if (count($errores) === 0) {
    $otro = $controlUsuario->buscarPorEmail($email); // o sea busco x mail para ver q no haya puesto un mail q este en otra cuenta
    if ($otro !== null && $otro->getId_usuario() != $_SESSION['id_usuario']) {
        $errores[] = 'Ese email ya lo usa otra cuenta.';
    }
}

//si hubo errores, vuelvo al formulario con los mensajes
if (count($errores) > 0) {
    $_SESSION['errores_perfil'] = $errores;
    header('Location: ../vistas/editar_perfil.php');
    exit;
}

//guardo  la actualizasaon 
$controlUsuario->actualizarPerfil(
    $_SESSION['id_usuario'],
    $email,
    $fechaNacimiento,
    $telefono === '' ? null : $telefono,
    $antecedentes === '' ? null : $antecedentes
);

$_SESSION['mensaje_perfil'] = 'Cambios guardados.';
header('Location: ../vistas/editar_perfil.php');
exit;