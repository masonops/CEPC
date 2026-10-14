<?php
/**
 * CEPC · Sitio público
 * transparencia.php — Rendición de cuentas, proyectos, asambleas,
 * votaciones y documentos oficiales.
 *
 * Todo sale del panel (Finanzas y Secretaría) en sólo lectura; qué se
 * publica y qué no está explicado en includes/datos-transparencia.php.
 * Cada bloque es un archivo en includes/secciones/transparencia/.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/arranque.php';

$anioCuentas = transparencia_anio();

$secciones = [
    'resumen'     => 'Cuentas',
    'movimientos' => 'Movimientos',
    'proyectos'   => 'Proyectos',
    'asambleas'   => 'Asambleas',
    'votaciones'  => 'Votaciones',
    'documentos'  => 'Documentos',
];

$titulo       = 'Transparencia · ' . marca();
$descripcion  = 'Ingresos y gastos, proyectos, asambleas, votaciones y estatutos del CEPC.';
$paginaActual = 'transparencia';
$estilos      = ['pagina', 'transparencia'];
$scripts      = ['transparencia'];
$cabecera     = [
    'titulo' => 'Transparencia',
    'bajada' => es_lista()
        ? 'Nuestra propuesta: si somos CEPC, cada peso que entre y salga, lo que se decida en asamblea y las reglas del centro estarán aquí, a la vista de todos.'
        : 'Cada peso que entra y sale, lo que se decide en asamblea y las reglas con las que funcionamos.',
    'icono'  => 'scales',
];

require __DIR__ . '/includes/partes/encabezado.php';
?>

<main id="contenido">
  <?php require __DIR__ . '/includes/partes/cabecera-pagina.php'; ?>

  <?php if (es_lista()): ?>
    <div class="contenedor transparencia-propuesta">
      <p class="aviso-ejemplo"><?= icono('info') ?>Esta página muestra cómo funcionará la rendición de cuentas si somos elegidos. Mientras tanto, los montos, asambleas y votaciones son de ejemplo.</p>
    </div>
  <?php endif; ?>

  <nav class="indice-pagina" aria-label="En esta página">
    <div class="contenedor">
      <ul>
        <?php foreach ($secciones as $ancla => $nombre): ?>
          <li><a href="#<?= esc($ancla === 'resumen' ? 'cuentas' : $ancla) ?>"><?= esc($nombre) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </nav>

  <?php foreach (array_keys($secciones) as $seccion): ?>
    <?php require __DIR__ . '/includes/secciones/transparencia/' . $seccion . '.php'; ?>
  <?php endforeach; ?>
</main>

<?php require __DIR__ . '/includes/partes/pie.php'; ?>
