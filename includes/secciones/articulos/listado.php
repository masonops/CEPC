<?php
/**
 * CEPC · Sitio público
 * includes/secciones/articulos/listado.php
 *
 * Filtro por categoría (assets/js/articulos.js; sin JavaScript se ven todas)
 * y la lista de publicaciones como tarjetas.
 */

declare(strict_types=1);

$articulos = datos_articulos(100);
$presentes = array_unique(array_column($articulos, 'categoria'));
$plurales  = ['propuesta' => 'Propuestas', 'fechas' => 'Fechas importantes', 'noticia' => 'Noticias'];
?>
<section class="seccion seccion--pegada" aria-label="Publicaciones">
  <div class="contenedor">
    <?php if ($articulos === []): ?>
      <div class="vacio">
        <?= icono('megaphone') ?>
        <p><strong><?= es_lista() ? 'Pronto publicaremos nuestras propuestas.' : 'Aún no hay publicaciones.' ?></strong> Vuelve en unos días.</p>
      </div>
    <?php else: ?>
      <?php if (count($presentes) > 1): ?>
        <div class="segmentos articulos-filtro" role="group" aria-label="Mostrar" data-articulos-filtro hidden>
          <button type="button" aria-pressed="true" data-categoria="">Todas</button>
          <?php foreach (ARTICULO_CATEGORIAS as $clave => $nombre): if (!in_array($clave, $presentes, true)) continue; ?>
            <button type="button" aria-pressed="false" data-categoria="<?= esc($clave) ?>"><?= esc($plurales[$clave] ?? $nombre) ?></button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <ul class="tarjetas-articulo">
        <?php foreach ($articulos as $a): ?>
          <li data-categoria="<?= esc($a['categoria']) ?>">
            <a class="tarjeta-articulo<?= $a['destacado'] ? ' tarjeta-articulo--destacada' : '' ?>" href="<?= esc($a['url']) ?>">
              <span class="etiqueta-articulo etiqueta-articulo--<?= esc($a['categoria']) ?>"><?= esc($a['categoria_texto']) ?></span>
              <h2><?= esc($a['titulo']) ?></h2>
              <?php if (!empty($a['resumen'])): ?><p><?= esc($a['resumen']) ?></p><?php endif; ?>
              <time datetime="<?= esc($a['fecha']) ?>"><?= esc(ucfirst(fecha_larga($a['fecha']))) ?></time>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</section>
