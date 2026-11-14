<?php
require_once __DIR__ . '/../includes/verificar_sesion.php';
require_once __DIR__ . '/../conexiones/conexion.php';
require_once __DIR__ . '/../controles/ControlCurso.php';
require_once __DIR__ . '/../controles/ControlArchivo.php';
require_once __DIR__ . '/../controles/ControlInscripcion.php';

$idArchivo = (int) ($_GET['id'] ?? 0);
$idCurso = (int) ($_GET['curso'] ?? 0);

$controlCurso = new ControlCurso($conexion);
$curso = $controlCurso->buscarPorId($idCurso);

if ($curso === null) {
    header('Location: principal.php');
    exit;
}

//el material lo ven el creador y los inscriptos confirmados
$controlInscripcion = new ControlInscripcion($conexion);
$esCreador = $curso->getIdCreador() == $_SESSION['id_usuario'];
$estaConfirmado = $controlInscripcion->obtenerEstado($_SESSION['id_usuario'], $idCurso) === 'confirmada';

if (!$esCreador && !$estaConfirmado) {
    header('Location: curso.php?id=' . $idCurso);
    exit;
}

$controlArchivo = new ControlArchivo($conexion);
$archivo = $controlArchivo->buscarDeCurso($idArchivo, $idCurso);

if ($archivo === null || !is_file(__DIR__ . '/../' . $archivo->getUrl())) {
    http_response_code(404);
    exit('No se encontró el archivo.');
}

$archivo->descargar();
exit;