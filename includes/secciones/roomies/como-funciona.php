<?php
/**
 * CEPC · Sitio público
 * includes/secciones/roomies/como-funciona.php — Pasos para publicar y
 * consejos de seguridad.
 */

declare(strict_types=1);

$dominios = implode(' o ', array_map(static fn(string $d): string => '@' . $d, (array) (sitio('roomies')['dominios'] ?? [])));
$pasos = [
    ['icono' => 'envelope-simple', 'titulo' => 'Publica con tu correo U. de Chile', 'texto' => 'Sólo se puede publicar y escribir con un correo ' . $dominios . '. Así sabes que al otro lado hay un estudiante.'],
    ['icono' => 'seal-check',      'titulo' => 'Confirmas y queda publicado',      'texto' => 'Te llega un enlace para confirmar el aviso y otro, privado, para pausarlo o borrarlo cuando encuentres roomie.'],
    ['icono' => 'chat-circle-text', 'titulo' => 'Te escriben a tu correo',         'texto' => 'Tu correo nunca aparece en la página: los mensajes te llegan directo y respondes desde tu bandeja.'],
];
$consejos = [
    'Visita el lugar y conoce a quienes viven ahí antes de transferir cualquier monto.',
    'Pide el contrato por escrito, aunque sea un subarriendo entre estudiantes.',
    'Desconfía de precios muy bajos o de quien te apure para pagar la garantía.',
    'Si algo te parece raro, repórtalo desde el aviso: lo revisamos.',
];
?>
<section class="seccion roomies-como" id="como-funciona" aria-labelledby="como-titulo">
  <div class="contenedor">
    <h2 id="como-titulo" class="seccion__titulo">Cómo funciona</h2>
    <ol class="roomies-pasos">
      <?php foreach ($pasos as $i => $p): ?>
        <li>
          <span class="roomies-pasos__numero"><?= $i + 1 ?></span>
          <span class="roomies-pasos__icono"><?= icono($p['icono']) ?></span>
          <h3><?= esc($p['titulo']) ?></h3>
          <p><?= esc($p['texto']) ?></p>
        </li>
      <?php endforeach; ?>
    </ol>

    <div class="roomies-seguridad">
      <span class="roomies-seguridad__icono"><?= icono('shield-check') ?></span>
      <div>
        <h3>Antes de pagar nada</h3>
        <ul>
          <?php foreach ($consejos as $c): ?><li><?= esc($c) ?></li><?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</section>
