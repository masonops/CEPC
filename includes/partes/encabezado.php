<?php
/**
 * CEPC · Sitio público
 * includes/partes/encabezado.php — <head> y barra superior.
 *
 * Espera $titulo y $descripcion definidos por la página.
 */

declare(strict_types=1);
?>
<!doctype html>
<html lang="es-CL" data-tema="claro">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= esc($titulo) ?></title>
<meta name="description" content="<?= esc($descripcion) ?>">
<meta name="color-scheme" content="light dark">
<meta name="theme-color" content="#fbfbfd" data-color-barra>
<meta property="og:type" content="website">
<meta property="og:title" content="<?= esc($titulo) ?>">
<meta property="og:description" content="<?= esc($descripcion) ?>">
<meta property="og:image" content="<?= esc(recurso('assets/img/logo-cepc.png')) ?>">
<meta property="og:locale" content="es_CL">
<link rel="icon" type="image/png" href="<?= esc(recurso('assets/img/favicon.png')) ?>">
<link rel="apple-touch-icon" href="<?= esc(recurso('assets/img/apple-touch-icon.png')) ?>">
<script>
  /* Tema: claro por defecto; se recuerda el oscuro si la persona lo eligió.
     Va aquí arriba para que la página no parpadee al cargar. */
  (function () {
    var raiz = document.documentElement, tema = 'claro';
    try { if (localStorage.getItem('cepc-tema') === 'oscuro') tema = 'oscuro'; } catch (e) {}
    raiz.setAttribute('data-tema', tema);
    /* .js activa las animaciones de aparición; si sitio.js no llega a
       cargar, se quita para que nada quede escondido. */
    raiz.classList.add('js');
    setTimeout(function () { if (!window.cepcListo) raiz.classList.remove('js'); }, 2500);
  })();
</script>
<link rel="stylesheet" href="<?= esc(recurso('assets/css/sitio.css')) ?>">
<script src="<?= esc(recurso('assets/js/sitio.js')) ?>" defer></script>
</head>
<body>

<a class="saltar" href="#contenido">Saltar al contenido</a>

<?php if (sitio('demo', false)): ?>
  <p class="aviso-demo" role="note">
    <strong>Vista previa con datos de ejemplo.</strong>
    <span>Las fechas y publicaciones no son reales.</span>
  </p>
<?php endif; ?>

<header class="barra" data-barra>
  <div class="contenedor barra__interior">
    <a class="marca" href="./" aria-label="<?= esc(sitio('sigla') . ', inicio') ?>">
      <img class="marca__logo solo-claro" src="<?= esc(recurso('assets/img/marca-cepc.png')) ?>" alt="" width="26" height="30">
      <img class="marca__logo solo-oscuro" src="<?= esc(recurso('assets/img/marca-cepc-oscuro.png')) ?>" alt="" width="26" height="30">
      <span class="marca__texto" translate="no">
        <strong><?= esc(sitio('sigla')) ?></strong>
        <span>Plan Común FCFM</span>
      </span>
    </a>

    <nav class="nav" id="nav-principal" aria-label="Secciones">
      <a href="#agenda">Agenda</a>
      <a href="#ayuda">Recursos</a>
      <a href="#novedades">Novedades</a>
      <a href="#directiva">Quiénes somos</a>
      <a href="#preguntas">Preguntas</a>
    </nav>

    <div class="barra__acciones">
      <button class="boton-icono" type="button" data-tema-boton aria-pressed="false">
        <?= icono('moon', 'solo-claro') ?><?= icono('sun', 'solo-oscuro') ?>
        <span class="oculto" data-tema-texto>Activar modo oscuro</span>
      </button>
      <button class="boton-icono boton-menu" type="button" data-menu-boton aria-expanded="false" aria-controls="nav-principal">
        <?= icono('list', 'boton-menu__abrir') ?><?= icono('x', 'boton-menu__cerrar') ?>
        <span class="oculto">Menú</span>
      </button>
    </div>
  </div>
</header>
