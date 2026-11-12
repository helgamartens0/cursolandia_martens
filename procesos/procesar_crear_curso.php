<?php
require_once __DIR__ . '/../includes/verificar_sesion.php';
require_once __DIR__ . '/../conexiones/conexion.php';
require_once __DIR__ . '/../controles/ControlUsuario.php';
require_once __DIR__ . '/../controles/ControlCurso.php';
require_once __DIR__ . '/../controles/ControlEtiqueta.php';

//si no viene del formulario, vuelvo
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vistas/crear_curso.php');
    exit;
}

$controlUsuario = new ControlUsuario($conexion);
$controlCurso = new ControlCurso($conexion);
$controlEtiqueta = new ControlEtiqueta($conexion);

//me fijo si puede crear otro curso (gratuito max 3 activos)
$usuario = $controlUsuario->buscarPorId($_SESSION['id_usuario']);
$cantidad = $controlUsuario->contarCursosCreados($usuario->getId_usuario());

if (!$usuario->puedeCrearCurso($cantidad)) {
    $_SESSION['errores_curso'] = ['Llegaste al limite de cursos activos del plan gratuito.'];
    header('Location: ../vistas/crear_curso.php');
    exit;
}

//leo los datos
$titulo = trim($_POST['titulo'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$costo = trim($_POST['costo'] ?? '');
$cupo = trim($_POST['cupo'] ?? '');
$fechaInicio = $_POST['fecha_inicio'] ?? '';
$fechaFin = $_POST['fecha_fin'] ?? '';
$tipoAcceso = $_POST['tipo_acceso'] ?? '';
$etiquetas = trim($_POST['etiquetas'] ?? '');   //las etiquetas separadas por coma

//valido
$errores = [];

if (mb_strlen($titulo) < 3 || mb_strlen($titulo) > 100) {
    $errores[] = 'El titulo tiene que tener entre 3 y 100 caracteres.';
}

if ($descripcion === '') {
    $errores[] = 'Escribi una descripcion del curso.';
}

//el costo es opcional: si esta vacio el curso es gratis
if ($costo !== '' && (!is_numeric($costo) || $costo <= 0)) {
    $errores[] = 'El costo tiene que ser un numero mayor a 0.';
}

//el cupo es opcional: si esta vacio no tiene limite
if ($cupo !== '' && (!is_numeric($cupo) || $cupo <= 0)) {
    $errores[] = 'El cupo tiene que ser un numero mayor a 0.';
}

if ($fechaInicio === '' || $fechaFin === '') {
    $errores[] = 'Completa las fechas de inicio y fin.';
} else {
    if ($fechaInicio < date('Y-m-d')) {
        $errores[] = 'La fecha de inicio no puede ser antes de hoy.';
    }
    if ($fechaFin < $fechaInicio) {
        $errores[] = 'La fecha de fin no puede ser antes de la de inicio.';
    }
}

if ($tipoAcceso !== 'publico' && $tipoAcceso !== 'privado') {
    $errores[] = 'Elegi si el curso es publico o privado.';
}

//junto las etiquetas en una sola lista, sin repetir
$listaEtiquetas = [];


foreach (explode(',', $etiquetas) as $nombre) {   //explode corta el texto en cada coma
    $nombre = mb_strtolower(trim($nombre));
    if ($nombre !== '' && !in_array($nombre, $listaEtiquetas)) {
        $listaEtiquetas[] = $nombre;
    }
}

if (count($listaEtiquetas) > 10) {
    $errores[] = 'Podes poner hasta 10 etiquetas.';
}

//si hubo errores vuelvo al formulario con lo que habia escrito
if (count($errores) > 0) {
    $_SESSION['errores_curso'] = $errores;
    $_SESSION['datos_curso'] = [
        'titulo' => $titulo,
        'descripcion' => $descripcion,
        'costo' => $costo,
        'cupo' => $cupo,
        'fecha_inicio' => $fechaInicio,
        'fecha_fin' => $fechaFin,
        'tipo_acceso' => $tipoAcceso,
        'etiquetas' => $etiquetas
    ];
    header('Location: ../vistas/crear_curso.php');
    exit;
}

//si costo o cupo quedaron vacios van como null (gratis / sin limite)
if ($costo === '') {
    $costo = null;
} else {
    $costo = (float) $costo;
}

if ($cupo === '') {
    $cupo = null;
} else {
    $cupo = (int) $cupo;
}

//guardo el curso (crear me devuelve el id del curso nuevo)
$idCurso = $controlCurso->crear($titulo, $descripcion, $costo, $cupo, $fechaInicio, $fechaFin, $tipoAcceso, $usuario->getId_usuario());

//le agrego las etiquetas (si no existen se crean)
foreach ($listaEtiquetas as $nombre) {
    $etiqueta = $controlEtiqueta->buscarOcrear($nombre);
    $controlEtiqueta->agregarACurso($idCurso, $etiqueta);
}

$_SESSION['aviso_curso'] = 'Tu curso fue creado!';
header('Location: ../vistas/curso.php?id=' . $idCurso);
exit;