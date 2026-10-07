<?php
$precio = $curso->esGratuito() ? 'Gratis' : '$ ' . number_format($curso->getCosto(), 0, ',', '.');
$hasta  = date('d/m/Y', strtotime($curso->getFechaFin()));
?>
<div class="col-sm-6 col-lg-4">
    <div class="card h-100 <?= $curso->esDestacado() ? 'card-pro' : 'curso' ?>">
        <div class="card-body d-flex flex-column gap-2">
            <h5 class="card-title mb-0">
                <?= htmlspecialchars($curso->getTitulo()) ?>
                <?php if ($curso->esDestacado()): ?>
                    <span class="badge ms-1">Pro</span>
                <?php endif; ?>
            </h5>
            <p class="small text-secondary mb-0">Por <?= htmlspecialchars($curso->getCreador()->getNombre() . ' ' . $curso->getCreador()->getApellido()) ?></p>
            <p class="card-text mb-0"><?= htmlspecialchars($curso->getDescripcion()) ?></p>
            <p class="small text-secondary mb-2"><?= $precio ?> · Hasta el <?= $hasta ?></p>
            <a href="curso.php?id=<?= $curso->getId() ?>" class="btn link-curso mt-auto align-self-start">Ir al curso</a>
        </div>
    </div>
</div>