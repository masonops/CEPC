/* CEPC · Sitio público
   articulos.js — Filtro por categoría de la página de publicaciones. */

(function () {
  'use strict';

  var filtro = document.querySelector('[data-articulos-filtro]');
  if (!filtro) return;
  filtro.hidden = false;

  var botones = filtro.querySelectorAll('button');
  var tarjetas = document.querySelectorAll('.tarjetas-articulo > li');

  function mostrar(categoria) {
    botones.forEach(function (b) {
      b.setAttribute('aria-pressed', String(b.dataset.categoria === categoria));
    });
    tarjetas.forEach(function (t) {
      t.hidden = categoria !== '' && t.dataset.categoria !== categoria;
    });
  }

  filtro.addEventListener('click', function (e) {
    var b = e.target.closest('button');
    if (b) mostrar(b.dataset.categoria);
  });

  var inicial = new URLSearchParams(location.search).get('categoria') || '';
  if (filtro.querySelector('[data-categoria="' + inicial + '"]')) mostrar(inicial);
})();
