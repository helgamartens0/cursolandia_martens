<?php
   
        require_once __DIR__ . '/../conexiones/conexion.php';
        require_once __DIR__ . '/../controles/ControlCurso.php';
        require_once __DIR__ . '/../includes/verificar_sesion.php';
        
        
        $controlCurso = new ControlCurso($conexion);
        $cursos = $controlCurso->obtenerTodos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>P&aacutegina Principal - Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="../css/styles.css" rel="stylesheet">
</head>
<body>
    <?php  
    
        require __DIR__ . '/../includes/navbar.php';
    ?>
    <div class="container mt-4">
            <h3>Todos los cursos</h3>
            <div class="row g-3">
                <?php foreach($cursos as $curso):?>
                <div class="col-sm-6">
                    <div class="card">
                        <div class="card-body curso">
                            <h5 class="card-title"><?php echo htmlspecialchars($curso->getTitulo())  ?> </h5>
                            <?= $curso->esGratuito() ? 'Gratis' : 'Pago' ?>
                            <br><?= "fecha de inicio: ". htmlspecialchars($curso->getFechaInicio()) ?>
                            <br><?= "fecha de fin: " . htmlspecialchars($curso->getFechaFin()) ?>
                            <p class="card-text"><?php echo htmlspecialchars($curso->getDescripcion())?></p>
                            <a href="curso.php?id=<?=$curso->getId()?>" class="btn link-curso">Ir al curso</a>
                        </div>
                    </div>
                </div>
                <?php endforeach?>
                
            </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>