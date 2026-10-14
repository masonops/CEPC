<?php
/**
 * CEPC · Sitio público
 * roomie.php?id=N — Ficha completa de un aviso de Roomies.
 *
 * Secciones (includes/secciones/roomie/):
 *   galeria.php     fotos (con visor a pantalla completa)
 *   cabecera.php    título, lugar, compartir y guardar
 *   detalles.php    lo principal: cifras, cómo llegar, descripción, qué
 *                   incluye, comodidades, convivencia y quién publica
 *   contacto.php    precio desglosado y formulario para escribir
 *   similares.php   otros avisos parecidos
 *
 * Recibe por POST el formulario de contacto y el de reporte.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/arranque.php';

$roomie = datos_roomie((int) ($_GET['id'] ?? 0));
$contacto = ['ok' => false, 'errores' => [], 'enviado' => false];
$reporte = null;

if ($roomie !== null && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && !sitio('estatico', false)) {
    require_once __DIR__ . '/includes/roomies/acciones.php';
    if (($_POST['accion'] ?? '') === 'reportar') {
        $reporte = roomies_reportar($roomie, $_POST);
    } else {
        $contacto = roomies_contactar($roomie, $_POST) + ['enviado' => true];
    }
}
if ($roomie === null && !sitio('estatico', false)) {
    http_response_code(404);
}
// La sesión (para el token de los formularios) debe abrirse antes de
// imprimir cualquier cosa.
if ($roomie !== null && !sitio('estatico', false)) {
    require_once __DIR__ . '/includes/roomies/acciones.php';
    roomies_csrf();
}

$titulo       = ($roomie['titulo'] ?? 'Aviso no disponible') . ' · Roomies · ' . marca();
$descripcion  = $roomie !== null
    ? roomie_pesos($roomie['precio']) . ' al mes · ' . $roomie['tipo_datos']['largo'] . ' en ' . $roomie['comuna'] . ', a ' . $roomie['cercania']['a_pie'] . ' min a pie de Beauchef.'
    : 'Este aviso ya no está disponible.';
$imagenCompartir = $roomie['fotos'][0]['g'] ?? null;
$paginaActual = 'roomies';
$estilos      = ['pagina', 'formularios', 'roomies', 'roomie'];
$scripts      = ['roomies-mapa', 'roomies', 'roomie'];
$conMapa      = true;

require __DIR__ . '/includes/partes/encabezado.php';
?>

<main id="contenido" class="ficha-roomie">
  <?php if ($roomie === null): ?>
    <div class="contenedor ficha-roomie__nada">
      <a class="enlace-flecha" href="<?= esc(pagina('roomies')) ?>"><?= icono('arrow-left') ?>Volver a Roomies</a>
      <h1>Este aviso ya no está disponible</h1>
      <p>Puede que ya encontraron roomie o que el aviso venció. Mira los demás avisos.</p>
    </div>
  <?php else: ?>
    <?php require __DIR__ . '/includes/secciones/roomie/cabecera.php'; ?>
    <?php require __DIR__ . '/includes/secciones/roomie/galeria.php'; ?>
    <div class="contenedor ficha-roomie__cuerpo">
      <div class="ficha-roomie__principal">
        <?php require __DIR__ . '/includes/secciones/roomie/detalles.php'; ?>
      </div>
      <aside class="ficha-roomie__lateral" id="contactar" aria-label="Precio y contacto">
        <?php require __DIR__ . '/includes/secciones/roomie/contacto.php'; ?>
      </aside>
    </div>
    <?php require __DIR__ . '/includes/secciones/roomie/similares.php'; ?>
  <?php endif; ?>
</main>

<?php require __DIR__ . '/includes/partes/pie.php'; ?>
