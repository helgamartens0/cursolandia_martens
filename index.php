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
                <div class="col-sm-6">
                    <div class="card card-pro">
                        <div class="card-body">
                            <h5 class="card-title">Titulo del curso</h5>
                            <span class="badge">Pro</span>
                            <p class="card-text">Pequeña descripcion del curso.</p>
                            <a href="#" class="btn btn-primary link-curso">Ir al curso</a>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                        <div class="card card-pro">
                            <div class="card-body">
                                <h5 class="card-title">Titulo del curso</h5>
                                <span class="badge">Pro</span>
                                <p class="card-text">Pequeña descripcion del curso</p>
                                <a href="#" class="btn btn-primary link-curso">Ir al curso</a>
                            </div>
                        </div>
                </div>
            </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>