# Sitio público · CEPC

La página que ven los estudiantes de Plan Común. Es informativa y tranquila:
un banner con lo más importante del momento, las próximas fechas, los
recursos de ayuda, lo último de Instagram, quiénes somos y preguntas
frecuentes.

PHP 8 sin frameworks, igual que el panel. Modo claro por defecto y botón
para modo oscuro (se recuerda en el navegador de cada persona).

---

## 1. Probarlo en tu computador

El sitio lee los datos del panel, así que primero hay que tener el panel
instalado (ver `../cepc-panel/README.md`). Luego:

```bash
cd cepc-web
php -S localhost:8100
```

Abre <http://localhost:8100>. El sitio encuentra solo el panel en
`../cepc-panel` y usa su misma base de datos (MySQL o SQLite).

Si el panel no está o la base no responde, la página igual se muestra: el
banner pasa a ser una bienvenida y cada sección muestra un aviso amable en
vez de datos.

---

## 2. Subirlo a SparkedHost

La idea es que el sitio quede en la raíz del dominio y el panel en `/panel/`:

```
public_html/
├── index.php, ics.php, config.php, assets/, includes/   ← esta carpeta
└── panel/                                               ← cepc-panel
```

1. Sube el contenido de `cepc-web/` a `public_html/`.
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
| Cambiar colores, tipografía o espacios | `assets/css/sitio.css` (tokens al inicio) |
| Cambiar la foto del banner | `assets/img/fondo-beauchef.jpg` (se difumina sola) |
| Agregar un ícono | Descargar el SVG de [Phosphor](https://phosphoricons.com) (estilo *regular*) a `assets/iconos/` y usar `icono('nombre')` |

---

## 5. Diseño

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
- **Vidrio esmerilado.** Es una aproximación con `backdrop-filter`; si el
  sistema pide «Reducir transparencia», se usan fondos sólidos.

---

## 6. Créditos

- Foto de Beauchef 851: [Carlos yo](https://commons.wikimedia.org/wiki/File:Beauchef_851,_FCFM_Universidad_de_Chile,_Santiago_20231215.jpg),
  licencia [CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0/deed.es),
  vía Wikimedia Commons. Las versiones recortadas en `assets/img/`
  (`fondo-beauchef.jpg`, `beauchef-851.jpg`) quedan bajo la misma licencia.
  El crédito aparece en el pie de la página: si cambian la foto por una
  propia, pueden quitarlo.
- Íconos: [Phosphor Icons](https://phosphoricons.com), licencia MIT
  (`assets/iconos/LICENCIA.txt`).
- Tipografía Inter: SIL Open Font License (`assets/fuentes/LICENCIA-Inter.txt`).
