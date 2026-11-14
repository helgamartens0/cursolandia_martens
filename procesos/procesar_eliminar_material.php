<?php
require_once __DIR__ . '/../includes/verificar_sesion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vistas/principal.php');
    exit;
}

require_once __DIR__ . '/../conexiones/conexion.php';
require_once __DIR__ . '/../controles/ControlCurso.php';
require_once __DIR__ . '/../controles/ControlArchivo.php';

$idArchivo = (int) ($_POST['id_archivo'] ?? 0);
$idCurso = (int) ($_POST['id_curso'] ?? 0);

$controlCurso = new ControlCurso($conexion);
$curso = $controlCurso->buscarPorId($idCurso);

//control solo el creador borra material de su curso
if ($curso === null || $curso->getIdCreador() != $_SESSION['id_usuario']) {
    header('Location: ../vistas/principal.php');
    exit;
}

$controlArchivo = new ControlArchivo($conexion);
$archivo = $controlArchivo->buscarDeCurso($idArchivo, $idCurso);

//$archivo es el resultado de buscar el archivo del curso, si devuelve null es xq o no existe o es de otro curso
//entonces nunca entraria a este if
if ($archivo !== null) {
    //primero lo saco de la carpeta y despues de la base
    /*
    en la base guardamos la ruta relativa (uploads/material/...). 
    para borrarlo del disco necesito la ruta completa, 
    así que le pego adelante la carpeta del proyecto, igual que en ver_comprobante.php
    */
    $ruta = __DIR__ . '/../' . $archivo->getUrl();
    if (is_file($ruta)) { //esto es x si el archivo ya no estaba (x ej borrarlo a mano)
        unlink($ruta);//funcion php q borra un archivo del disco 
        //hay q hacer esto xq si solo borrara la fila de la base el archivo seguiria en la carpeta
        //ocupando espacio
    }
    $controlArchivo->eliminar($idArchivo, $idCurso); //recien ahora elimino la fila
    $_SESSION['aviso_material'] = 'Archivo eliminado.';
}

header('Location: ../vistas/subir_material.php?id=' . $idCurso);
exit;