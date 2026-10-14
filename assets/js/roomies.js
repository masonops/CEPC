/* CEPC · Sitio público
   roomies.js — Tarjetas de Roomies (carrusel de fotos y «guardar») y, en
   roomies.php, los filtros, el orden y el mapa con los avisos.

   Los guardados viven sólo en este navegador (localStorage). */

(function () {
  'use strict';

  var CLAVE_GUARDADOS = 'roomies-guardados';

  /* --- Guardados -------------------------------------------------------- */

  function leerGuardados() {
    try {
      var lista = JSON.parse(localStorage.getItem(CLAVE_GUARDADOS) || '[]');
      return Array.isArray(lista) ? lista.map(String) : [];
    } catch (e) {
      return [];
    }
  }

  function escribirGuardados(lista) {
    try { localStorage.setItem(CLAVE_GUARDADOS, JSON.stringify(lista)); } catch (e) { /* sin almacenamiento */ }
  }

  function pintarGuardados() {
    var guardados = leerGuardados();
    document.querySelectorAll('[data-guardar]').forEach(function (b) {
      var si = guardados.indexOf(b.getAttribute('data-guardar')) !== -1;
      b.setAttribute('aria-pressed', String(si));
      var texto = b.querySelector('.oculto, [data-guardar-texto]');
      if (texto) texto.textContent = si ? 'Quitar de guardados' : (b.hasAttribute('data-guardar-largo') ? 'Guardar' : 'Guardar aviso');
    });
  }

  document.addEventListener('click', function (e) {
    var boton = e.target.closest('[data-guardar]');
    if (!boton) return;
    e.preventDefault();
    var id = boton.getAttribute('data-guardar');
    var lista = leerGuardados();
    var i = lista.indexOf(id);
    if (i === -1) lista.push(id); else lista.splice(i, 1);
    escribirGuardados(lista);
    pintarGuardados();
    document.dispatchEvent(new CustomEvent('roomies:guardados'));
  });
  pintarGuardados();

  /* --- Carrusel de fotos de cada tarjeta -------------------------------- */

  document.querySelectorAll('[data-carrusel]').forEach(function (c) {
    var pista = c.querySelector('[data-carrusel-pista]');
    var puntos = c.querySelectorAll('.aviso__puntos i');
    if (!pista || puntos.length < 2) return;

    function actual() {
      return Math.round(pista.scrollLeft / pista.clientWidth);
    }
    pista.addEventListener('scroll', function () {
      var n = actual();
      puntos.forEach(function (p, i) { p.classList.toggle('activo', i === n); });
      c.classList.toggle('en-primera', n === 0);
      c.classList.toggle('en-ultima', n === puntos.length - 1);
    }, { passive: true });
    c.classList.add('en-primera');

    c.querySelectorAll('[data-carrusel-paso]').forEach(function (b) {
      b.addEventListener('click', function (e) {
        e.preventDefault();
        var n = Math.max(0, Math.min(puntos.length - 1, actual() + Number(b.getAttribute('data-carrusel-paso'))));
        pista.scrollTo({ left: n * pista.clientWidth, behavior: 'smooth' });
      });
    });
  });

  /* --- Listado: filtros, orden y mapa ----------------------------------- */

  var barra = document.querySelector('[data-filtros]');
  var lista = document.querySelector('[data-lista]');
  var datosEl = document.getElementById('datos-roomies');
  if (!barra || !lista || !datosEl) return;

  var datos = JSON.parse(datosEl.textContent);
  var tarjetas = Array.prototype.slice.call(lista.querySelectorAll('[data-aviso]'));
  var cuenta = document.querySelector('[data-cuenta]');
  var sinResultados = document.querySelector('[data-sin-resultados]');
  var limpiar = document.querySelector('[data-limpiar]');
  var orden = document.querySelector('[data-orden]');
  var precio = barra.querySelector('[data-filtro-precio]');
  var precioSalida = barra.querySelector('[data-precio-salida]');
  var texto = barra.querySelector('[data-filtro-texto]');
  var precioMax = Number(precio.max);
  var formato = new Intl.NumberFormat('es-CL');

  barra.hidden = false;

  var estado = {
    texto: '', tipo: '', precio: precioMax, distancia: 0, metro: false,
    comodidades: [], incluido: false, mascotas: false, disponible: false, para: '', guardados: false
  };

  // Desplegables: sólo uno abierto, y se cierran al hacer clic afuera o con Esc.
  var desplegables = barra.querySelectorAll('[data-desplegable]');
  desplegables.forEach(function (d) {
    d.addEventListener('toggle', function () {
      if (d.open) desplegables.forEach(function (o) { if (o !== d) o.open = false; });
    });
  });
  document.addEventListener('click', function (e) {
    desplegables.forEach(function (d) { if (d.open && !d.contains(e.target)) d.open = false; });
  });
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    desplegables.forEach(function (d) {
      if (d.open) { d.open = false; d.querySelector('summary').focus(); }
    });
  });

  function normalizar(s) {
    return String(s).toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
  }

  function pasa(t) {
    var d = t.dataset;
    if (estado.tipo && d.tipo !== estado.tipo) return false;
    if (Number(d.total) > estado.precio) return false;
    if (estado.distancia && Number(d.aPie) > estado.distancia) return false;
    if (estado.metro && Number(d.metro) > 5) return false;
    var com = ' ' + d.comodidades + ' ';
    for (var i = 0; i < estado.comodidades.length; i++) {
      if (com.indexOf(' ' + estado.comodidades[i] + ' ') === -1) return false;
    }
    if (estado.incluido && d.incluidos !== '1') return false;
    if (estado.mascotas && d.mascotas === 'no') return false;
    if (estado.disponible && d.disponible !== '1') return false;
    if (estado.para === 'mujeres' && d.preferencia === 'hombres') return false;
    if (estado.para === 'hombres' && d.preferencia === 'mujeres') return false;
    if (estado.guardados && leerGuardados().indexOf(d.aviso) === -1) return false;
    if (estado.texto) {
      var palabras = normalizar(estado.texto).split(/\s+/);
      var buscable = normalizar(d.texto);
      for (var j = 0; j < palabras.length; j++) {
        if (palabras[j] && buscable.indexOf(palabras[j]) === -1) return false;
      }
    }
    return true;
  }

  function comparar(a, b) {
    var modo = orden.value;
    if (modo === 'barato') return Number(a.dataset.total) - Number(b.dataset.total);
    if (modo === 'nuevo') return a.dataset.publicado < b.dataset.publicado ? 1 : -1;
    return Number(a.dataset.aPie) - Number(b.dataset.aPie);
  }

  function hayFiltros() {
    return !!(estado.texto || estado.tipo || estado.precio < precioMax || estado.distancia || estado.metro ||
      estado.comodidades.length || estado.incluido || estado.mascotas || estado.disponible || estado.para || estado.guardados);
  }

  function aplicar() {
    var visibles = [];
    tarjetas.sort(comparar).forEach(function (t) {
      lista.appendChild(t);
      var si = pasa(t);
      t.hidden = !si;
      if (si) visibles.push(t.dataset.aviso);
    });
    cuenta.textContent = visibles.length + (visibles.length === 1 ? ' aviso' : ' avisos');
    sinResultados.hidden = visibles.length > 0;
    limpiar.hidden = !hayFiltros();

    // Resúmenes en las píldoras.
    var resumenPrecio = barra.querySelector('[data-resumen-precio]');
    resumenPrecio.classList.toggle('activa', estado.precio < precioMax);
    resumenPrecio.querySelector('span').textContent = estado.precio < precioMax ? 'Hasta $' + formato.format(estado.precio) : 'Precio';
    var resumenDist = barra.querySelector('[data-resumen-distancia]');
    resumenDist.classList.toggle('activa', !!(estado.distancia || estado.metro));
    resumenDist.querySelector('span').textContent = estado.distancia ? estado.distancia + ' min a pie' : (estado.metro ? 'Cerca del metro' : 'Distancia');
    var mas = estado.comodidades.length + (estado.incluido ? 1 : 0) + (estado.mascotas ? 1 : 0) + (estado.disponible ? 1 : 0) + (estado.para ? 1 : 0);
    var cuentaMas = barra.querySelector('[data-cuenta-mas]');
    cuentaMas.hidden = mas === 0;
    cuentaMas.textContent = mas;
    barra.querySelector('[data-resumen-mas]').classList.toggle('activa', mas > 0);

    if (mapa) mapa.mostrar(visibles);
  }

  texto.addEventListener('input', function () { estado.texto = texto.value.trim(); aplicar(); });

  barra.querySelectorAll('[data-filtro-tipo]').forEach(function (b) {
    b.addEventListener('click', function () {
      estado.tipo = b.getAttribute('data-filtro-tipo');
      barra.querySelectorAll('[data-filtro-tipo]').forEach(function (o) { o.setAttribute('aria-pressed', String(o === b)); });
      aplicar();
    });
  });

  precio.addEventListener('input', function () {
    estado.precio = Number(precio.value);
    precioSalida.textContent = '$' + formato.format(estado.precio);
    aplicar();
  });

  barra.querySelectorAll('[data-filtro-distancia]').forEach(function (r) {
    r.addEventListener('change', function () { estado.distancia = Number(r.value) || 0; aplicar(); });
  });
  barra.querySelector('[data-filtro-metro]').addEventListener('change', function (e) { estado.metro = e.target.checked; aplicar(); });

  barra.querySelectorAll('[data-filtro-comodidad]').forEach(function (c) {
    c.addEventListener('change', function () {
      estado.comodidades = Array.prototype.slice.call(barra.querySelectorAll('[data-filtro-comodidad]:checked')).map(function (x) { return x.value; });
      aplicar();
    });
  });
  barra.querySelector('[data-filtro-incluido]').addEventListener('change', function (e) { estado.incluido = e.target.checked; aplicar(); });
  barra.querySelector('[data-filtro-mascotas]').addEventListener('change', function (e) { estado.mascotas = e.target.checked; aplicar(); });
  barra.querySelector('[data-filtro-disponible]').addEventListener('change', function (e) { estado.disponible = e.target.checked; aplicar(); });
  barra.querySelectorAll('[data-filtro-para]').forEach(function (r) {
    r.addEventListener('change', function () { estado.para = r.value; aplicar(); });
  });

  var botonGuardados = barra.querySelector('[data-filtro-guardados]');
  botonGuardados.addEventListener('click', function () {
    estado.guardados = !estado.guardados;
    botonGuardados.setAttribute('aria-pressed', String(estado.guardados));
    botonGuardados.classList.toggle('activa', estado.guardados);
    aplicar();
  });
  document.addEventListener('roomies:guardados', function () { if (estado.guardados) aplicar(); });

  orden.addEventListener('change', aplicar);

  limpiar.addEventListener('click', function () {
    estado = { texto: '', tipo: '', precio: precioMax, distancia: 0, metro: false, comodidades: [], incluido: false, mascotas: false, disponible: false, para: '', guardados: false };
    texto.value = '';
    precio.value = precioMax;
    precioSalida.textContent = '$' + formato.format(precioMax);
    barra.querySelectorAll('input[type=checkbox]').forEach(function (c) { c.checked = false; });
    barra.querySelectorAll('input[type=radio]').forEach(function (r) { r.checked = r.value === ''; });
    barra.querySelectorAll('[data-filtro-tipo]').forEach(function (o) { o.setAttribute('aria-pressed', String(o.getAttribute('data-filtro-tipo') === '')); });
    botonGuardados.setAttribute('aria-pressed', 'false');
    botonGuardados.classList.remove('activa');
    aplicar();
  });

  /* --- Vista lista / mapa (teléfono) ------------------------------------ */

  var resultados = document.querySelector('[data-resultados]');
  var vista = document.querySelector('[data-vista]');
  vista.hidden = false;
  vista.querySelectorAll('[data-vista-boton]').forEach(function (b) {
    b.addEventListener('click', function () {
      var modo = b.getAttribute('data-vista-boton');
      resultados.classList.toggle('roomies-resultados--mapa', modo === 'mapa');
      vista.querySelectorAll('[data-vista-boton]').forEach(function (o) { o.setAttribute('aria-pressed', String(o === b)); });
      if (mapa) setTimeout(function () { mapa.leaflet.invalidateSize(); mapa.encuadrar(); }, 30);
    });
  });

  /* --- Mapa ------------------------------------------------------------- */

  var mapa = null;
  var lienzo = document.querySelector('[data-mapa-roomies]');
  if (lienzo && window.RoomiesMapa && window.L) {
    var M = window.RoomiesMapa;
    var leaflet = M.crear(lienzo);
    if (leaflet) {
      M.estaciones(leaflet, datos.estaciones, datos.lineas);
      M.beauchef(leaflet, datos.beauchef);

      var marcadores = {};
      datos.avisos.forEach(function (a) {
        var icono = L.divIcon({
          className: 'pin-precio',
          html: '<span>$' + formato.format(Math.round(a.precio / 1000)) + ' mil</span>',
          iconSize: null,
          iconAnchor: [34, 16]
        });
        var marca = L.marker([a.lat, a.lng], { icon: icono, title: a.titulo, riseOnHover: true });
        var metro = a.metro ? '<span class="mini-aviso__metro">' + a.metro.lineas.map(function (l) { return M.lineaHtml(l, datos.lineas); }).join('') + ' ' + M.escapar(a.metro.nombre) + '</span>' : '';
        marca.bindPopup(
          '<a class="mini-aviso" href="' + M.escapar(a.url) + '">' +
            (a.foto ? '<img src="' + M.escapar(a.foto) + '" alt="" width="240" height="160">' : '') +
            '<span class="mini-aviso__cuerpo"><strong>$' + formato.format(a.precio) + '<small>/mes</small></strong>' +
            '<span class="mini-aviso__titulo">' + M.escapar(a.titulo) + '</span>' +
            '<span class="mini-aviso__datos">' + a.a_pie + ' min a pie de Beauchef</span>' + metro + '</span></a>',
          { closeButton: false, minWidth: 240, maxWidth: 240, offset: [0, -8], className: 'popup-aviso' }
        );
        marca.on('click', function () {
          var t = lista.querySelector('[data-aviso="' + a.id + '"]');
          if (t) resaltar(String(a.id), true);
        });
        marcadores[a.id] = marca;
      });

      var capa = L.featureGroup().addTo(leaflet);

      var resaltar = function (id, encender) {
        Object.keys(marcadores).forEach(function (k) {
          var el = marcadores[k].getElement();
          if (el) el.classList.toggle('pin-precio--activo', encender && k === id);
        });
        tarjetas.forEach(function (t) { t.classList.toggle('aviso--resaltado', encender && t.dataset.aviso === id); });
      };

      tarjetas.forEach(function (t) {
        t.addEventListener('mouseenter', function () {
          resaltar(t.dataset.aviso, true);
          var m = marcadores[t.dataset.aviso];
          if (m) m.setZIndexOffset(1000);
        });
        t.addEventListener('mouseleave', function () {
          resaltar(t.dataset.aviso, false);
          var m = marcadores[t.dataset.aviso];
          if (m) m.setZIndexOffset(0);
        });
      });

      mapa = {
        leaflet: leaflet,
        visibles: [],
        mostrar: function (ids) {
          this.visibles = ids;
          capa.clearLayers();
          ids.forEach(function (id) { if (marcadores[id]) capa.addLayer(marcadores[id]); });
          this.encuadrar();
        },
        encuadrar: function () {
          var puntos = this.visibles.map(function (id) { return marcadores[id] && marcadores[id].getLatLng(); }).filter(Boolean);
          puntos.push(L.latLng(datos.beauchef.lat, datos.beauchef.lng));
          leaflet.fitBounds(L.latLngBounds(puntos).pad(0.18), { maxZoom: 16, animate: false });
        }
      };
    }
  }

  aplicar();
})();
