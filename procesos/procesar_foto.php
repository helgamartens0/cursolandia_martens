<?php
require_once __DIR__ . '/../includes/verificar_sesion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vistas/editar_perfil.php');
    exit;
}

require_once __DIR__ . '/../conexiones/conexion.php';
require_once __DIR__ . '/../controles/ControlUsuario.php';

$controlUsuario = new ControlUsuario($conexion);
$usuario = $controlUsuario->buscarPorId($_SESSION['id_usuario']);

//la foto: igual que el comprobante, pero solo imagenes y hasta 2 MB
$errores = [];
$foto = $_FILES['foto'] ?? null;
$extensionesPermitidas = ['jpg', 'jpeg', 'png'];
$tiposPermitidos = ['image/jpeg', 'image/png'];
$extension = '';

if ($foto === null || $foto['error'] !== UPLOAD_ERR_OK) {
    $errores[] = 'Elegí una foto (hasta 2 MB).';
} elseif ($foto['size'] > 2 * 1024 * 1024) {
    $errores[] = 'La foto no puede pesar más de 2 MB.';
} else {
    $extension = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION));
    $tipoReal = (new finfo(FILEINFO_MIME_TYPE))->file($foto['tmp_name']);

    if (!in_array($extension, $extensionesPermitidas) || !in_array($tipoReal, $tiposPermitidos)) {
        $errores[] = 'La foto tiene que ser JPG o PNG.';
    }
}

if (count($errores) > 0) {
    $_SESSION['errores_perfil'] = $errores;
    header('Location: ../vistas/editar_perfil.php');
    exit;
}

//va a img/perfiles y no a uploads, porque las fotos las ve todo el mundo
$carpeta = __DIR__ . '/../img/perfiles';
if (!is_dir($carpeta)) {
    mkdir($carpeta, 0755, true);
}
$nombreFoto = $usuario->getId_usuario() . '_' . time() . '.' . $extension;

if (!move_uploaded_file($foto['tmp_name'], $carpeta . '/' . $nombreFoto)) {
    $_SESSION['errores_perfil'] = ['No se pudo guardar la foto. Probá de nuevo.'];
    header('Location: ../vistas/editar_perfil.php');
    exit;
}

//si ya tenia una foto, borro la vieja para que no quede ocupando lugar
if ($usuario->tieneFoto()) {
    $vieja = __DIR__ . '/../' . $usuario->getImagen_principal();
    if (is_file($vieja)) {
        unlink($vieja);
    }
}

$ruta = 'img/perfiles/' . $nombreFoto;
$controlUsuario->actualizarFoto($usuario->getId_usuario(), $ruta);

$_SESSION['foto'] = $ruta;   //asi la navbar la muestra 
$_SESSION['mensaje_perfil'] = 'Foto actualizada.';
header('Location: ../vistas/editar_perfil.php');
exit;