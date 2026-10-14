<?php
/**
 * CEPC · Sitio público
 * index.php — Portada.
 *
 * Cada sección vive en su propio archivo dentro de includes/secciones/inicio/
 * (y su estilo en assets/css/inicio/). Para cambiar el orden, mueve las
 * líneas de $secciones; para quitar una, bórrala de la lista.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/arranque.php';

$secciones = [
    'portada',        // Banner (en modo lista: el lema y la próxima fecha importante)
    'articulos',      // Propuestas y artículos (se publican desde el panel)
    'agenda',         // Próximas fechas
    'ayuda',          // Lo que necesitas (apuntes, tutorías, bienestar, mapa, calculadora…)
    'novedades',      // Lo último en Instagram
    'transparencia',  // Cuentas claras: resumen y enlaces a la página de transparencia
    'somos',          // Quiénes somos y la directiva
    'preguntas',      // Preguntas frecuentes
];

$titulo       = es_lista()
    ? marca() . ' · ' . sitio('lista')['lema'] . ' · Lista al CEPC'
    : sitio('sigla') . ' · ' . sitio('nombre');
$descripcion  = es_lista()
    ? sitio('lista')['descripcion'] . ' de la FCFM. Nuestras propuestas, fechas importantes, mapa del campus y más.'
    : 'Fechas, apuntes, tutorías y apoyo para Plan Común de la FCFM, Universidad de Chile.';
$paginaActual = 'inicio';
$estilos      = array_merge(
    ['mapa-dibujo'],  // la miniatura del mapa en «Lo que necesitas»
    array_map(static fn(string $s): string => 'inicio/' . $s, $secciones),
);

require __DIR__ . '/includes/partes/encabezado.php';
?>

<main id="contenido">
<?php foreach ($secciones as $seccion): ?>
  <?php require __DIR__ . '/includes/secciones/inicio/' . $seccion . '.php'; ?>
<?php endforeach; ?>
</main>

<?php require __DIR__ . '/includes/partes/pie.php'; ?>
