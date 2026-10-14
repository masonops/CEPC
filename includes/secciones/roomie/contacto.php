<?php
/**
 * CEPC · Sitio público
 * includes/secciones/roomie/contacto.php — Tarjeta fija al costado: precio
 * desglosado, disponibilidad y formulario para escribir a quien publica.
 * El correo de quien publica nunca se muestra: el mensaje le llega por
 * correo y responde directo a quien escribió.
 */

declare(strict_types=1);

$garantia = (float) $roomie['garantia_meses'];
$garantiaTexto = match (true) {
    $garantia <= 0    => 'Sin garantía',
    $garantia == 0.5  => 'Medio mes · ' . roomie_pesos((int) round($roomie['precio'] / 2)),
    $garantia == 1.0  => '1 mes · ' . roomie_pesos($roomie['precio']),
    default           => (int) $garantia . ' meses · ' . roomie_pesos((int) round($roomie['precio'] * $garantia)),
};
$valor = static fn(string $c): string => esc((string) ($_POST[$c] ?? ''));
$errores = $contacto['errores'];
$mensajeInicial = 'Hola, ' . $roomie['autor_nombre'] . ". Vi tu aviso «" . $roomie['titulo'] . "» en Roomies y me interesa. Te cuento un poco de mí: ";
$whatsapp = ($roomie['mostrar_whatsapp'] && $roomie['whatsapp'])
    ? 'https://wa.me/' . $roomie['whatsapp'] . '?text=' . rawurlencode('Hola, ' . $roomie['autor_nombre'] . '. Te escribo por tu aviso «' . $roomie['titulo'] . '» en Roomies.')
    : '';
?>
<div class="tarjeta-precio">
  <p class="tarjeta-precio__monto"><strong><?= esc(roomie_pesos($roomie['precio'])) ?></strong><span>/mes por persona</span></p>

  <dl class="desglose">
    <div><dt>Arriendo</dt><dd><?= esc(roomie_pesos($roomie['precio'])) ?></dd></div>
    <div>
      <dt>Gastos comunes</dt>
      <dd><?= $roomie['gastos_incluidos'] ? 'Incluidos' : ($roomie['gastos_comunes'] > 0 ? esc(roomie_pesos($roomie['gastos_comunes'])) : '—') ?></dd>
    </div>
    <div class="desglose__total"><dt>Total al mes</dt><dd><?= esc(roomie_pesos($roomie['total'])) ?></dd></div>
  </dl>
  <?php $aparte = array_diff(['luz', 'agua', 'gas', 'internet'], $roomie['incluye']); ?>
  <?php if ($aparte !== []): ?>
    <p class="tarjeta-precio__nota">Aparte: <?= esc(implode(', ', array_map(static fn(string $k): string => mb_strtolower(ROOMIES_INCLUYE[$k]['texto']), $aparte))) ?>.</p>
  <?php endif; ?>

  <ul class="tarjeta-precio__datos">
    <li><?= icono('calendar-check') ?><span><?= esc($roomie['disponible_texto']) ?></span></li>
    <li><?= icono('key') ?><span>Garantía: <?= esc($garantiaTexto) ?></span></li>
    <?php if ((int) $roomie['estadia_minima'] > 0): ?>
      <li><?= icono('hourglass') ?><span>Mínimo <?= (int) $roomie['estadia_minima'] ?> meses</span></li>
    <?php endif; ?>
  </ul>

  <div class="tarjeta-precio__contacto">
    <h2 class="tarjeta-precio__titulo">Escríbele a <?= esc($roomie['autor_nombre']) ?></h2>

    <?php if ($contacto['ok']): ?>
      <div class="formulario__ok" role="status">
        <?= icono('check') ?>
        <p><strong>Mensaje enviado.</strong> <?= esc($roomie['autor_nombre']) ?> te responderá a tu correo.</p>
      </div>
    <?php elseif (sitio('estatico', false)): ?>
      <p class="formulario__nota"><?= icono('info') ?>En la vista previa no se envían mensajes. En el sitio real, el mensaje le llega a <?= esc($roomie['autor_nombre']) ?> por correo.</p>
    <?php endif; ?>

    <?php if (!$contacto['ok']): ?>
      <?php if (!sitio('estatico', false)) { require_once SITIO_RAIZ . '/includes/roomies/acciones.php'; } ?>
      <form class="formulario" method="post" action="#contactar" novalidate<?= sitio('estatico', false) ? ' data-solo-vista' : '' ?>>
        <?= sitio('estatico', false) ? '' : roomies_campos_seguridad() ?>
        <input type="hidden" name="accion" value="contactar">
        <?php if (!empty($errores['general'])): ?><p class="formulario__error" role="alert"><?= esc($errores['general']) ?></p><?php endif; ?>
        <label class="campo<?= isset($errores['nombre']) ? ' campo--error' : '' ?>">
          <span>Tu nombre</span>
          <input type="text" name="nombre" autocomplete="given-name" maxlength="40" required value="<?= $valor('nombre') ?>">
          <?php if (isset($errores['nombre'])): ?><small><?= esc($errores['nombre']) ?></small><?php endif; ?>
        </label>
        <label class="campo<?= isset($errores['correo']) ? ' campo--error' : '' ?>">
          <span>Tu correo U. de Chile</span>
          <input type="email" name="correo" autocomplete="email" inputmode="email" maxlength="120" required placeholder="nombre@ug.uchile.cl" value="<?= $valor('correo') ?>">
          <?php if (isset($errores['correo'])): ?><small><?= esc($errores['correo']) ?></small><?php endif; ?>
        </label>
        <label class="campo<?= isset($errores['mensaje']) ? ' campo--error' : '' ?>">
          <span>Mensaje</span>
          <textarea name="mensaje" rows="5" maxlength="1500" required><?= $valor('mensaje') !== '' ? $valor('mensaje') : esc($mensajeInicial) ?></textarea>
          <?php if (isset($errores['mensaje'])): ?><small><?= esc($errores['mensaje']) ?></small><?php endif; ?>
        </label>
        <button class="boton boton--primario boton--ancho" type="submit"<?= sitio('estatico', false) ? ' disabled' : '' ?>><?= icono('envelope-simple') ?>Enviar mensaje</button>
        <p class="formulario__privacidad"><?= icono('lock-simple') ?>Tu correo sólo se lo mandamos a <?= esc($roomie['autor_nombre']) ?>. Nadie más lo ve.</p>
      </form>
    <?php endif; ?>

    <?php if ($whatsapp !== '' || !empty($roomie['instagram'])): ?>
      <div class="tarjeta-precio__otros">
        <?php if ($whatsapp !== ''): ?>
          <a class="boton boton--claro" href="<?= esc($whatsapp) ?>" target="_blank" rel="noopener"><?= icono('whatsapp-logo') ?>WhatsApp</a>
        <?php endif; ?>
        <?php if (!empty($roomie['instagram'])): ?>
          <a class="boton boton--claro" href="https://instagram.com/<?= esc(rawurlencode((string) $roomie['instagram'])) ?>" target="_blank" rel="noopener"><?= icono('instagram-logo') ?>Instagram</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="barra-contacto" aria-hidden="true">
  <p><strong><?= esc(roomie_pesos($roomie['precio'])) ?></strong>/mes<span><?= esc($roomie['disponible_texto']) ?></span></p>
  <a class="boton boton--primario" href="#contactar" tabindex="-1">Escribir</a>
</div>
