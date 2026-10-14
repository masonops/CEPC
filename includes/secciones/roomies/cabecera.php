<?php
/**
 * CEPC · Sitio público
 * includes/secciones/roomies/cabecera.php — Título de Roomies, resumen en
 * cifras y botón para publicar.
 */

declare(strict_types=1);

$precios  = array_column($roomies, 'total');
$cercanos = count(array_filter($roomies, static fn(array $r): bool => $r['cercania']['a_pie'] <= 20));
$activo   = sitio('roomies')['activo'] ?? true;
?>
<header class="roomies-cabecera">
  <div class="contenedor roomies-cabecera__interior">
    <div>
      <?php if (es_lista()): ?>
        <p class="roomies-cabecera__propuesta"><span translate="no"><?= esc(sitio('lista')['nombre']) ?></span> Propuesta para estudiantes foráneos</p>
      <?php endif; ?>
      <h1 class="roomies-cabecera__titulo">Roomies</h1>
      <p class="roomies-cabecera__bajada">Piezas y deptos para compartir cerca de Beauchef, publicados por estudiantes. Cada aviso está verificado con un correo de la Universidad.</p>
    </div>
    <div class="roomies-cabecera__acciones">
      <?php if ($activo): ?>
        <a class="boton boton--primario" href="<?= esc(pagina('roomies-publicar')) ?>"><?= icono('plus') ?>Publicar mi pieza</a>
      <?php endif; ?>
      <a class="boton boton--claro" href="#como-funciona">Cómo funciona</a>
    </div>
  </div>

  <?php if ($activo && $roomies !== []): ?>
    <div class="contenedor">
      <dl class="roomies-cifras">
        <div><dt>Avisos</dt><dd><?= count($roomies) ?></dd></div>
        <div><dt>Desde</dt><dd><?= esc(roomie_pesos(min($precios))) ?></dd></div>
        <div><dt>A 20 min o menos a pie</dt><dd><?= $cercanos ?></dd></div>
      </dl>
    </div>
  <?php elseif (!$activo): ?>
    <div class="contenedor">
      <p class="aviso-ejemplo roomies-pronto"><?= icono('hourglass') ?>Roomies abre muy pronto. Si eres foráneo/a y buscas dónde vivir, vuelve en unos días.</p>
    </div>
  <?php endif; ?>
</header>
