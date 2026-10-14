<?php
/**
 * CEPC · Sitio público
 * buscar.php — Resultados de búsqueda en una página completa.
 *
 * Es el destino del botón «Buscar» cuando no hay JavaScript, y el enlace
 * para compartir una búsqueda (buscar.php?q=MA1001). Con JavaScript, la
 * lista se actualiza mientras se escribe.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/arranque.php';

$consulta   = trim((string) ($_GET['q'] ?? ''));
$resultados = sitio('estatico', false) ? [] : busqueda_buscar($consulta);

$titulo       = 'Buscar · ' . sitio('sigla');
$descripcion  = 'Busca ramos, salas, solemnes, entregas y lugares del campus.';
$paginaActual = 'buscar';
$estilos      = ['pagina', 'buscar'];
$cabecera     = ['titulo' => 'Buscar', 'bajada' => 'Ramos, salas, solemnes, entregas y dónde comer.', 'icono' => 'magnifying-glass'];

require __DIR__ . '/includes/partes/encabezado.php';
?>

<main id="contenido">
  <?php require __DIR__ . '/includes/partes/cabecera-pagina.php'; ?>
  <?php require __DIR__ . '/includes/secciones/buscar/resultados.php'; ?>
</main>

<?php require __DIR__ . '/includes/partes/pie.php'; ?>
