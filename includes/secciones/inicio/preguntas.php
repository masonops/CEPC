<?php
/**
 * CEPC · Sitio público
 * includes/secciones/inicio/preguntas.php
 *
 * Preguntas frecuentes. Se editan en includes/contenido.php →
 * contenido_preguntas().
 */

declare(strict_types=1);

$preguntas = contenido_preguntas();
?>
<section class="seccion" id="preguntas" aria-labelledby="preguntas-titulo">
  <div class="contenedor contenedor--angosto">
    <div class="seccion__cabecera seccion__cabecera--centro">
      <h2 id="preguntas-titulo" class="seccion__titulo revelar">Preguntas frecuentes</h2>
    </div>
    <div class="preguntas">
      <?php foreach ($preguntas as $pregunta): ?>
        <details class="pregunta revelar">
          <summary>
            <span><?= esc($pregunta['pregunta']) ?></span>
            <?= icono('caret-down', 'pregunta__flecha') ?>
          </summary>
          <div class="pregunta__respuesta"><p><?= $pregunta['respuesta'] ?></p></div>
        </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>
