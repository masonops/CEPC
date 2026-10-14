<?php
/**
 * CEPC · Sitio público
 * includes/secciones/inicio/articulos.php
 *
 * Propuestas y artículos publicados desde el panel (tabla `articulos`).
 * La publicación destacada (o la más nueva) va grande; al lado, las
 * siguientes. Si todavía no hay ninguna, se muestra un aviso.
 */

declare(strict_types=1);

$articulos  = datos_articulos(5, null, true);
$principal  = $articulos[0] ?? null;
$siguientes = array_slice($articulos, 1);
?>
<section class="seccion" id="articulos" aria-labelledby="articulos-titulo">
  <div class="contenedor">
    <div class="seccion__cabecera seccion__cabecera--fila">
      <h2 id="articulos-titulo" class="seccion__titulo revelar"><?= es_lista() ? 'Propuestas y artículos' : 'Publicaciones' ?></h2>
      <?php if (count($articulos) > 1): ?>
        <a class="enlace-flecha" href="<?= esc(pagina('articulos')) ?>">Ver todas <?= icono('arrow-right') ?></a>
      <?php endif; ?>
    </div>

    <?php if ($principal === null): ?>
      <div class="vacio revelar">
        <?= icono('megaphone') ?>
        <p><strong><?= es_lista() ? 'Pronto publicaremos nuestras propuestas.' : 'Aún no hay publicaciones.' ?></strong> Vuelve en unos días.</p>
      </div>
    <?php else: ?>
      <div class="articulos">
        <a class="articulo-principal revelar" href="<?= esc($principal['url']) ?>">
          <?php if (!empty($principal['imagen'])): ?>
            <img class="articulo-principal__imagen" src="<?= esc($principal['imagen']) ?>" alt="" loading="lazy" decoding="async">
          <?php endif; ?>
          <span class="etiqueta-articulo etiqueta-articulo--<?= esc($principal['categoria']) ?>"><?= esc($principal['categoria_texto']) ?></span>
          <h3><?= esc($principal['titulo']) ?></h3>
          <?php if (!empty($principal['resumen'])): ?><p><?= esc($principal['resumen']) ?></p><?php endif; ?>
          <span class="articulo-principal__pie">
            <time datetime="<?= esc($principal['fecha']) ?>"><?= esc(fecha_cercania($principal['fecha']) ?? ucfirst(fecha_larga($principal['fecha']))) ?></time>
            <span class="enlace-flecha">Leer <?= icono('arrow-right') ?></span>
          </span>
        </a>

        <?php if ($siguientes !== []): ?>
          <ol class="articulos__lista">
            <?php foreach ($siguientes as $i => $a): ?>
              <li class="revelar" style="--i: <?= (int) $i ?>">
                <a class="articulo-fila" href="<?= esc($a['url']) ?>">
                  <span class="etiqueta-articulo etiqueta-articulo--<?= esc($a['categoria']) ?>"><?= esc($a['categoria_texto']) ?></span>
                  <strong><?= esc($a['titulo']) ?></strong>
                  <time datetime="<?= esc($a['fecha']) ?>"><?= esc(fecha_cercania($a['fecha']) ?? ucfirst(fecha_larga($a['fecha']))) ?></time>
                </a>
              </li>
            <?php endforeach; ?>
          </ol>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
