<?php
/**
 * CEPC · Sitio público
 * includes/secciones/roomies-publicar/listo.php — Después de enviar:
 * «revisa tu correo».
 */

declare(strict_types=1);

$correoUsado = (string) ($resultado['datos']['autor_correo'] ?? '');
?>
<div class="contenedor">
  <div class="publicar-listo">
    <span class="publicar-listo__icono"><?= icono('envelope-simple') ?></span>
    <h2>Revisa tu correo</h2>
    <?php if (($resultado['enviado'] ?? true) === false): ?>
      <p>Guardamos tu aviso, pero no pudimos enviar el correo de confirmación. Escríbenos y lo activamos a mano.</p>
    <?php else: ?>
      <p>Te mandamos un enlace a <strong><?= esc($correoUsado) ?></strong>. Ábrelo para publicar tu aviso. Si no lo ves en unos minutos, revisa la carpeta de spam.</p>
    <?php endif; ?>
    <a class="boton boton--claro" href="<?= esc(pagina('roomies')) ?>">Volver a Roomies</a>
  </div>
</div>
