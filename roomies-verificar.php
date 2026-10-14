<?php
/**
 * CEPC · Sitio público
 * roomies-verificar.php?token=… — El enlace del correo de confirmación.
 * Publica el aviso (o lo deja en revisión si config.php → roomies.moderar).
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/arranque.php';
require_once __DIR__ . '/includes/roomies/acciones.php';

$verificado = roomies_verificar((string) ($_GET['token'] ?? ''));
if ($verificado === null) {
    http_response_code(404);
}

$titulo       = 'Confirmar aviso · Roomies · ' . marca();
$descripcion  = 'Confirmación de un aviso de Roomies.';
$paginaActual = 'roomies';
$estilos      = ['pagina', 'formularios', 'roomies-publicar'];

require __DIR__ . '/includes/partes/encabezado.php';
?>

<main id="contenido" class="publicar">
  <div class="contenedor">
    <div class="publicar-listo">
      <?php if ($verificado === null): ?>
        <span class="publicar-listo__icono"><?= icono('warning') ?></span>
        <h1 class="oculto">Enlace no válido</h1>
        <h2>Este enlace ya no sirve</h2>
        <p>Puede que ya hayas confirmado el aviso. Si lo necesitas, usa el enlace para administrarlo que te llegó en el mismo correo.</p>
        <a class="boton boton--claro" href="<?= esc(pagina('roomies')) ?>">Ir a Roomies</a>
      <?php elseif ($verificado['estado'] === 'revision'): ?>
        <span class="publicar-listo__icono"><?= icono('hourglass') ?></span>
        <h1 class="oculto">Correo confirmado</h1>
        <h2>¡Correo confirmado!</h2>
        <p>Tu aviso «<?= esc($verificado['titulo']) ?>» quedó en revisión. Lo publicamos apenas lo veamos (normalmente el mismo día).</p>
        <a class="boton boton--claro" href="roomies-gestionar.php?token=<?= esc($verificado['token_gestion']) ?>">Administrar mi aviso</a>
      <?php else: ?>
        <span class="publicar-listo__icono"><?= icono('seal-check') ?></span>
        <h1 class="oculto">Aviso publicado</h1>
        <h2>¡Tu aviso está publicado!</h2>
        <p>«<?= esc($verificado['titulo']) ?>» ya aparece en Roomies con el sello de verificado. Te avisaremos por correo cuando alguien te escriba.</p>
        <div class="gestion__acciones">
          <a class="boton boton--primario" href="<?= esc(enlace_roomie($verificado['id'])) ?>">Ver mi aviso</a>
          <a class="boton boton--claro" href="roomies-gestionar.php?token=<?= esc($verificado['token_gestion']) ?>">Administrarlo</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<?php require __DIR__ . '/includes/partes/pie.php'; ?>
