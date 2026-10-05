<?php
    $cursos= [
        [
        'titulo'      => 'Introducción a PHP',
        'descripcion' => 'Aprendé lo básico de PHP desde cero.',
        'costo'       => null,
        'cupo'        => 20,
        'fechaInicio' => '2026-10-15',
        'fechaFin'    => '2026-12-15',
        'etiquetas'   => ['php', 'programacion'],
        'es_pro'      => true
        ],
        [
        'titulo'      => 'Introducción al Diseño Grafico',
        'descripcion' => 'Aprendé a diseñar paginas web desde cero.',
        'costo'       => 1000,
        'cupo'        => 50,
        'fechaInicio' => '2026-11-10',
        'fechaFin'    => '2026-12-20',
        'etiquetas'   => ['diseño', 'figma'],
        'es_pro'      => false
        ],
        [
        'titulo'      => 'Arquitectura de computadoras',
        'descripcion' => 'Aprendé arquitectura de computadoras como un pro.',
        'costo'       => 30000,
        'cupo'        => 10,
        'fechaInicio' => '2027-02-10',
        'fechaFin'    => '2027-12-12',
        'etiquetas'   => ['arquitectura', 'pc'],
        'es_pro'      => true
        ],
        [
        'titulo'      => 'JAVA para principiantes',
        'descripcion' => 'Aprendé a programar en java',
        'costo'       => null,
        'cupo'        => 200,
        'fechaInicio' => '2026-10-15',
        'fechaFin'    => '2026-12-15',
        'etiquetas'   => ['java', 'programacion'],
        'es_pro'      => false
        ],
    ];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="css/styles.css" rel="stylesheet">
</head>
<body>
    <?php require 'includes/navbar.php'; ?>
    <div class="container mt-4">
            <h3>Cursos PRO - Destacados</h3>
            <div class="row g-3">
                <?php foreach($cursos as $curso):?>
                    <?php if($curso['es_pro']): ?>
                <div class="col-sm-6">
                    <div class="card card-pro">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $curso['titulo'] ?><span class="badge"> Pro</span></h5>
                            <p class="card-text"><?php echo $curso['descripcion']?></p>
                            <a href="#" class="btn link-curso">Ir al curso</a>
                        </div>
                    </div>
                </div>
                    <?php endif ?>
                <?php endforeach?>
                
            </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>