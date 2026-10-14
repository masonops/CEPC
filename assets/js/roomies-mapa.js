/* CEPC · Sitio público
   roomies-mapa.js — Lo común de los mapas de Roomies (listado, aviso y
   formulario para publicar), sobre Leaflet (assets/vendor/leaflet).

   · Mapa base de OpenStreetMap; el CSS (roomies.css) lo suaviza en modo
     claro y lo invierte en modo oscuro.
   · Beauchef 850 marcado con el sello de la página.
   · Estaciones de Metro cercanas como puntos del color de su línea. */

(function () {
  'use strict';

  var MOSAICOS = 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
  var ATRIBUCION = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>';

  function escapar(texto) {
    return String(texto).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  /** Crea un mapa con el fondo que sigue al tema claro u oscuro. */
  function crear(elemento, opciones) {
    if (!window.L || !elemento) return null;
    opciones = opciones || {};
    var mapa = L.map(elemento, {
      zoomControl: opciones.zoom !== false,
      scrollWheelZoom: opciones.rueda === true,
      attributionControl: true,
      zoomSnap: 0.5
    });
    mapa.attributionControl.setPrefix(false);

    L.tileLayer(MOSAICOS, { attribution: ATRIBUCION, maxZoom: 19 }).addTo(mapa);

    // Con la rueda sólo se hace zoom tras hacer clic en el mapa (no
    // secuestra el scroll de la página).
    if (opciones.rueda !== true) {
      mapa.on('focus click', function () { mapa.scrollWheelZoom.enable(); });
      mapa.on('blur mouseout', function () { mapa.scrollWheelZoom.disable(); });
    }
    return mapa;
  }

  /** Marcador de Beauchef 850. */
  function beauchef(mapa, punto) {
    var icono = L.divIcon({
      className: 'pin-beauchef',
      html: '<span class="pin-beauchef__sello">FCFM</span><span class="pin-beauchef__nombre">Beauchef 850</span>',
      iconSize: null,
      iconAnchor: [18, 18]
    });
    return L.marker([punto.lat, punto.lng], { icon: icono, keyboard: false, zIndexOffset: 500, title: 'Beauchef 850' }).addTo(mapa);
  }

  /** Estaciones de Metro como puntos con el color de su línea. */
  function estaciones(mapa, lista, colores) {
    var grupo = L.layerGroup();
    (lista || []).forEach(function (e) {
      var color = (colores[e.lineas[0]] || ['#6e6e73'])[0];
      var marca = L.circleMarker([e.lat, e.lng], {
        radius: 5, weight: 2, color: '#ffffff', fillColor: color, fillOpacity: 1, className: 'punto-metro'
      });
      marca.bindTooltip('Metro ' + escapar(e.nombre) + ' · ' + e.lineas.map(function (l) { return 'L' + l; }).join(' '), { direction: 'top', offset: [0, -4] });
      grupo.addLayer(marca);
    });
    grupo.addTo(mapa);
    return grupo;
  }

  /** Distancia en metros (igual que roomies_metros() en PHP). */
  function metros(lat1, lng1, lat2, lng2) {
    var r = 6371000, rad = Math.PI / 180;
    var p1 = lat1 * rad, p2 = lat2 * rad;
    var a = Math.pow(Math.sin((p2 - p1) / 2), 2) + Math.cos(p1) * Math.cos(p2) * Math.pow(Math.sin((lng2 - lng1) * rad / 2), 2);
    return 2 * r * Math.asin(Math.min(1, Math.sqrt(a)));
  }

  function lineaHtml(linea, colores) {
    var c = colores[linea] || ['#6e6e73', '#ffffff'];
    return '<span class="linea-metro" style="--metro:' + c[0] + ';--metro-texto:' + c[1] + '">L' + escapar(linea) + '</span>';
  }

  window.RoomiesMapa = {
    crear: crear,
    beauchef: beauchef,
    estaciones: estaciones,
    metros: metros,
    lineaHtml: lineaHtml,
    escapar: escapar
  };
})();
