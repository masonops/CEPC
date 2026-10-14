<?php
/**
 * CEPC · Sitio público
 * includes/secciones/articulo/mas.php — Otras publicaciones, al final.
 */

declare(strict_types=1);

$otros = array_values(array_filter(
    datos_articulos(4),
    static fn(array $a): bool => $a['id'] !== ($articulo['id'] ?? 0)
));
$otros = array_slice($otros, 0, 3);
?>
<?php if ($otros !== []): ?>
<section class="seccion articulo-mas" aria-labelledby="mas-titulo">
  <div class="contenedor">
    <h2 id="mas-titulo" class="articulo-mas__titulo">Más publicaciones</h2>
    <ul class="tarjetas-articulo">
      <?php foreach ($otros as $a): ?>
        <li>
          <a class="tarjeta-articulo" href="<?= esc($a['url']) ?>">
            <span class="etiqueta-articulo etiqueta-articulo--<?= esc($a['categoria']) ?>"><?= esc($a['categoria_texto']) ?></span>
            <h3><?= esc($a['titulo']) ?></h3>
            <time datetime="<?= esc($a['fecha']) ?>"><?= esc(ucfirst(fecha_larga($a['fecha']))) ?></time>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>
