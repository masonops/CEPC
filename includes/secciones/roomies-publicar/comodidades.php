<?php
/**
 * CEPC · Sitio público
 * includes/secciones/roomies-publicar/comodidades.php — Lo que tiene la
 * pieza, el depto y el edificio.
 */

declare(strict_types=1);
?>
<fieldset class="parte" id="parte-comodidades" data-parte="comodidades">
  <legend><span class="parte__numero">5</span>Comodidades</legend>
  <?php foreach (ROOMIES_GRUPOS_COMODIDAD as $grupo => $nombreGrupo): ?>
    <div class="campo">
      <span><?= esc($nombreGrupo) ?></span>
      <div class="casillas">
        <?php foreach (ROOMIES_COMODIDADES as $clave => $c): if ($c['grupo'] !== $grupo) continue; ?>
          <label><input type="checkbox" name="comodidades[]" value="<?= esc($clave) ?>"<?= $marcado('comodidades', $clave) ?>><span><?= icono($c['icono']) ?><?= esc($c['texto']) ?></span></label>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
</fieldset>
