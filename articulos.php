<?php
/**
 * CEPC · Sitio público
 * articulos.php — Todas las propuestas y publicaciones, con filtro por
 * categoría. Se escriben desde el panel (tabla `articulos`).
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/arranque.php';

$titulo       = (es_lista() ? 'Propuestas y artículos' : 'Publicaciones') . ' · ' . marca();
$descripcion  = es_lista()
    ? 'Las propuestas de ' . marca() . ' para el CEPC, fechas importantes y noticias de Plan Común.'
    : 'Publicaciones y noticias del Centro de Estudiantes de Plan Común.';
$paginaActual = 'articulos';
$estilos      = ['pagina', 'inicio/articulos', 'articulos'];
$scripts      = ['articulos'];
$cabecera     = [
    'titulo' => es_lista() ? 'Propuestas y artículos' : 'Publicaciones',
    'bajada' => es_lista()
        ? 'Lo que queremos hacer en el CEPC, explicado con calma, y las fechas que no te puedes perder.'
        : 'Noticias y fechas importantes para Plan Común.',
    'icono'  => 'megaphone',
];

require __DIR__ . '/includes/partes/encabezado.php';
?>

<main id="contenido">
  <?php require __DIR__ . '/includes/partes/cabecera-pagina.php'; ?>
  <?php require __DIR__ . '/includes/secciones/articulos/listado.php'; ?>
</main>

<?php require __DIR__ . '/includes/partes/pie.php'; ?>
