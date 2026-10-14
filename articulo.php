<?php
/**
 * CEPC · Sitio público
 * articulo.php?id=N — Una propuesta o publicación completa.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/arranque.php';

$articulo = datos_articulo((int) ($_GET['id'] ?? 0));
if ($articulo === null && !sitio('estatico', false)) {
    http_response_code(404);
}

$titulo       = ($articulo['titulo'] ?? 'Publicación no encontrada') . ' · ' . marca();
$descripcion  = $articulo['resumen'] ?? 'Esta publicación no existe o ya no está disponible.';
$paginaActual = 'articulos';
$estilos      = ['pagina', 'inicio/articulos', 'articulos'];

require __DIR__ . '/includes/partes/encabezado.php';
?>

<main id="contenido">
  <?php require __DIR__ . '/includes/secciones/articulo/cuerpo.php'; ?>
  <?php require __DIR__ . '/includes/secciones/articulo/mas.php'; ?>
</main>

<?php require __DIR__ . '/includes/partes/pie.php'; ?>
