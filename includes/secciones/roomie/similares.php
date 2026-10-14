<?php
/**
 * CEPC · Sitio público
 * includes/secciones/roomie/similares.php — Avisos parecidos (precio y zona).
 */

declare(strict_types=1);

$similares = roomies_similares($roomie);
?>
<?php if ($similares !== []): ?>
<section class="seccion ficha-similares" aria-labelledby="similares-titulo">
  <div class="contenedor">
    <div class="seccion__cabecera seccion__cabecera--fila">
      <h2 id="similares-titulo" class="ficha-similares__titulo">También te pueden servir</h2>
      <a class="enlace-flecha" href="<?= esc(pagina('roomies')) ?>">Ver todos <?= icono('arrow-right') ?></a>
    </div>
    <div class="ficha-similares__lista">
      <?php foreach ($similares as $s): ?><?= roomie_tarjeta($s) ?><?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
