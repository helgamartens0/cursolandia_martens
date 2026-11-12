<?php
require_once __DIR__ . '/../includes/verificar_sesion.php';

//si no viene del formulario, vuelvo
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vistas/principal.php');
    exit;
}

require_once __DIR__ . '/../conexiones/conexion.php';
require_once __DIR__ . '/../controles/ControlUsuario.php';
require_once __DIR__ . '/../controles/ControlCurso.php';
require_once __DIR__ . '/../controles/ControlInscripcion.php';

$controlUsuario = new ControlUsuario($conexion);
$controlCurso = new ControlCurso($conexion);
$controlInscripcion = new ControlInscripcion($conexion);

$idUsuario = $_SESSION['id_usuario'];
$idCurso = (int) ($_POST['id_curso'] ?? 0);

//el curso tiene que existir
$curso = $controlCurso->buscarPorId($idCurso);
if ($curso === null) {
    header('Location: ../vistas/principal.php');
    exit;
}

$usuario = $controlUsuario->buscarPorId($idUsuario);

//vuelvo a revisar todas las reglas (aunque la pagina no muestre el boton, se controla aca)
$error = '';

if ($curso->getIdCreador() == $idUsuario) {
    $error = 'No te podes inscribir en un curso que creaste';                       
} elseif ($controlInscripcion->obtenerEstado($idUsuario, $idCurso) !== null) {
    $error = 'Ya estas inscripto en este curso';
} elseif (!$curso->estaActivo()) {
    $error = 'Solo te podes inscribir mientras el curso esta activo';              
} elseif (!$curso->hayCupoDisponible($controlInscripcion->contarInscriptos($idCurso))) {
    $error = 'No quedan lugares en este curso';
} elseif (!$usuario->puedeInscribirse($controlUsuario->contarInscripcionesActivas($idUsuario))) {
    $error = 'Llegaste al limite de inscripciones del plan gratuito';               
}

if ($error !== '') {
    $_SESSION['error_curso'] = $error;
    header('Location: ../vistas/curso.php?id=' . $idCurso);
    exit;
}

//si es pago o privado queda pendiente; si es gratis y publico queda confirmada
if ($curso->requiereConfirmacion()) {
    $controlInscripcion->inscribir($idUsuario, $idCurso, false);
    $_SESSION['aviso_curso'] = 'Tu inscripcion quedo pendiente: el creador del curso la tiene que confirmar';
} else {
    $controlInscripcion->inscribir($idUsuario, $idCurso, true);
    $_SESSION['aviso_curso'] = 'Listo! Ya estas inscripto.';
}

header('Location: ../vistas/curso.php?id=' . $idCurso);
exit;