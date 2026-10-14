<?php
/**
 * CEPC · Sitio público
 * calculadora.php — Calculadora de notas (ponderaciones).
 *
 * Con las notas que ya tienes y el peso de cada evaluación, calcula el
 * promedio mínimo que necesitas en lo que queda para aprobar (4,0) o para
 * ir a segunda (3,7). Todo se calcula en el navegador (assets/js/calculadora.js)
 * y se guarda sólo en el dispositivo de cada persona.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/arranque.php';

$titulo       = 'Calculadora de notas · ' . sitio('sigla');
$descripcion  = 'Calcula la nota mínima que necesitas para aprobar con 4,0 o ir a segunda con 3,7.';
$paginaActual = 'calculadora';
$estilos      = ['pagina', 'calculadora'];
$scripts      = ['calculadora'];
$cabecera     = [
    'titulo' => '¿Cuánto necesito?',
    'bajada' => 'Anota el peso de cada evaluación y las notas que ya tienes. Te decimos el mínimo que necesitas en lo que queda.',
    'icono'  => 'calculator',
];

require __DIR__ . '/includes/partes/encabezado.php';
?>

<main id="contenido">
  <?php require __DIR__ . '/includes/partes/cabecera-pagina.php'; ?>
  <?php require __DIR__ . '/includes/secciones/calculadora/calculadora.php'; ?>
  <?php require __DIR__ . '/includes/secciones/calculadora/explicacion.php'; ?>
</main>

<?php require __DIR__ . '/includes/partes/pie.php'; ?>
