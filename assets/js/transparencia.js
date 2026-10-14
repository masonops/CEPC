/* ==========================================================================
   CEPC · Sitio público
   transparencia.js — Filtros del libro de movimientos: Todos / Ingresos /
   Gastos, búsqueda por texto y «Ver todos» cuando la lista es larga.
   Sin JavaScript se ve la tabla completa.
   ========================================================================== */

(function () {
  'use strict';

  var libro = document.querySelector('[data-libro]');
  if (!libro) return;

  var VISIBLES = 12;
  var filas = Array.prototype.slice.call(libro.querySelectorAll('tbody tr'));
  var estado = libro.querySelector('[data-libro-estado]');
  var buscador = libro.querySelector('[data-libro-buscar]');
  var tipo = 'todos';
  var todos = false;

  var mas = document.createElement('button');
  mas.type = 'button';
  mas.className = 'boton boton--vidrio libro__mas';
  libro.appendChild(mas);

  function normalizar(t) {
    return String(t).normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
  }

  filas.forEach(function (f) { f._texto = normalizar(f.textContent); });

  function aplicar() {
    var q = normalizar(buscador.value.trim());
    var coinciden = filas.filter(function (f) {
      return (tipo === 'todos' || f.getAttribute('data-tipo') === tipo) && (!q || f._texto.indexOf(q) !== -1);
    });
    filas.forEach(function (f) { f.hidden = true; });
    coinciden.forEach(function (f, i) { f.hidden = !todos && !q && i >= VISIBLES; });

    var mostradas = coinciden.filter(function (f) { return !f.hidden; }).length;
    estado.textContent = coinciden.length
      ? 'Mostrando ' + mostradas + ' de ' + coinciden.length + ' movimientos.'
      : 'Ningún movimiento coincide con la búsqueda.';
    mas.hidden = mostradas >= coinciden.length;
    mas.textContent = 'Ver los ' + coinciden.length + ' movimientos';
  }

  libro.addEventListener('click', function (evento) {
    var boton = evento.target.closest('[data-libro-tipo]');
    if (boton) {
      tipo = boton.getAttribute('data-libro-tipo');
      libro.querySelectorAll('[data-libro-tipo]').forEach(function (b) {
        b.setAttribute('aria-pressed', String(b === boton));
      });
      aplicar();
    }
  });

  mas.addEventListener('click', function () { todos = true; aplicar(); });
  buscador.addEventListener('input', aplicar);
  aplicar();
})();
