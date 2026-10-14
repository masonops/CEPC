<?php
/**
 * CEPC · Sitio público
 * includes/secciones/roomies-publicar/convivencia.php — Mascotas, fumar,
 * visitas y con quién buscan vivir.
 */

declare(strict_types=1);

$porDefecto = ['mascotas' => 'consultar', 'fumar' => 'no', 'visitas' => 'aviso', 'preferencia' => 'todos'];
?>
<fieldset class="parte" id="parte-convivencia" data-parte="convivencia">
  <legend><span class="parte__numero">6</span>Convivencia</legend>
  <div class="rejilla rejilla--2">
    <?php foreach (ROOMIES_REGLAS as $clave => $regla): ?>
      <div class="campo" role="radiogroup" aria-label="<?= esc($regla['titulo']) ?>">
        <span><?= esc($regla['titulo']) ?></span>
        <div class="opciones-radio">
          <?php foreach ($regla['valores'] as $v => $texto): ?>
            <label><input type="radio" name="<?= esc($clave) ?>" value="<?= esc($v) ?>"<?= $marcado($clave, $v, $v === $porDefecto[$clave]) ?>><span><?= esc($texto) ?></span></label>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</fieldset>
