<?php
require_once __DIR__ . '/../includes/verificar_sesion.php';
require_once __DIR__ . '/../conexiones/conexion.php';
require_once __DIR__ . '/../controles/ControlEtiqueta.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vistas/editar_perfil.php');
    exit;
}

$controlEtiqueta = new ControlEtiqueta($conexion);
$idUsuario = $_SESSION['id_usuario'];   
$accion    = $_POST['accion'] ?? '';

if ($accion === 'agregar') {
    $nombre = trim($_POST['nombre'] ?? '');

    if (mb_strlen($nombre) < 2 || mb_strlen($nombre) > 30) {
        $_SESSION['errores_perfil'] = ['El interés tiene que tener entre 2 y 30 caracteres.'];
    } else {
        $etiqueta = $controlEtiqueta->buscarOcrear($nombre);
        $controlEtiqueta->agregarInteres($idUsuario, $etiqueta);
    }

} elseif ($accion === 'quitar') {
    $idEtiqueta = (int) ($_POST['id_etiqueta'] ?? 0);
    $controlEtiqueta->eliminarInteres($idUsuario, $idEtiqueta);
}

// En cualquier caso volvemos a editar perfil (PRG: así recargar la página no repite la acción)
header('Location: ../vistas/editar_perfil.php');
exit;