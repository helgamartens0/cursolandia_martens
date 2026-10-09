<?php
session_start();

//solo se proecsa si llega desd el formulario:
if($_SERVER['REQUEST_METHOD']!== 'POST'){
    header('Location: ../vistas/registro.php');
    exit;
}

// 1) Leo los datos del formulario
$dni = trim($_POST['dni'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$email = trim($_POST['email'] ?? '');
$fechaNacimiento = $_POST['fecha_nacimiento'] ?? '';
$clave = $_POST['clave'] ?? '';
$clave2 = $_POST['clave2'] ?? '';

// 2) valido los formatos 
$errores = [];

if(!ctype_digit($dni) || (int) $dni < 1000000 || (int) $dni > 99999999){
    $errores[] = "EL DNI tiene que tener 7 u 8 numeros, sin puntos.";
}

// nombre y apellido: solo letras (con tildes y ñ), espacios, apostrofo o guion. Entre 2 y 20 caracteres.
// la /u del final es para que entienda las tildes y la ñ (UTF-8)
$soloLetras = "/^[A-Za-zÁÉÍÓÚáéíóúÜüÑñ][A-Za-zÁÉÍÓÚáéíóúÜüÑñ' -]{1,19}$/u";

if(!preg_match($soloLetras, $nombre)){
    $errores[] = "El nombre tiene que tener solo letras, entre 2 y 20 caracteres.";
}

if(!preg_match($soloLetras, $apellido)){
    $errores[] = "El apellido tiene que tener solo letras, entre 2 y 20 caracteres.";
}

if(!filter_var($email,FILTER_VALIDATE_EMAIL) || mb_strlen($email)>100){
    $errores[] = "El email NO es valido.";
}

$fecha = DateTime::createFromFormat('Y-m-d',$fechaNacimiento);
if(!$fecha || $fecha->format('Y-m-d') !== $fechaNacimiento || $fechaNacimiento>=date('Y-m-d') || $fechaNacimiento<'1920-01-01'){
    $errores[] = "Fecha de nacimiento No valida";
}

if (mb_strlen($clave) < 8) {
    $errores[] = 'La contraseña tiene que tener al menos 8 caracteres.';
} elseif ($clave !== $clave2) {
    $errores[] = 'Las contraseñas no coinciden.';
}

// 3) Si los formatos estan bien, valido q no haya cosas repetidas en la base (dni y email)
if(count($errores) === 0){
    require_once __DIR__.'/../conexiones/conexion.php';
    require_once __DIR__.'/../controles/ControlUsuario.php';
    
    $controlUsuario = new ControlUsuario($conexion);
    
    if($controlUsuario->existe((int) $dni)){
        $errores[] = "Ya existe una cuenta con ese DNI";
    }
    if($controlUsuario->buscarPorEmail($email) !== null){
        $errores[] = "Ya existe una cuenta con ese EMAIL";
    }
}

// 4) Si hubo errores, volvemos al formulario con los mensajes y todo lo escrito (menos las contraseñas)
if(count($errores)>0){
    $_SESSION['errores_registro'] = $errores;
    $_SESSION['datos_registro'] = [
        'dni' => $dni,
        'nombre' => $nombre,
        'apellido' => $apellido,
        'email' => $email,
        'fecha_nacimiento' => $fechaNacimiento
    ];
    
    header('Location: ../vistas/registro.php');
    exit;
}

// 5) Todo bien: lo damos de alta
$controlUsuario->registrar((int) $dni, $nombre, $apellido, $email, $clave, $fechaNacimiento);

// 6) y le iniciamos la sesion directamente (mapa de navegacion: Registro -> Principal)
$usuario = $controlUsuario->buscarPorEmail($email);
session_regenerate_id(true);
$_SESSION['id_usuario'] = $usuario->getId_usuario();
$_SESSION['nombre'] = $usuario->getNombre();
$_SESSION['iniciales'] = $usuario->getIniciales();

header('Location: ../vistas/principal.php');
exit;
