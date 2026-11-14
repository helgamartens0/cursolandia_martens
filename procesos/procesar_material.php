<?php
require_once __DIR__ . '/../includes/verificar_sesion.php';

//si no viene del formulario, afuera
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vistas/principal.php');
    exit;
}

require_once __DIR__ . '/../conexiones/conexion.php';
require_once __DIR__ . '/../controles/ControlCurso.php';
require_once __DIR__ . '/../controles/ControlArchivo.php';

$idCurso = (int) ($_POST['id_curso'] ?? 0);

$controlCurso = new ControlCurso($conexion);
$curso = $controlCurso->buscarPorId($idCurso);

// otro control solo el creador sube material a su curso
if ($curso === null || $curso->getIdCreador() != $_SESSION['id_usuario']) {
    header('Location: ../vistas/principal.php');
    exit;
}

//le cargo al curso lo que ya tiene, para poder contar y sumar
$controlArchivo = new ControlArchivo($conexion);
foreach ($controlArchivo->obtenerDeCurso($curso->getId()) as $archivo) {
    $curso->agregarArchivo($archivo);
}

$errores = [];
$archivo = $_FILES['archivo'] ?? null;
$extensionesPermitidas = ['pdf', 'jpg', 'jpeg', 'png', 'docx', 'pptx', 'zip'];
$tiposPermitidos = [
    'application/pdf',
    'image/jpeg',
    'image/png',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'application/zip'
];
$extension = '';

//controlar q haya llegado bien y sea de un tipo permitido (igual que con el comprobante)
if ($archivo === null || $archivo['error'] !== UPLOAD_ERR_OK) {
    $errores[] = 'No llegó ningún archivo, o es demasiado grande.';
} else {
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $tipoReal = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);

    if (!in_array($extension, $extensionesPermitidas) || !in_array($tipoReal, $tiposPermitidos)) {
        $errores[] = 'El archivo tiene que ser PDF, JPG, PNG, Word, PowerPoint o ZIP.';
    }
}

//2)si el creador es gratuito, reviso los limites
if (count($errores) === 0 && !$curso->getCreador()->esPro()) {
    $tamanioMB = round($archivo['size'] / 1024 / 1024, 2);
    $nuevo = new Archivo(0, $archivo['name'], $tamanioMB, date('Y-m-d'), '');

    if (count($curso->getArchivos()) >= Curso::MAX_ARCHIVOS) {
        $errores[] = 'Ya subiste ' . Curso::MAX_ARCHIVOS . ' archivos, que es el máximo del plan gratuito.';
    } elseif ($nuevo->superaLimite()) {
        $errores[] = 'Cada archivo puede pesar hasta ' . Archivo::MAX_MB . ' MB.';
    } elseif ($curso->tamanioTotalArchivos() + $tamanioMB > Curso::MAX_MB_TOTAL) {
        $errores[] = 'Con este archivo te pasás de los ' . Curso::MAX_MB_TOTAL . ' MB del curso.';
    }
}

if (count($errores) > 0) {
    $_SESSION['errores_material'] = $errores;
    header('Location: ../vistas/subir_material.php?id=' . $idCurso);
    exit;
}

//3) lo guardo con un nombre nuevo: idcurso_hora.extension (ej: 3_1794500000.pdf)
$carpeta = __DIR__ . '/../uploads/material';
if (!is_dir($carpeta)) {
    mkdir($carpeta, 0755, true);
}
$nombreArchivo = $idCurso . '_' . time() . '.' . $extension;

if (!move_uploaded_file($archivo['tmp_name'], $carpeta . '/' . $nombreArchivo)) {
    $_SESSION['errores_material'] = ['No se pudo guardar el archivo. Probá de nuevo.'];
    header('Location: ../vistas/subir_material.php?id=' . $idCurso);
    exit;
}

//4) y lo anoto en la base con su nombre original
$tamanioMB = round($archivo['size'] / 1024 / 1024, 2);
$controlArchivo->crear($idCurso, $archivo['name'], $tamanioMB, 'uploads/material/' . $nombreArchivo);

$_SESSION['aviso_material'] = 'Archivo subido.';
header('Location: ../vistas/subir_material.php?id=' . $idCurso);
exit;