<?php
/**
 * CEPC · Sitio público
 * roomies-publicar.php — Formulario para publicar una pieza o depto en
 * Roomies. Al enviarlo se guarda como «pendiente» y llega un correo al
 * correo U. de Chile para confirmarlo (includes/roomies/acciones.php).
 *
 * Cada parte del formulario es un archivo en
 * includes/secciones/roomies-publicar/ (en el orden de $partes).
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/arranque.php';

$estatico  = sitio('estatico', false);
$activo    = (bool) (sitio('roomies')['activo'] ?? true);
$resultado = null;

if (!$estatico) {
    require_once __DIR__ . '/includes/roomies/acciones.php';
    roomies_csrf(); // abre la sesión antes de imprimir nada
    if ($activo && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $resultado = roomies_publicar($_POST, $_FILES);
    }
}
$instalado = $estatico || roomies_instalado();

// Valor anterior de un campo (si el formulario volvió con errores).
$valor = static fn(string $campo, string $defecto = ''): string => esc((string) ($_POST[$campo] ?? $defecto));
$marcado = static fn(string $campo, string $opcion, bool $defecto = false): string =>
    (isset($_POST[$campo]) ? in_array($opcion, (array) $_POST[$campo], true) || $_POST[$campo] === $opcion : $defecto) ? ' checked' : '';
$errores = $resultado['errores'] ?? [];
$error = static fn(string $campo): string => isset($errores[$campo]) ? '<small>' . esc($errores[$campo]) . '</small>' : '';
$conError = static fn(string $campo): string => isset($errores[$campo]) ? ' campo--error' : '';

$partes = [
    'tu'          => 'Sobre ti',
    'lugar'       => 'Dónde queda',
    'detalles'    => 'El lugar',
    'precio'      => 'Precio',
    'comodidades' => 'Comodidades',
    'convivencia' => 'Convivencia',
    'fotos'       => 'Fotos',
    'contacto'    => 'Contacto',
];

$titulo       = 'Publicar en Roomies · ' . marca();
$descripcion  = 'Publica tu pieza o depto para compartir con estudiantes de la Universidad de Chile.';
$paginaActual = 'roomies';
$estilos      = ['pagina', 'formularios', 'roomies', 'roomies-publicar'];
$scripts      = ['roomies-mapa', 'roomies-publicar'];
$conMapa      = true;

require __DIR__ . '/includes/partes/encabezado.php';
?>

<main id="contenido" class="publicar">
  <header class="contenedor publicar__cabecera">
    <a class="enlace-flecha publicar__volver" href="<?= esc(pagina('roomies')) ?>"><?= icono('arrow-left') ?>Roomies</a>
    <h1>Publica tu pieza</h1>
    <p>Tarda unos 5 minutos. Te pediremos confirmar tu correo de la Universidad antes de publicarlo.</p>
  </header>

  <?php if (!empty($resultado['ok'])): ?>
    <?php require __DIR__ . '/includes/secciones/roomies-publicar/listo.php'; ?>
  <?php elseif (!$activo || !$instalado): ?>
    <div class="contenedor">
      <p class="formulario__nota publicar__aviso"><?= icono('hourglass') ?>Roomies todavía no recibe avisos. Vuelve muy pronto.</p>
    </div>
  <?php else: ?>
    <div class="contenedor publicar__cuerpo">
      <nav class="publicar__indice" aria-label="Partes del formulario">
        <ol>
          <?php foreach ($partes as $id => $nombre): ?>
            <li><a href="#parte-<?= esc($id) ?>" data-indice="<?= esc($id) ?>"><?= esc($nombre) ?></a></li>
          <?php endforeach; ?>
        </ol>
      </nav>

      <form class="publicar__formulario" method="post" enctype="multipart/form-data" novalidate data-publicar<?= $estatico ? ' data-solo-vista' : '' ?>>
        <?= $estatico ? '' : roomies_campos_seguridad() ?>
        <?php if ($estatico): ?>
          <p class="formulario__nota"><?= icono('info') ?>Esta es la vista previa: puedes recorrer el formulario, pero no se envía.</p>
        <?php endif; ?>
        <?php if (!empty($errores)): ?>
          <div class="formulario__error" role="alert" tabindex="-1" data-errores>
            <strong>Revisa lo marcado en rojo.</strong>
            <?= !empty($errores['general']) ? ' ' . esc($errores['general']) : '' ?>
          </div>
        <?php endif; ?>

        <?php foreach (array_keys($partes) as $parte): ?>
          <?php require __DIR__ . '/includes/secciones/roomies-publicar/' . $parte . '.php'; ?>
        <?php endforeach; ?>
        <?php require __DIR__ . '/includes/secciones/roomies-publicar/enviar.php'; ?>
      </form>
    </div>
  <?php endif; ?>
</main>

<?php require __DIR__ . '/includes/partes/pie.php'; ?>
