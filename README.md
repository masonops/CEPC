# Sitio público · CEPC

El sitio que ven los estudiantes de Plan Común. Informativo y tranquilo,
con estas páginas:

| Página | Qué tiene |
|---|---|
| `index.php` | Portada: banner con lo más importante, próximas fechas, recursos, novedades, cuentas claras, quiénes somos y preguntas frecuentes |
| `calendario.php` | Mes completo con solemnes, controles, entregas, actividades y asambleas; filtros y detalle por día |
| `mapa.php` | Mapa interactivo de Beauchef 850 y 851: edificios, pisos y salas, rutas desde la entrada y dónde comer |
| `calculadora.php` | Cuánto necesitas en lo que queda para aprobar (4,0) o ir a segunda (3,7) |
| `transparencia.php` | Rendición de cuentas, proyectos, asambleas, votaciones y documentos |
| `buscar.php` | Resultados del buscador (la barra tiene un buscador tipo Spotlight: tecla `/` o Ctrl/⌘ + K) |

PHP 8 sin frameworks, igual que el panel. Modo claro por defecto y botón
para modo oscuro (se recuerda en el navegador de cada persona).

---

## Cómo está ordenado

**Cada sección es un archivo aparte**, para poder cambiar una sin tocar las
demás:

```
index.php, calendario.php, …        cada página sólo junta sus secciones
includes/secciones/inicio/          una sección de la portada por archivo
includes/secciones/calendario/      (mes.php, lista.php)
includes/secciones/mapa/            (lienzo.php, panel.php)
includes/secciones/calculadora/     (calculadora.php, explicacion.php)
includes/secciones/transparencia/   (resumen, movimientos, proyectos, asambleas, votaciones, documentos)
includes/partes/                    encabezado, pie, buscador y título de página
includes/mapa/                      datos del mapa (ver más abajo)
assets/css/base.css                 colores, tipografía, botones, barra y pie
assets/css/inicio/*.css             un estilo por sección de la portada
assets/css/<página>.css             el estilo de cada página
assets/js/<página>.js               la interacción de cada página
```

Para cambiar el orden de la portada o quitar una sección, edita la lista
`$secciones` de `index.php`.

---

## 1. Probarlo en tu computador

El sitio lee los datos del panel, así que lo ideal es tener el panel
instalado (ver el README del panel) en una carpeta `cepc-panel` junto a
ésta, o indicar dónde está en `config.php` → `panel_ruta`. Luego:

```bash
php -S localhost:8100
```

Abre <http://localhost:8100>. El sitio usa la misma base de datos del panel
(MySQL o SQLite).

Si el panel no está o la base no responde, la página igual se muestra: el
banner pasa a ser una bienvenida y cada sección muestra un aviso amable en
vez de datos.

Para ver la versión de GitHub Pages en tu computador:

```bash
php herramientas/construir-estatico.php
cd _site && php -S localhost:8200
```

---

## Vista previa en GitHub Pages

GitHub Pages no ejecuta PHP ni puede leer la base del panel. Por eso el
flujo `.github/workflows/pages.yml` arma una **versión estática con datos de
ejemplo** (`demo/ejemplo.php`) y la publica. Arriba aparece una franja que
dice «Vista previa con datos de ejemplo», para que nadie confunda esas
fechas con las reales.

- Se publica sola en cada `push` a `main` y una vez al día (00:17 en
  Santiago), así «Hoy», «Mañana» y «En 3 días» siempre están bien.
- También se puede lanzar a mano: *Actions → Publicar en GitHub Pages →
  Run workflow*.
- Los botones «Agregar al calendario» descargan archivos `.ics` generados
  durante la construcción.

**Activarla (una sola vez):** *Settings → Pages → Build and deployment →
Source: GitHub Actions*. Si el repositorio es privado, GitHub Pages pide un
plan pagado; con el repositorio público funciona gratis (aquí no hay datos
del panel ni contraseñas).

El sitio de verdad, con los datos del panel, es el que se sube a
SparkedHost (sección siguiente).

---

## 2. Subirlo a SparkedHost

La idea es que el sitio quede en la raíz del dominio y el panel en `/panel/`:

