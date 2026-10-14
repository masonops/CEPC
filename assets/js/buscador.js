/* ==========================================================================
   CEPC · Sitio público
   buscador.js — Buscador de la barra (estilo Spotlight) y página buscar.

   Lee el índice que deja includes/partes/buscador.php en
   <script id="indice-busqueda"> y filtra en el navegador: no hace
   peticiones al servidor. La misma lógica corre en buscar.php para quien
   no tiene JavaScript (includes/busqueda.php → busqueda_buscar()).

   Atajos: «/» o Ctrl/⌘ + K abren; Esc cierra; ↑ ↓ eligen; Enter abre.
   ========================================================================== */

(function () {
  'use strict';

  var fuente = document.getElementById('indice-busqueda');
  if (!fuente) return;

  var indice;
  try { indice = JSON.parse(fuente.textContent); } catch (e) { return; }

  var MAX_POR_GRUPO = 5;

  /** Sin tildes y en minúsculas. */
  function normalizar(texto) {
    return String(texto || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().replace(/\s+/g, ' ').trim();
  }

  // Texto preparado una sola vez por elemento.
  indice.items.forEach(function (item) {
    item._titulo = normalizar(item.titulo);
    item._todo = item._titulo + ' ' + normalizar(item.detalle + ' ' + (item.claves || ''));
  });

  /** Devuelve los elementos que calzan, ordenados por relevancia. */
  function buscar(consulta) {
    var palabras = normalizar(consulta).split(' ').filter(Boolean);
    if (!palabras.length) return [];
    var resultados = [];
    indice.items.forEach(function (item) {
      var puntaje = 0;
      for (var i = 0; i < palabras.length; i++) {
        var p = palabras[i];
        var pos = item._todo.indexOf(p);
        if (pos === -1) return;
        if (item._titulo.indexOf(p) === 0) puntaje += 3;
        else if (pos === 0 || item._todo.charAt(pos - 1) === ' ') puntaje += 2;
        else puntaje += 1;
      }
      resultados.push({ item: item, puntaje: puntaje });
    });
    resultados.sort(function (a, b) { return b.puntaje - a.puntaje; });
    return resultados.map(function (r) { return r.item; });
  }

  /** Escapa texto para insertarlo como HTML. */
  function esc(texto) {
    return String(texto).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  /** Resalta en negrita las palabras buscadas dentro del título. */
  function resaltar(texto, consulta) {
    var limpio = esc(texto);
    var palabras = normalizar(consulta).split(' ').filter(function (p) { return p.length > 1; });
    if (!palabras.length) return limpio;
    var plano = normalizar(texto);
    var marcas = [];
    palabras.forEach(function (p) {
      var pos = plano.indexOf(p);
      if (pos !== -1) marcas.push([pos, pos + p.length]);
    });
    if (!marcas.length) return limpio;
    // Se recorre el texto original carácter a carácter (misma longitud que el normalizado).
    var salida = '';
    var chars = Array.from(texto);
    chars.forEach(function (c, i) {
      var dentro = marcas.some(function (m) { return i >= m[0] && i < m[1]; });
      var antes = marcas.some(function (m) { return i - 1 >= m[0] && i - 1 < m[1]; });
      if (dentro && !antes) salida += '<mark>';
      if (!dentro && antes) salida += '</mark>';
      salida += esc(c);
    });
    if (marcas.some(function (m) { return chars.length - 1 >= m[0] && chars.length - 1 < m[1]; })) salida += '</mark>';
    return salida;
  }

  /** Dibuja los resultados agrupados dentro de un contenedor. */
  function pintar(contenedor, consulta, maxPorGrupo) {
    var resultados = buscar(consulta);
    var porGrupo = {};
    resultados.forEach(function (item) {
      (porGrupo[item.grupo] = porGrupo[item.grupo] || []).push(item);
    });

    var html = '';
    var n = 0;
    Object.keys(indice.grupos).forEach(function (grupo) {
      var lista = porGrupo[grupo];
      if (!lista) return;
      var g = indice.grupos[grupo];
      html += '<div class="resultado-grupo" role="group" aria-label="' + esc(g.titulo) + '">'
        + '<p class="resultado-grupo__titulo" aria-hidden="true">' + esc(g.titulo) + '</p>';
      lista.slice(0, maxPorGrupo).forEach(function (item) {
        html += '<a class="resultado" role="option" id="resultado-' + n + '" href="' + esc(item.url) + '"'
          + (item.externo ? ' target="_blank" rel="noopener"' : '') + ' aria-selected="false">'
          + '<span class="resultado__icono">' + g.icono + '</span>'
          + '<span class="resultado__texto"><strong>' + resaltar(item.titulo, consulta) + '</strong>'
          + '<span>' + esc(item.detalle) + '</span></span>'
          + (item.externo ? '<span class="resultado__externo">U-Campus ↗</span>' : '')
          + '</a>';
        n++;
      });
      html += '</div>';
    });

    if (!n && normalizar(consulta)) {
      html = '<p class="resultado-vacio">No encontramos «' + esc(consulta) + '». Prueba con el código del ramo (MA1001), '
        + 'el nombre de la sala (B12) o una palabra como «solemne».</p>';
    }
    contenedor.innerHTML = html;
    return n;
  }

  window.cepcBuscar = { buscar: buscar, pintar: pintar, normalizar: normalizar };

  /* ---------------------------------------------------------------------
     Ventana tipo Spotlight
     --------------------------------------------------------------------- */

  var ventana = document.querySelector('[data-buscador]');
  if (!ventana) return;

  var entrada = ventana.querySelector('[data-buscador-entrada]');
  var lista = ventana.querySelector('[data-buscador-resultados]');
  var ayuda = ventana.querySelector('[data-buscador-ayuda]');
  var estado = ventana.querySelector('[data-buscador-estado]');
  var anteriorFoco = null;
  var activo = -1;

  function opciones() { return lista.querySelectorAll('.resultado'); }

  function marcar(i) {
    var ops = opciones();
    if (!ops.length) { activo = -1; entrada.removeAttribute('aria-activedescendant'); return; }
    activo = (i + ops.length) % ops.length;
    ops.forEach(function (o, j) { o.setAttribute('aria-selected', String(j === activo)); });
    entrada.setAttribute('aria-activedescendant', ops[activo].id);
    ops[activo].scrollIntoView({ block: 'nearest' });
  }

  function actualizar() {
    var consulta = entrada.value;
    var total = pintar(lista, consulta, MAX_POR_GRUPO);
    var hayTexto = normalizar(consulta) !== '';
    ayuda.hidden = hayTexto;
    entrada.setAttribute('aria-expanded', String(total > 0));
    estado.textContent = hayTexto ? (total ? total + ' resultados' : 'Sin resultados') : '';
    marcar(0);
  }

  function abrir() {
    if (!ventana.hidden) return;
    anteriorFoco = document.activeElement;
    ventana.hidden = false;
    document.documentElement.classList.add('buscador-abierto');
    entrada.value = '';
    actualizar();
    requestAnimationFrame(function () { entrada.focus(); });
  }

  function cerrar() {
    if (ventana.hidden) return;
    ventana.hidden = true;
    document.documentElement.classList.remove('buscador-abierto');
    if (anteriorFoco && anteriorFoco.focus) anteriorFoco.focus();
  }

  document.addEventListener('click', function (evento) {
    var abridor = evento.target.closest('[data-buscador-abrir]');
    if (abridor) { evento.preventDefault(); abrir(); return; }
    if (evento.target.closest('[data-buscador-cerrar]')) { cerrar(); return; }
    var sugerencia = evento.target.closest('[data-sugerencia]');
    if (sugerencia) { entrada.value = sugerencia.getAttribute('data-sugerencia'); actualizar(); entrada.focus(); }
    if (evento.target.closest('.resultado')) cerrar();
  });

  document.addEventListener('keydown', function (evento) {
    var escribiendo = /^(INPUT|TEXTAREA|SELECT)$/.test(document.activeElement.tagName) || document.activeElement.isContentEditable;
    if ((evento.key === 'k' || evento.key === 'K') && (evento.metaKey || evento.ctrlKey)) {
      evento.preventDefault();
      ventana.hidden ? abrir() : cerrar();
    } else if (evento.key === '/' && !escribiendo && ventana.hidden) {
      evento.preventDefault();
      abrir();
    } else if (!ventana.hidden && evento.key === 'Escape') {
      cerrar();
    }
  });

  entrada.addEventListener('input', actualizar);
  entrada.addEventListener('keydown', function (evento) {
    if (evento.key === 'ArrowDown') { evento.preventDefault(); marcar(activo + 1); }
    else if (evento.key === 'ArrowUp') { evento.preventDefault(); marcar(activo - 1); }
    else if (evento.key === 'Enter') {
      var ops = opciones();
      if (activo >= 0 && ops[activo]) { evento.preventDefault(); ops[activo].click(); }
    }
  });

  // Que Tab no se escape de la ventana mientras está abierta.
  ventana.addEventListener('keydown', function (evento) {
    if (evento.key !== 'Tab') return;
    var focos = ventana.querySelectorAll('input, button:not([hidden]), a[href]');
    var visibles = Array.prototype.filter.call(focos, function (el) { return el.offsetParent !== null; });
    if (!visibles.length) return;
    var primero = visibles[0], ultimo = visibles[visibles.length - 1];
    if (evento.shiftKey && document.activeElement === primero) { evento.preventDefault(); ultimo.focus(); }
    else if (!evento.shiftKey && document.activeElement === ultimo) { evento.preventDefault(); primero.focus(); }
  });

  /* ---------------------------------------------------------------------
     Página buscar: resultados completos a partir de ?q=
     --------------------------------------------------------------------- */

  var pagina = document.querySelector('[data-busqueda-pagina]');
  if (pagina) {
    var campo = pagina.querySelector('input[name="q"]');
    var destino = pagina.querySelector('[data-busqueda-resultados]');
    var refrescar = function () { pintar(destino, campo.value, 50); };
    var q = new URLSearchParams(location.search).get('q');
    if (q && !campo.value) campo.value = q;
    if (destino.getAttribute('data-servidor') !== '1' || campo.value) refrescar();
    campo.addEventListener('input', function () {
      refrescar();
      var url = new URL(location.href);
      url.searchParams.set('q', campo.value);
      history.replaceState(null, '', url);
    });
  }
})();
