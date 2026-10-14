<?php
/**
 * CEPC · Sitio público
 * includes/secciones/transparencia/documentos.php
 *
 * Estatutos, reglamentos y archivos oficiales. La lista y sus enlaces se
 * editan en includes/contenido.php → contenido_documentos().
 */

declare(strict_types=1);

$documentos = contenido_documentos();
?>
<section class="seccion" id="documentos" aria-labelledby="documentos-titulo">
  <div class="contenedor">
    <h2 id="documentos-titulo" class="seccion__titulo">Documentos</h2>
    <p class="seccion__bajada">Las reglas con las que funciona el CEPC.</p>

    <ul class="documentos">
      <?php foreach ($documentos as $d): $hay = trim((string) $d['url']) !== ''; ?>
        <li>
          <?php if ($hay): ?><a class="documento" href="<?= esc($d['url']) ?>" rel="noopener" target="_blank"><?php else: ?><div class="documento documento--pronto"><?php endif; ?>
            <span class="documento__icono"><?= icono('file-text') ?></span>
            <span class="documento__texto">
              <strong><?= esc($d['titulo']) ?></strong>
              <span><?= esc($d['descripcion']) ?></span>
            </span>
            <?php if ($hay): ?>
              <?= icono('arrow-up-right', 'documento__flecha') ?>
            <?php else: ?>
              <span class="documento__pronto">Pronto</span>
            <?php endif; ?>
          <?php if ($hay): ?></a><?php else: ?></div><?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
