<?php
/**
 * CEPC · Sitio público
 * includes/secciones/roomies-publicar/detalles.php — Dormitorios, baños,
 * metros, piso y cuántas personas viven y caben.
 */

declare(strict_types=1);

$numero = static function (string $nombre, string $etiqueta, int $min, int $max, string $defecto, bool $opcional = false) use ($valor): string {
    return '<label class="campo campo--numero"><span>' . esc($etiqueta) . ($opcional ? ' <span class="campo__ayuda">· opcional</span>' : '') . '</span>'
        . '<input type="number" name="' . esc($nombre) . '" min="' . $min . '" max="' . $max . '" inputmode="numeric" value="' . $valor($nombre, $defecto) . '"></label>';
};
?>
<fieldset class="parte" id="parte-detalles" data-parte="detalles">
  <legend><span class="parte__numero">3</span>El lugar</legend>
  <div class="rejilla rejilla--3">
    <?= $numero('dormitorios', 'Dormitorios', 1, 10, '2') ?>
    <?= $numero('banos', 'Baños', 1, 6, '1') ?>
    <?= $numero('m2', 'Metros cuadrados', 8, 1000, '', true) ?>
    <?= $numero('piso', 'Piso', -3, 60, '', true) ?>
    <?= $numero('roomies_actuales', 'Personas que ya viven', 0, 10, '1') ?>
    <?= $numero('piezas_libres', 'Piezas libres', 1, 8, '1') ?>
  </div>
</fieldset>
