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
require_once __DIR__ . '/../controles/ControlRespuesta.php';

$idPregunta = (int) ($_POST['id_pregunta'] ?? 0);
$idCurso = (int) ($_POST['id_curso'] ?? 0);
$accion = $_POST['accion'] ?? '';          //'responder' o 'editar'
$texto = trim($_POST['texto'] ?? '');

$controlCurso = new ControlCurso($conexion);
$curso = $controlCurso->buscarPorId($idCurso);

if ($curso === null) {
    header('Location: ../vistas/principal.php');
    exit;
}

//solo responden el creador y los inscriptos confirmados
$controlInscripcion = new ControlInscripcion($conexion);
$esCreador = $curso->getIdCreador() == $_SESSION['id_usuario'];
$estaConfirmado = $controlInscripcion->obtenerEstado($_SESSION['id_usuario'], $idCurso) === 'confirmada';

if (!$esCreador && !$estaConfirmado) {
    header('Location: ../vistas/curso.php?id=' . $idCurso);
    exit;
}

$controlPregunta = new ControlPregunta($conexion);
$pregunta = $controlPregunta->buscarDeCurso($idPregunta, $idCurso);

if ($pregunta === null) {
    header('Location: ../vistas/foro.php?id=' . $idCurso);
    exit;
}

//le cargo las respuestas para poder ver quien respondio ultimo
$controlRespuesta = new ControlRespuesta($conexion);
foreach ($controlRespuesta->obtenerDePregunta($pregunta->getId_pregunta()) as $respuesta) {
    $pregunta->agregarRespuesta($respuesta);
}

$errores = [];

if (!$curso->estaActivo()) {
    $errores[] = 'El foro está cerrado porque el curso no está en curso.';   //RN05
} elseif ($pregunta->estaCerrada()) {
    $errores[] = 'La pregunta está cerrada.';
} elseif ($texto === '' || mb_strlen($texto) > 1000) {
    $errores[] = 'Escribí tu respuesta (hasta 1000 caracteres).';
}


//no se puede participar dos veces seguidas (la pregunta cuenta como la primera vez)
$ultimoAutor = $pregunta->autorUltimaRespuesta();
$participeUltimo = $ultimoAutor->getId_usuario() == $_SESSION['id_usuario'];
$ultimaRespuesta = $pregunta->ultimaRespuesta();

if ($accion === 'responder' && $participeUltimo) {
    if ($ultimaRespuesta === null) {
        $errores[] = 'Tenés que esperar a que otra persona responda tu pregunta.';
    } else {
        $errores[] = 'No podés responder dos veces seguidas. Editá tu última respuesta o esperá a que responda otra persona.';
    }
}

//CU-15: solo se edita la ultima respuesta, y solo si es mia
if ($accion === 'editar' && ($ultimaRespuesta === null || !$participeUltimo)) {
    $errores[] = 'Solo podés editar tu respuesta mientras sea la última.';
}

if (count($errores) > 0) {
    $_SESSION['errores_pregunta'] = $errores;
    header('Location: ../vistas/pregunta.php?id=' . $idPregunta . '&curso=' . $idCurso);
    exit;
}

if ($accion === 'responder') {
    $controlRespuesta->crear($idPregunta, $_SESSION['id_usuario'], $texto);
    //aca despues pongo la notificacion a los participantes de la pregunta (buscar creo q estaba en mi archivo aparte en el drive)
    $_SESSION['aviso_pregunta'] = 'Respuesta publicada.';
} elseif ($accion === 'editar') {
    $controlRespuesta->editar($ultimaRespuesta->getId_respuesta(), $_SESSION['id_usuario'], $texto);   
    $_SESSION['aviso_pregunta'] = 'Respuesta editada.';
}

header('Location: ../vistas/pregunta.php?id=' . $idPregunta . '&curso=' . $idCurso);
exit;