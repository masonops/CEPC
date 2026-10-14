<?php
/**
 * CEPC · Sitio público
 * includes/secciones/roomies-publicar/precio.php — Arriendo por persona,
 * gastos comunes, garantía, qué incluye, desde cuándo y estadía mínima.
 */

declare(strict_types=1);
?>
<fieldset class="parte" id="parte-precio" data-parte="precio">
  <legend><span class="parte__numero">4</span>Precio</legend>
  <p class="parte__ayuda">Todo por persona y al mes. Si arriendan un depto entre varios, divide el total.</p>
  <div class="rejilla rejilla--2">
    <label class="campo<?= $conError('precio') ?>">
      <span>Arriendo</span>
      <span class="campo-con-prefijo"><span>$</span><input type="number" name="precio" min="50000" max="3000000" step="1000" inputmode="numeric" required placeholder="230000" value="<?= $valor('precio') ?>" data-precio></span>
      <?= $error('precio') ?>
    </label>
    <label class="campo">
      <span>Gastos comunes</span>
      <span class="campo-con-prefijo"><span>$</span><input type="number" name="gastos_comunes" min="0" max="1000000" step="1000" inputmode="numeric" placeholder="25000" value="<?= $valor('gastos_comunes') ?>" data-gastos></span>
    </label>
  </div>
  <p class="publicar__total" data-total hidden>Total al mes: <strong></strong></p>

  <div class="campo">
    <span>Incluido en el precio</span>
    <div class="casillas">
      <?php foreach (ROOMIES_INCLUYE as $clave => $item): ?>
        <label><input type="checkbox" name="incluye[]" value="<?= esc($clave) ?>"<?= $marcado('incluye', $clave) ?><?= $clave === 'gastos' ? ' data-incluye-gastos' : '' ?>><span><?= icono($item['icono']) ?><?= esc($item['texto']) ?></span></label>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="rejilla rejilla--3">
    <label class="campo">
      <span>Garantía</span>
      <select name="garantia_meses">
        <?php foreach (['1' => '1 mes', '0.5' => 'Medio mes', '2' => '2 meses', '0' => 'Sin garantía'] as $v => $t): ?>
          <option value="<?= esc($v) ?>"<?= ($_POST['garantia_meses'] ?? '1') === $v ? ' selected' : '' ?>><?= esc($t) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label class="campo">
      <span>Disponible desde</span>
      <input type="date" name="disponible_desde" min="<?= date('Y-m-d') ?>" value="<?= $valor('disponible_desde') ?>">
    </label>
    <label class="campo">
      <span>Estadía mínima</span>
      <select name="estadia_minima">
        <?php foreach (['0' => 'Sin mínimo', '3' => '3 meses', '5' => 'Un semestre', '6' => '6 meses', '10' => 'Un año académico', '12' => '12 meses'] as $v => $t): ?>
          <option value="<?= esc($v) ?>"<?= ($_POST['estadia_minima'] ?? '0') === $v ? ' selected' : '' ?>><?= esc($t) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
  </div>
</fieldset>
