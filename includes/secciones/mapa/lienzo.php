<?php
/**
 * CEPC · Sitio público
 * includes/secciones/mapa/lienzo.php
 *
 * El dibujo del mapa y sus botones de zoom. Se puede arrastrar, hacer zoom
 * con la rueda o pellizcando, y tocar cada edificio.
 */

declare(strict_types=1);
?>
<div class="lienzo" data-lienzo>
  <?= mapa_svg(['interactivo' => true, 'lugares' => true, 'coordenadas' => $verCoordenadas]) ?>

  <div class="lienzo__controles" role="group" aria-label="Zoom del mapa">
    <button type="button" data-zoom="1" aria-label="Acercar"><?= icono('plus') ?></button>
    <button type="button" data-zoom="-1" aria-label="Alejar"><?= icono('minus') ?></button>
    <button type="button" data-zoom="0" aria-label="Ver todo el campus"><?= icono('arrows-in-simple') ?></button>
  </div>

  <p class="lienzo__brujula" aria-hidden="true">N ↑</p>
  <?php if ($verCoordenadas): ?>
    <p class="lienzo__coordenadas" data-coordenadas>x –, y –</p>
  <?php endif; ?>
</div>
