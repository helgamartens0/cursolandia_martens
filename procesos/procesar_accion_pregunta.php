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
require_once __DIR__ . '/../controles/ControlNotificacion.php';

$idPregunta = (int) ($_POST['id_pregunta'] ?? 0);
$idCurso = (int) ($_POST['id_curso'] ?? 0);
$idRespuesta = (int) ($_POST['id_respuesta'] ?? 0);   //solo viene al aceptar
$accion = $_POST['accion'] ?? '';                     //'aceptar', 'destacar' o 'eliminar'

$controlCurso = new ControlCurso($conexion);
$curso = $controlCurso->buscarPorId($idCurso);

if ($curso === null) {
    header('Location: ../vistas/principal.php');
    exit;
}

//solo el creador y los inscriptos confirmados
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

$volverAPregunta = '../vistas/pregunta.php?id=' . $idPregunta . '&curso=' . $idCurso;
$esAutorPregunta = $pregunta->getAutor()->getId_usuario() == $_SESSION['id_usuario'];

//destacar (solo el creador)
if ($accion === 'destacar') {
    if (!$esCreador) {
        header('Location: ' . $volverAPregunta);
        exit;
    }
    $controlPregunta->destacar($idPregunta);
    $_SESSION['aviso_pregunta'] = 'Pregunta destacada: ahora aparece primero en el foro.';
    header('Location: ' . $volverAPregunta);
    exit;
}

//eliminar (solo el creador del curso)
if ($accion === 'eliminar') {
    if (!$esCreador) {
        header('Location: ' . $volverAPregunta);
        exit;
    }
    $controlPregunta->eliminar($idPregunta);
    
    //aviso al que hizo la pregunta (va sin id de pregunta porque ya no existe)
    $controlNotificacion = new ControlNotificacion($conexion);
    $mensaje = 'El docente eliminó tu pregunta "' . $controlNotificacion->resumir($pregunta->getTexto()) . '" del curso ' . $curso->getTitulo();
    $controlNotificacion->enviar([$pregunta->getAutor()], $idCurso, null, 'preguntaEliminada', $mensaje, $_SESSION['id_usuario']);

    $_SESSION['aviso_foro'] = 'Pregunta eliminada.';
    header('Location: ../vistas/foro.php?id=' . $idCurso);   //la pregunta ya no existe, vuelvo al foro
    exit;
}

//aceptar: lo puede hacer el docente o quien hizo la pregunta
if ($accion === 'aceptar') {
    //le cargo las respuestas para buscar la que eligieron
    $controlRespuesta = new ControlRespuesta($conexion);
    foreach ($controlRespuesta->obtenerDePregunta($idPregunta) as $respuesta) {
        $pregunta->agregarRespuesta($respuesta);
    }

    $elegida = null;
    foreach ($pregunta->getRespuestas() as $respuesta) {
        if ($respuesta->getId_respuesta() == $idRespuesta) {
            $elegida = $respuesta;
        }
    }

    $errores = [];
    if (!$esCreador && !$esAutorPregunta) {
        $errores[] = 'Solo el docente o quien hizo la pregunta puede aceptar una respuesta.';
    } elseif (!$curso->estaActivo()) {
        $errores[] = 'El foro está cerrado porque el curso no está en curso.';   //RN05
    } elseif ($pregunta->estaCerrada()) {
        $errores[] = 'La pregunta ya está cerrada.';
    } elseif ($elegida === null) {
        $errores[] = 'Esa respuesta no es de esta pregunta.';
    }

    if (count($errores) > 0) {
        $_SESSION['errores_pregunta'] = $errores;
        header('Location: ' . $volverAPregunta);
        exit;
    }

    $controlPregunta->aceptarRespuesta($idPregunta, $elegida->getId_respuesta());
    
    //aviso a los participantes, y al de la respuesta aceptada con un mensaje especial(?)
    $controlNotificacion = new ControlNotificacion($conexion);
    $resumen = $controlNotificacion->resumir($pregunta->getTexto());
    $autorAceptada = $elegida->getAutor();

    $otros = [];
    foreach ($pregunta->participantes() as $participante) {
        if ($participante->getId_usuario() != $autorAceptada->getId_usuario()) {
            $otros[] = $participante;
        }
    }
    $mensaje = 'Se cerró la pregunta "' . $resumen . '" en ' . $curso->getTitulo() . ': ya tiene una respuesta aceptada';
    $controlNotificacion->enviar($otros, $idCurso, $idPregunta, 'preguntaCerrada', $mensaje, $_SESSION['id_usuario']);

    $mensaje = '¡Aceptaron tu respuesta a la pregunta "' . $resumen . '"! (' . $curso->getTitulo() . ')';
    $controlNotificacion->enviar([$autorAceptada], $idCurso, $idPregunta, 'preguntaCerrada', $mensaje, $_SESSION['id_usuario']);
    
    $_SESSION['aviso_pregunta'] = 'Respuesta aceptada. La pregunta quedó cerrada.';
}

header('Location: ' . $volverAPregunta);
exit;