```
public_html/
├── index.php, ics.php, config.php, assets/, includes/   ← esta carpeta
└── panel/                                               ← cepc-panel
```

1. Sube el contenido de este repositorio a `public_html/` (las carpetas
   `.github/`, `demo/`, `herramientas/` y `referencias/` no hacen falta en
   el servidor; si las subes, el `.htaccess` las bloquea).
2. Sube `cepc-panel/` a `public_html/panel/` (con su `.env`, como dice su README).
3. Listo: el sitio detecta `./panel` solo y muestra el enlace «Acceso
   directiva» en el pie.

Si el panel vive en otra carpeta, indícala en `config.php` → `panel_ruta`
(y `panel_url` para el enlace).

---

## 3. De dónde sale cada cosa

| Sección | Se edita en | Qué se publica |
|---|---|---|
| **Banner** | Automático, o `includes/contenido.php` → `contenido_destacado_manual()` | Ver abajo |
| **Próximas fechas** | Panel → *Calendario* | Solemnes, controles, entregas, asambleas, actividades, sunsets, marchas y paros. **Nunca** las reuniones de directiva ni los plazos internos. |
| **Apuntes** | Panel → *Docencia* | Sólo cuenta el material marcado como verificado. |
| **Tutorías** | Panel → *Docencia → Tutorías* | Las abiertas y futuras, con cupos libres. Sin datos de contacto del tutor. |
| **Novedades** | Panel → *Comunicaciones* | Publicaciones de Instagram en estado «Publicado» con fecha de hoy o anterior. |
| **Directiva** | Panel → *Mi perfil* | Nombre, cargo y foto. Nada de correos ni teléfonos. |
| **Calendario y buscador** | Panel → *Calendario* | Los mismos tipos públicos que la agenda. |
| **Transparencia** | Panel → *Finanzas* y *Secretaría* | Movimientos (sin quién los registró), proyectos con su gasto, asambleas con asistencia y acta, y votaciones **cerradas**. Nunca los reembolsos a personas. |
| **Textos fijos y preguntas** | `includes/contenido.php` | |
| **Instagram y correo** | Tabla `configuracion` del panel, o `config.php` | |

### Cómo se elige el banner

1. Si hay un **destacado manual** en `includes/contenido.php`, ése.
2. Si no, el **evento más importante de las próximas 3 semanas**: asambleas,
   marchas y paros pesan más que actividades y sunsets, y éstos más que una
   solemne. A igual peso, el más próximo.
3. Si no hay ninguno, la **última publicación de Instagram** si tiene menos
   de 10 días.
4. Si tampoco, el próximo evento aunque esté lejos.
5. Si no hay nada, una bienvenida.

Los plazos (21 y 10 días) se cambian en `config.php`.

Cuando el banner es un evento, el botón **Agregar al calendario** descarga un
`.ics` (`ics.php?id=N`) que abre el calendario del teléfono. Cada tarjeta de
la agenda tiene el mismo botón.

---

## 4. Dónde tocar cada cosa

