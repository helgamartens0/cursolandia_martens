<?php
require_once __DIR__ . '/../includes/verificar_sesion.php';

//si no viene del formulario, vuelvo a la pagina principal
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vistas/principal.php');
    exit;
}

require_once __DIR__ . '/../conexiones/conexion.php';
require_once __DIR__ . '/../controles/ControlCurso.php';
require_once __DIR__ . '/../controles/ControlInscripcion.php';

$idInscripcion = (int) ($_POST['id_inscripcion'] ?? 0);
$idCurso = (int) ($_POST['id_curso'] ?? 0);
$accion = $_POST['accion'] ?? '';

//el curso tiene que existir y el que confirma tiene que ser su creador
$controlCurso = new ControlCurso($conexion);
$curso = $controlCurso->buscarPorId($idCurso);

if ($curso === null || $curso->getIdCreador() != $_SESSION['id_usuario']) {
    header('Location: ../vistas/principal.php'); //x las du
    exit;
}

$controlInscripcion = new ControlInscripcion($conexion);

if ($accion === 'confirmar') {
    $controlInscripcion->confirmar($idInscripcion, $idCurso);
    $_SESSION['mensaje_inscripciones'] = 'Inscripcion confirmada.';
} elseif ($accion === 'rechazar') {
    $controlInscripcion->rechazar($idInscripcion, $idCurso);
    $_SESSION['mensaje_inscripciones'] = 'Inscripcion rechazada.';
}

header('Location: ../vistas/inscripciones.php?id=' . $idCurso);
exit;