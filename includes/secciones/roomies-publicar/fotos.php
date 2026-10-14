<?php
/**
 * CEPC · Sitio público
 * includes/secciones/roomies-publicar/fotos.php — Hasta 8 fotos. El
 * navegador las achica antes de subirlas (assets/js/roomies-publicar.js) y
 * el servidor las vuelve a procesar sin metadatos.
 */

declare(strict_types=1);

$maxFotos = (int) (sitio('roomies')['max_fotos'] ?? 8);
?>
<fieldset class="parte" id="parte-fotos" data-parte="fotos">
  <legend><span class="parte__numero">7</span>Fotos</legend>
  <p class="parte__ayuda">Entre 2 y <?= $maxFotos ?>. La primera es la portada: que sea la pieza, con luz natural. Suma el baño, la cocina y el living.</p>
  <div class="fotos-subir<?= $conError('fotos') ?>" data-fotos data-max="<?= $maxFotos ?>">
    <ul class="fotos-subir__lista" data-fotos-lista></ul>
    <label class="fotos-subir__boton">
      <input type="file" name="fotos[]" accept="image/jpeg,image/png,image/webp" multiple data-fotos-input>
      <?= icono('upload-simple') ?>
      <strong>Agregar fotos</strong>
      <span>JPG, PNG o WebP</span>
    </label>
  </div>
  <?php if (isset($errores['fotos'])): ?><p class="campo campo--error"><small><?= esc($errores['fotos']) ?></small></p><?php endif; ?>
  <?php if ($errores !== []): ?><p class="campo__ayuda">Por seguridad, las fotos hay que elegirlas otra vez.</p><?php endif; ?>
</fieldset>
