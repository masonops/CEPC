<?php
/**
 * CEPC · Sitio público
 * includes/secciones/inicio/somos.php
 *
 * Quiénes somos, con la foto de Beauchef 851 y la directiva.
 */

declare(strict_types=1);

$directiva = datos_directiva();
?>
<section class="seccion" id="directiva" aria-labelledby="directiva-titulo">
  <div class="contenedor somos">
    <figure class="somos__foto revelar">
      <img src="<?= esc(recurso('assets/img/beauchef-851.jpg')) ?>" alt="Entrada del edificio de Beauchef 851, con su fachada de vidrio y árboles en primer plano." width="880" height="1100" loading="lazy" decoding="async">
    </figure>

    <div class="somos__texto">
      <h2 id="directiva-titulo" class="oculto">Quiénes somos</h2>
      <?php if (es_lista()): ?>
        <p class="declaracion revelar">
          <strong><?= esc(sitio('lista')['lema']) ?>.</strong>
          Somos <span translate="no"><?= esc(sitio('lista')['nombre']) ?></span>, una lista de estudiantes de primer y segundo año que se presenta al Centro de Estudiantes de Plan Común. Queremos un CEPC cercano, ordenado y transparente.
        </p>
      <?php else: ?>
        <p class="declaracion revelar">
          <strong>Somos el Centro de Estudiantes de Plan Común.</strong>
          Representamos a primer y segundo año ante la Escuela y la Facultad, organizamos actividades y te acompañamos cuando algo no anda bien.
        </p>
      <?php endif; ?>

      <?php if ($directiva !== []): ?>
        <h3 class="somos__subtitulo revelar"><?= es_lista() ? 'Nuestras candidaturas' : 'Directiva ' . esc(date('Y')) ?></h3>
        <ul class="personas">
          <?php foreach ($directiva as $i => $persona): ?>
            <li class="persona revelar" style="--i: <?= (int) $i ?>">
              <span class="avatar avatar--<?= esc($persona['color'] ?: 'azul') ?>">
                <?php if ($persona['foto']): ?>
                  <img src="<?= esc($persona['foto']) ?>" alt="" width="46" height="46" loading="lazy" decoding="async">
                <?php else: ?>
                  <?= esc(iniciales_de($persona['nombre'])) ?>
                <?php endif; ?>
              </span>
              <span class="persona__texto">
                <strong><?= esc($persona['nombre']) ?></strong>
                <span><?= esc($persona['cargo']) ?></span>
              </span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>
</section>
