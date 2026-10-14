<?php
/**
 * CEPC · Sitio público
 * includes/secciones/roomies-publicar/enviar.php — Condiciones y botón.
 */

declare(strict_types=1);

$dias = (int) (sitio('roomies')['dias_vigencia'] ?? 60);
?>
<div class="parte parte--final">
  <label class="aceptar<?= $conError('acepto') ?>">
    <input type="checkbox" name="acepto" value="1" required<?= $marcado('acepto', '1') ?>>
    <span>Confirmo que soy estudiante, que la información y las fotos son reales y que no cobraré nada por adelantado sin un acuerdo escrito. El aviso dura <?= $dias ?> días y lo puedo borrar cuando quiera.</span>
  </label>
  <?= $error('acepto') ?>
  <button class="boton boton--primario publicar__enviar" type="submit"<?= sitio('estatico', false) ? ' disabled' : '' ?> data-publicar-enviar><?= icono('paper-plane-tilt') ?>Enviar y confirmar por correo</button>
</div>