| Quiero… | Archivo |
|---|---|
| Cambiar correo, Instagram, dirección o el enlace al banco de apuntes | `config.php` |
| Fijar una noticia en el banner | `includes/contenido.php` → `contenido_destacado_manual()` |
| Cambiar las preguntas frecuentes o los textos de las tarjetas | `includes/contenido.php` |
| Cambiar qué tipos de evento son públicos o cuánto pesan | `includes/datos.php` → `TIPOS_PUBLICOS` |
| Cambiar colores, tipografía o espacios | `assets/css/base.css` (tokens al inicio) |
| Cambiar una sección de la portada | `includes/secciones/inicio/<sección>.php` y `assets/css/inicio/<sección>.css` |
| Agregar algo al buscador | `includes/busqueda.php` (una función `busqueda_*()` nueva) |
| Cambiar dónde lleva un ramo (U-Campus) | `config.php` → `ucampus_ramo_url` |
| Cambiar las plantillas de la calculadora | `includes/secciones/calculadora/calculadora.php` → `$plantillasNotas` |
| Subir estatutos, reglamentos o actas | `includes/contenido.php` → `contenido_documentos()` |
| Cargar salas y pisos reales | `includes/mapa/edificios.php` |
| Cargar lugares para comer | `includes/mapa/lugares.php` |
| Agregar un camino para las rutas | `includes/mapa/rutas.php` |
| Cambiar la foto del banner | `assets/img/fondo-beauchef.jpg` (se difumina sola) |
| Cambiar los datos de ejemplo de GitHub Pages | `demo/ejemplo.php` |
| Agregar un ícono | Descargar el SVG de [Phosphor](https://phosphoricons.com) (estilo *regular*) a `assets/iconos/` y usar `icono('nombre')` |

---

## 5. El mapa

Los datos del mapa están en `includes/mapa/`, separados para que sea fácil
completarlos:

| Archivo | Qué tiene | ¿Se edita a mano? |
|---|---|---|
| `geometria.php` | La forma de cada edificio, calcada del plano | No: la genera `herramientas/trazar-mapa.py` |
| `edificios.php` | Nombre, descripción, servicios, pisos y salas | **Sí** |
| `rutas.php` | Red de caminos (nodos y uniones) para calcular rutas | Sí, para sumar atajos |
| `lugares.php` | Dónde comer: posición, etiquetas (vegano, Junaeb…) y horario | **Sí** |

- **Orientación:** norte arriba. Beauchef 851 a la izquierda y 850 a la
  derecha (el plano original de `referencias/mapas/` estaba girado; el script
  lo gira 90° a la derecha).
- **Coordenadas:** abre `mapa.php?coordenadas=1` para ver una cuadrícula y
  la posición del mouse; así se ubican lugares y nodos nuevos.
- **Pendiente:** las **salas y pisos son de ejemplo** (el plano no trae
  subterráneos ni pisos altos) y los **lugares de afuera** también. Mientras
  `MAPA_SALAS_DE_EJEMPLO` sea `true` y los lugares tengan `'ejemplo' => true`,
  el mapa lo avisa.
- **Distancias:** se calculan con una escala estimada (`metros_por_unidad`
  en `rutas.php`); por eso dicen «≈».

---

## 6. Diseño

- **Colores del logo.** El azul `#004AAD` es el único color de acción
  (botones y enlaces). El lila `#B88ED6` es decorativo: etiquetas de tipo de
  evento, la tarjeta de apuntes y los avatares. En modo oscuro se usan
  versiones más luminosas de ambos para que se lean bien.
- **Tipografía.** La del sistema de Apple (San Francisco) en iPhone y Mac;
  en Windows y Android se usa Inter, incluida en `assets/fuentes/`.
- **Formas.** Botones en píldora; tarjetas grandes y fotos con radio 28px;
  tarjetas de la agenda 22px; piezas internas 14px.
- **Movimiento.** Entrada suave del banner y aparición de secciones al bajar.
  Todo se apaga si el sistema tiene activado «Reducir movimiento».
- **Gráficos y calendario.** Los colores de las barras y de los grupos del
  calendario se validaron para daltonismo y contraste en ambos modos. El
  color nunca va solo: siempre lo acompaña texto o un ícono.
- **Vidrio esmerilado.** Es una aproximación con `backdrop-filter`; si el
  sistema pide «Reducir transparencia», se usan fondos sólidos.

---

## 7. Créditos

- Foto de Beauchef 851: [Carlos yo](https://commons.wikimedia.org/wiki/File:Beauchef_851,_FCFM_Universidad_de_Chile,_Santiago_20231215.jpg),
  licencia [CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0/deed.es),
  vía Wikimedia Commons. Las versiones recortadas en `assets/img/`
  (`fondo-beauchef.jpg`, `beauchef-851.jpg`) quedan bajo la misma licencia.
  El crédito aparece en el pie de la página: si cambian la foto por una
  propia, pueden quitarlo.
- Íconos: [Phosphor Icons](https://phosphoricons.com), licencia MIT
  (`assets/iconos/LICENCIA.txt`).
- Tipografía Inter: SIL Open Font License (`assets/fuentes/LICENCIA-Inter.txt`).
