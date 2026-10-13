/* ==========================================================================
   CEPC · Sitio público
   sitio.js — Tema claro/oscuro, menú móvil, barra con fondo al bajar,
   aparición de secciones y flechas de la agenda.

   Sin dependencias. Todo funciona sin este archivo, salvo el cambio de tema.
   ========================================================================== */

(function () {
  'use strict';

  var raiz = document.documentElement;
  var sinMovimiento = window.matchMedia('(prefers-reduced-motion: reduce)');

  /* ---------------------------------------------------------------------
     Tema
     --------------------------------------------------------------------- */

  var botonTema = document.querySelector('[data-tema-boton]');
  var textoTema = document.querySelector('[data-tema-texto]');
  var colorBarra = document.querySelector('[data-color-barra]');

  function pintarTema(tema) {
    raiz.setAttribute('data-tema', tema);
    var oscuro = tema === 'oscuro';
    if (botonTema) botonTema.setAttribute('aria-pressed', String(oscuro));
    if (textoTema) textoTema.textContent = oscuro ? 'Activar modo claro' : 'Activar modo oscuro';
    if (botonTema) botonTema.title = oscuro ? 'Modo claro' : 'Modo oscuro';
    if (colorBarra) colorBarra.setAttribute('content', oscuro ? '#0e0f13' : '#fbfbfd');
  }

  pintarTema(raiz.getAttribute('data-tema') === 'oscuro' ? 'oscuro' : 'claro');

  if (botonTema) {
    botonTema.addEventListener('click', function () {
      var nuevo = raiz.getAttribute('data-tema') === 'oscuro' ? 'claro' : 'oscuro';
      try { localStorage.setItem('cepc-tema', nuevo); } catch (e) { /* modo privado */ }

      if (document.startViewTransition && !sinMovimiento.matches) {
        document.startViewTransition(function () { pintarTema(nuevo); });
      } else {
        pintarTema(nuevo);
      }
    });
  }

  /* ---------------------------------------------------------------------
     Menú móvil
     --------------------------------------------------------------------- */

  var barra = document.querySelector('[data-barra]');
  var botonMenu = document.querySelector('[data-menu-boton]');
  var nav = document.getElementById('nav-principal');

  function abrirMenu(abrir) {
    if (!barra || !botonMenu) return;
    barra.classList.toggle('barra--abierta', abrir);
    botonMenu.setAttribute('aria-expanded', String(abrir));
    raiz.style.overflow = abrir ? 'hidden' : '';
  }

  if (botonMenu && nav) {
    botonMenu.addEventListener('click', function () {
      abrirMenu(botonMenu.getAttribute('aria-expanded') !== 'true');
    });
    nav.addEventListener('click', function (evento) {
      if (evento.target.closest('a')) abrirMenu(false);
    });
    document.addEventListener('keydown', function (evento) {
      if (evento.key === 'Escape' && botonMenu.getAttribute('aria-expanded') === 'true') {
        abrirMenu(false);
        botonMenu.focus();
      }
    });
    window.matchMedia('(min-width: 861px)').addEventListener('change', function (m) {
      if (m.matches) abrirMenu(false);
    });
  }

  /* ---------------------------------------------------------------------
     Barra con fondo: transparente sobre el banner, esmerilada al bajar.
     Se usa un testigo de 1px arriba de la página en vez de escuchar scroll.
     --------------------------------------------------------------------- */

  if (barra && 'IntersectionObserver' in window) {
    var testigo = document.createElement('div');
    testigo.setAttribute('aria-hidden', 'true');
    testigo.style.cssText = 'position:absolute;top:0;left:0;width:1px;height:24px;pointer-events:none';
    document.body.prepend(testigo);

    new IntersectionObserver(function (entradas) {
      barra.classList.toggle('barra--solida', !entradas[0].isIntersecting);
    }).observe(testigo);
  } else if (barra) {
    barra.classList.add('barra--solida');
  }

  /* ---------------------------------------------------------------------
     Aparición de secciones
     --------------------------------------------------------------------- */

  var revelables = document.querySelectorAll('.revelar');

  if ('IntersectionObserver' in window && !sinMovimiento.matches) {
    var observador = new IntersectionObserver(function (entradas) {
      entradas.forEach(function (entrada) {
        if (entrada.isIntersecting) {
          entrada.target.classList.add('visible');
          observador.unobserve(entrada.target);
        }
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.12 });

    revelables.forEach(function (el) { observador.observe(el); });
  } else {
    revelables.forEach(function (el) { el.classList.add('visible'); });
  }

  /* ---------------------------------------------------------------------
     Flechas de la agenda
     --------------------------------------------------------------------- */

  document.querySelectorAll('[data-paletas]').forEach(function (grupo) {
    var carril = document.getElementById(grupo.getAttribute('data-paletas'));
    if (!carril) return;

    var anterior = grupo.querySelector('[data-dir="-1"]');
    var siguiente = grupo.querySelector('[data-dir="1"]');
    var tarjetas = carril.children;

    grupo.addEventListener('click', function (evento) {
      var boton = evento.target.closest('[data-dir]');
      if (!boton) return;
      var paso = carril.clientWidth * 0.8 * Number(boton.getAttribute('data-dir'));
      carril.scrollBy({ left: paso, behavior: sinMovimiento.matches ? 'auto' : 'smooth' });
    });

    // Las flechas se apagan cuando la primera o la última tarjeta están a la vista.
    if ('IntersectionObserver' in window && tarjetas.length) {
      var bordes = new IntersectionObserver(function (entradas) {
        entradas.forEach(function (entrada) {
          var boton = entrada.target === tarjetas[0] ? anterior : siguiente;
          if (boton) boton.disabled = entrada.isIntersecting;
        });
      }, { root: carril, threshold: 0.95 });

      bordes.observe(tarjetas[0]);
      bordes.observe(tarjetas[tarjetas.length - 1]);
    }
  });

  window.cepcListo = true;
})();
