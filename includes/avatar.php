<?php
//muestra la foto de la persona, o el circulito con sus iniciales si no subio ninguna
//$clase: 'avatar' es el chiquito (listas, foro) y 'perfil-avatar' el grande del perfil
function mostrarAvatar(Usuario $persona, string $clase = 'avatar'): void
{
    if ($persona->tieneFoto()): ?>
        <img src="../<?= htmlspecialchars($persona->getImagen_principal()) ?>" alt="Foto de <?= htmlspecialchars($persona->getNombre()) ?>" class="<?= $clase ?>">
    <?php else: ?>
        <div class="<?= $clase ?>"><?= htmlspecialchars($persona->getIniciales()) ?></div>
    <?php endif;
}