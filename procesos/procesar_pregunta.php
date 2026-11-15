<?php
require_once __DIR__ . '/../includes/verificar_sesion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vistas/principal.php');
    exit;
}

require_once __DIR__ . '/../conexiones/conexion.php';
require_once __DIR__ . '/../controles/ControlCurso.php';
require_once __DIR__ . '/../controles/ControlInscripcion.php';
require_once __DIR__ . '/../controles/ControlPregunta.php';
require_once __DIR__ . '/../controles/ControlNotificacion.php';

$idCurso = (int) ($_POST['id_curso'] ?? 0);
$texto = trim($_POST['texto'] ?? '');

$controlCurso = new ControlCurso($conexion);
$curso = $controlCurso->buscarPorId($idCurso);

if ($curso === null) {
    header('Location: ../vistas/principal.php');
    exit;
}

//iniciar una pregunta es solo para los inscriptos confirmados
$controlInscripcion = new ControlInscripcion($conexion);
if ($controlInscripcion->obtenerEstado($_SESSION['id_usuario'], $idCurso) !== 'confirmada') {
    header('Location: ../vistas/curso.php?id=' . $idCurso);
    exit;
}

$errores = [];

//error de q solo mientras el curso esta activo
if (!$curso->estaActivo()) {
    $errores[] = 'El foro está cerrado porque el curso no está en curso.';
}

if ($texto === '' || mb_strlen($texto) > 1000) {
    $errores[] = 'Escribí tu pregunta (hasta 1000 caracteres).';
}

if (count($errores) > 0) {
    $_SESSION['errores_foro'] = $errores;
    header('Location: ../vistas/foro.php?id=' . $idCurso);
    exit;
}

$controlPregunta = new ControlPregunta($conexion);
$idPregunta = $controlPregunta->crear($idCurso, $_SESSION['id_usuario'], $texto);

//aviso a todos los del curso (el creador y los inscriptos confirmados), menos al q hizo la preg
$usuariosCurso = [$curso->getCreador()];
foreach ($controlInscripcion->obtenerDeCurso($idCurso) as $inscripcion) {
    if ($inscripcion->estaConfirmada()) {
        $usuariosCurso[] = $inscripcion->getUsuario();
    }
}
$controlNotificacion = new ControlNotificacion($conexion);
$mensaje = $_SESSION['nombre'] . ' hizo una pregunta en ' . $curso->getTitulo() . ': "' . $controlNotificacion->resumir($texto) . '"';
$controlNotificacion->enviar($usuariosCurso, $idCurso, $idPregunta, 'preguntaCreada', $mensaje, $_SESSION['id_usuario']);

$_SESSION['aviso_foro'] = 'Pregunta publicada.';
header('Location: ../vistas/foro.php?id=' . $idCurso);
exit;