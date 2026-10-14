<?php
/**
 * CEPC · Sitio público
 * roomies-gestionar.php?token=… — Página privada para administrar un aviso:
 * pausarlo, reactivarlo, renovarlo, marcarlo como arrendado o borrarlo.
 * El enlace llega en el correo de confirmación; quien lo tenga puede
 * administrar el aviso, por eso no se comparte.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/arranque.php';
require_once __DIR__ . '/includes/roomies/acciones.php';

roomies_csrf();
$token   = (string) ($_GET['token'] ?? '');
$roomie  = roomies_por_gestion($token);
$mensaje = '';

if ($roomie !== null && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (roomies_csrf_valido($_POST['csrf'] ?? null)) {
        $mensaje = roomies_gestionar($roomie, (string) ($_POST['accion'] ?? ''));
        $roomie = roomies_por_gestion($token);
    } else {
        $mensaje = 'La página estuvo abierta mucho rato. Intenta otra vez.';
    }
}
if ($roomie === null && $mensaje === '') {
    http_response_code(404);
}

$estados = [
    'pendiente' => 'Falta confirmar el correo',
    'revision'  => 'En revisión',
    'publicado' => 'Publicado',
    'pausado'   => 'Pausado',
    'arrendado' => 'Arrendado',
];

$titulo       = 'Administrar aviso · Roomies · ' . marca();
$descripcion  = 'Administra tu aviso de Roomies.';
$paginaActual = 'roomies';
$estilos      = ['pagina', 'formularios', 'roomies-publicar'];

require __DIR__ . '/includes/partes/encabezado.php';

$boton = static fn(string $accion, string $texto, string $clase = 'boton--claro', string $confirmar = ''): string =>
    '<form method="post"><input type="hidden" name="csrf" value="' . esc(roomies_csrf()) . '"><input type="hidden" name="accion" value="' . esc($accion) . '">'
    . '<button class="boton ' . $clase . '" type="submit"' . ($confirmar !== '' ? ' onclick="return confirm(\'' . esc($confirmar) . '\')"' : '') . '>' . esc($texto) . '</button></form>';
?>

<main id="contenido" class="publicar">
  <header class="contenedor publicar__cabecera">
    <a class="enlace-flecha publicar__volver" href="<?= esc(pagina('roomies')) ?>"><?= icono('arrow-left') ?>Roomies</a>
    <h1>Tu aviso</h1>
    <p>Este enlace es privado: quien lo tenga puede administrar tu aviso.</p>
  </header>

  <div class="contenedor">
    <div class="gestion">
      <?php if ($mensaje !== ''): ?>
        <div class="formulario__ok" role="status"><?= icono('check') ?><p><?= esc($mensaje) ?></p></div>
      <?php endif; ?>

      <?php if ($roomie === null): ?>
        <?php if ($mensaje === ''): ?>
          <p class="formulario__error">Este enlace no corresponde a ningún aviso activo.</p>
        <?php endif; ?>
        <a class="boton boton--claro" href="<?= esc(pagina('roomies')) ?>">Ir a Roomies</a>
      <?php else: $estado = (string) $roomie['estado_real']; ?>
        <div class="gestion__aviso">
          <?php if (!empty($roomie['fotos'][0])): ?><img src="<?= esc($roomie['fotos'][0]['m']) ?>" alt=""><?php endif; ?>
          <div>
            <h2><?= esc($roomie['titulo']) ?></h2>
            <p><?= esc(roomie_pesos($roomie['precio'])) ?>/mes · <?= esc($roomie['comuna']) ?></p>
            <span class="gestion__estado gestion__estado--<?= esc($estado) ?>"><?= esc($estados[$estado] ?? $estado) ?></span>
            <?php if ($estado === 'publicado' && !empty($roomie['expira_en'])): ?>
              <p class="campo__ayuda">Publicado hasta el <?= esc(fecha_larga((string) $roomie['expira_en'])) ?>.</p>
            <?php endif; ?>
          </div>
        </div>

        <div class="gestion__acciones">
          <?php if ($estado === 'publicado'): ?>
            <a class="boton boton--primario" href="<?= esc(enlace_roomie((int) $roomie['id'])) ?>">Ver aviso</a>
            <?= $boton('renovar', 'Renovar ' . (int) roomies_ajuste('dias_vigencia', 60) . ' días') ?>
            <?= $boton('pausar', 'Pausar') ?>
            <?= $boton('arrendado', 'Ya encontré roomie') ?>
          <?php elseif (in_array($estado, ['pausado', 'arrendado'], true)): ?>
            <?= $boton('reactivar', 'Publicar otra vez', 'boton--primario') ?>
          <?php endif; ?>
          <?= $boton('eliminar', 'Borrar aviso', 'boton--peligro', '¿Borrar el aviso y sus fotos? No se puede deshacer.') ?>
        </div>
        <p class="campo__ayuda">¿Quieres cambiar algo del aviso? Bórralo y publícalo de nuevo, o escríbenos.</p>
      <?php endif; ?>
    </div>
  </div>
</main>

<?php require __DIR__ . '/includes/partes/pie.php'; ?>
