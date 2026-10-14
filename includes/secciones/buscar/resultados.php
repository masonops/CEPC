<?php
/**
 * CEPC · Sitio público
 * includes/secciones/buscar/resultados.php
 *
 * Campo de búsqueda y lista de resultados. Los resultados del servidor
 * ($resultados, de buscar.php) se agrupan igual que en la ventana del
 * buscador; buscador.js los reemplaza mientras se escribe.
 */

declare(strict_types=1);

$agrupados = [];
foreach ($resultados as $r) {
    $agrupados[$r['grupo']][] = $r;
}
?>
<section class="seccion busqueda" data-busqueda-pagina>
  <div class="contenedor contenedor--angosto">
    <form class="busqueda__campo" action="<?= esc(pagina('buscar')) ?>" method="get" role="search">
      <?= icono('magnifying-glass') ?>
      <input type="search" name="q" value="<?= esc($consulta) ?>" placeholder="Ramos, salas, solemnes, entregas…"
             aria-label="Buscar" autocomplete="off" spellcheck="false">
      <button class="boton boton--primario" type="submit">Buscar</button>
    </form>

    <div class="busqueda__resultados" data-busqueda-resultados data-servidor="<?= sitio('estatico', false) ? '0' : '1' ?>">
      <?php if ($consulta !== '' && $resultados === []): ?>
        <p class="resultado-vacio">No encontramos «<?= esc($consulta) ?>». Prueba con el código del ramo (MA1001), el nombre de la sala (B12) o una palabra como «solemne».</p>
      <?php endif; ?>
      <?php foreach (BUSQUEDA_GRUPOS as $clave => $grupo): ?>
        <?php if (empty($agrupados[$clave])) continue; ?>
        <div class="resultado-grupo">
          <p class="resultado-grupo__titulo"><?= esc($grupo['titulo']) ?></p>
          <?php foreach ($agrupados[$clave] as $item): ?>
            <a class="resultado" href="<?= esc($item['url']) ?>"<?= !empty($item['externo']) ? ' target="_blank" rel="noopener"' : '' ?>>
              <span class="resultado__icono"><?= icono($grupo['icono']) ?></span>
              <span class="resultado__texto"><strong><?= esc($item['titulo']) ?></strong><span><?= esc($item['detalle']) ?></span></span>
              <?php if (!empty($item['externo'])): ?><span class="resultado__externo">U-Campus ↗</span><?php endif; ?>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
