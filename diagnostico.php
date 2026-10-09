<?php
// ARCHIVO TEMPORAL DE DIAGNÓSTICO: borralo cuando terminemos (no lo subas a GitHub)
session_start();
require __DIR__ . '/conexiones/conexion.php';

echo '<pre style="font-size:15px">';

echo "== Sesión ==\n";
print_r($_SESSION);

$r = mysqli_query($conexion, "SELECT CURDATE() AS hoy_mysql, DATABASE() AS base");
echo "\n== Fecha y base ==\n";
print_r(mysqli_fetch_assoc($r));
echo "Fecha de PHP: " . date('Y-m-d') . "\n";

echo "\n== Inscripciones ==\n";
try {
    $r = mysqli_query($conexion, "SELECT i.id_usuario, i.id_curso, i.estado, c.titulo, c.fecha_inicio, c.fecha_fin
                                  FROM inscripcion i JOIN curso c ON c.id_curso = i.id_curso");
    print_r(mysqli_fetch_all($r, MYSQLI_ASSOC));
} catch (mysqli_sql_exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n== Cursos ==\n";
$r = mysqli_query($conexion, "SELECT id_curso, titulo, fecha_inicio, fecha_fin, id_creador FROM curso");
print_r(mysqli_fetch_all($r, MYSQLI_ASSOC));
echo '</pre>';
