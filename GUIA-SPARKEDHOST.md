# Guía: subir el sitio y el panel a SparkedHost

Paso a paso para dejar el sitio público (este repositorio) en la raíz del
dominio y el panel de control en `/panel/`, en un plan de **Web Hosting de
SparkedHost** (usa cPanel).

Tiempo estimado: 45 minutos la primera vez.

> **¿Usas subdominios?** (por ejemplo `cepc.midominio.tech` para el sitio y
> `panel.midominio.tech` para el panel, cada uno con su carpeta en
> `/home/usuario/`). También funciona: sigue la guía igual, pero lee los
> recuadros «Con subdominios». El sitio encuentra solo la carpeta
> `panel.*` que está al lado y enlaza a `https://panel.midominio.tech/`.

```
public_html/                 ← este repositorio (sitio público)
├── index.php, roomies.php, …
├── config.php               ← lo editas en el paso 5
├── uploads/roomies/         ← fotos de Roomies (debe poder escribirse)
└── panel/                   ← el repositorio cepc-panel
    └── .env                 ← lo creas en el paso 4
```

---

## 0. Antes de empezar

Necesitas:

- El plan de Web Hosting de SparkedHost con un **dominio** apuntando a él
  (si compraste el dominio afuera, cambia sus DNS a los que te da
  SparkedHost en el correo de bienvenida; tarda hasta 24 h).
- Un ZIP de este repositorio (GitHub → *Code* → *Download ZIP*) y otro del
  panel (`cepc-panel`).
- Tu correo `@ug.uchile.cl` para probar Roomies al final.

Entra a cPanel desde el panel de SparkedHost: en tu servicio de Web Hosting,
botón **Login to cPanel**.

---

## 1. Versión de PHP y límites

1. cPanel → **MultiPHP Manager** (o *Select PHP Version*): elige **PHP 8.2**
   o más nuevo para el dominio.

   > **Con subdominios:** cada subdominio tiene **su propia versión de PHP**
   > y los nuevos parten con la del sistema (que puede ser PHP 7). Marca la
   > casilla del sitio **y** la del panel y aplica PHP 8.2 a los dos. Si el
   > panel queda en PHP 7, muestra un error 500 en blanco.
2. Si aparece *Select PHP Version → Extensions*, revisa que estén marcadas:
   `pdo_mysql`, `mbstring`, `gd`, `exif`, `fileinfo`, `intl` (opcional).
   Si no aparece esa pantalla, escribe a soporte de SparkedHost y pídeles
   que las activen (suelen venir activas).
3. cPanel → **MultiPHP INI Editor** → elige el dominio y ajusta:

   | Ajuste | Valor |
   |---|---|
   | `upload_max_filesize` | `12M` |
   | `post_max_size` | `100M` |
   | `max_file_uploads` | `20` |
   | `memory_limit` | `256M` |

   (Roomies achica las fotos en el navegador antes de subirlas, pero así
   también funciona con fotos grandes desde navegadores antiguos.)

---

## 2. Base de datos

1. cPanel → **Manage My Databases** (o *MySQL Databases*):
   - Crea una base, por ejemplo `cepc` (quedará `usuario_cepc`).
   - Crea un usuario con una contraseña larga (usa el generador).
   - En *Add User To Database*, agrega el usuario a la base con
     **ALL PRIVILEGES**.
   - Anota: nombre de la base, usuario y contraseña. El host es `localhost`.
2. cPanel → **phpMyAdmin** → elige la base a la izquierda → **Importar**, y
   sube en este orden:
   1. `panel/sql/schema_mysql.sql` (del repositorio del panel).
   2. `sql/articulos.sql` (de este repositorio).
   3. `sql/roomies.sql` (de este repositorio).

   Cada importación debe terminar con «La importación se ejecutó
   exitosamente».

---

## 3. Subir los archivos

1. cPanel → **File Manager** → entra a `public_html/`. Si hay un
   `index.html` de bienvenida de SparkedHost, bórralo.
2. **Sitio público:** botón *Upload*, sube el ZIP de este repositorio.
   Vuelve, clic derecho sobre el ZIP → *Extract*. Quedará una carpeta (por
   ejemplo `CEPC-main/`): entra, selecciona todo (*Select All*, incluidos los
   archivos ocultos como `.htaccess`; activa *Settings → Show Hidden Files*)
   y muévelo (*Move*) a `/public_html`. Borra la carpeta vacía y el ZIP.
3. **Panel:** crea la carpeta `public_html/panel/`, sube ahí el ZIP del panel
   y haz lo mismo (extraer y mover el contenido a `public_html/panel/`).
4. No hacen falta en el servidor (puedes borrarlas): `.github/`,
   `herramientas/`, `referencias/`, `demo/`. Si quedan, el `.htaccess` las
   bloquea igual.

---

## 4. Configurar el panel (`panel/.env`)

En File Manager, dentro de `public_html/panel/`, copia `.env.example` como
`.env` (clic derecho → *Copy*, nombre `.env`) y edítalo (*Edit*):

> **Con subdominios:** el `.env` va en la carpeta del panel
> (`/home/usuario/panel.midominio.tech/.env`) y `APP_URL` es
> `https://panel.midominio.tech`.

```ini
APP_ENTORNO=produccion
APP_DEBUG=false
APP_URL=https://tudominio.cl/panel
APP_CLAVE=<una cadena larga y aleatoria, 40+ letras y números>

DB_DRIVER=mysql
DB_HOST=localhost
DB_PUERTO=3306
DB_NOMBRE=<usuario_cepc>
DB_USUARIO=<usuario_de_la_base>
DB_PASSWORD=<la contraseña>
```

Luego abre `https://tudominio.cl/panel/instalar.php` **una vez** (sin
«datos de ejemplo»), crea las cuentas y **borra `instalar.php`** al
terminar. Cada integrante entra y cambia su contraseña en *Mi perfil*.

---

## 5. Configurar el sitio (`config.php`)

Edita `public_html/config.php`:

| Clave | Qué poner |
|---|---|
| `modo` | `'lista'` durante la campaña; `'cepc'` si ganan |
| `instagram` | La cuenta de la lista, sin @ |
| `correo` | El correo de la lista (aparece en el sitio y recibe los reportes) |
| `url_sitio` | `https://tudominio.cl` (sin `/` al final). Se usa en los enlaces de los correos de Roomies |
| `roomies.remitente` | Una casilla de tu dominio, ej. `roomies@tudominio.cl` (ver paso 6) |
| `roomies.correo_reportes` | Quién recibe los reportes de avisos (vacío = `correo`) |
| `roomies.moderar` | `true` si quieren aprobar cada aviso antes de que se vea |
| `banco_apuntes_url` | Enlace a la carpeta de apuntes y tareas |

El sitio encuentra el panel solo en `./panel` y muestra «Acceso equipo» en
el pie.

**Permisos:** la carpeta `public_html/uploads/roomies/` debe poder
escribirse. En File Manager, clic derecho → *Change Permissions* → `755`
(en cPanel PHP corre con tu usuario, así que basta). Si al publicar sale
«carpeta uploads sin permisos», prueba `775`.

---

## 6. Correo (Roomies manda correos)

Roomies envía el enlace de confirmación y los mensajes de los interesados
con la función `mail()` de PHP. Para que no lleguen a spam:

1. cPanel → **Email Accounts** → *Create*: crea `roomies@tudominio.cl` (la
   que pusiste en `roomies.remitente`). No hace falta revisarla, pero puedes.
2. cPanel → **Email Deliverability**: el dominio debe tener **SPF** y
   **DKIM** en verde. Si dice «Problems Exist», usa *Repair* (si el DNS está
   en SparkedHost se arregla solo; si está en otro lado, copia los registros
   que te muestra).
3. Haz la prueba del paso 8. Si el correo no llega a `@ug.uchile.cl`,
   revisa *Spam* y la carpeta *Correo no deseado* de Outlook (la
   Universidad usa Microsoft 365).

---

## 7. HTTPS

1. cPanel → **SSL/TLS Status** → *Run AutoSSL*. Espera a que el dominio
   quede con candado verde (con subdominios, **cada uno** debe aparecer con
   candado; si uno sale con certificado «self-signed», vuelve a correr
   AutoSSL cuando su DNS ya apunte al servidor).
2. En `public_html/.htaccess` y en `public_html/panel/.htaccess`,
   descomenta el bloque «Forzar HTTPS» (borra los `#` de esas 4 líneas).

---

## 8. Probar que todo funciona

- [ ] `https://tudominio.cl` muestra la portada sin la franja «Vista previa».
- [ ] `https://tudominio.cl/panel/` pide usuario y contraseña.
- [ ] Un evento creado en el panel (*Calendario*) aparece en la agenda y en
      el calendario del sitio.
- [ ] **Roomies:** publica un aviso de prueba con tu correo `@ug.uchile.cl`
      y dos fotos → te llega el correo → al abrir el enlace queda publicado
      → desde otro correo de la U, escríbele por el formulario y revisa que
      llegue → con el enlace de administración, bórralo.
- [ ] `https://tudominio.cl/config.php` y `https://tudominio.cl/sql/roomies.sql`
      dan error (están bloqueados).
- [ ] En el teléfono se ve bien y el modo oscuro funciona.

---

## 9. Para actualizar más adelante

- Sube sólo los archivos que cambiaron (File Manager o FTP).
- **Nunca sobrescribas** `config.php`, `panel/.env` ni la carpeta
  `uploads/` (ahí están las fotos de los avisos).
- Si una actualización trae un `.sql` nuevo, impórtalo en phpMyAdmin.
- Respaldo: cPanel → **Backup** → descarga la base (*Download a MySQL
  Database Backup*) y la carpeta `uploads/` de vez en cuando.

---

## Si algo falla

| Síntoma | Qué revisar |
|---|---|
| Página en blanco o «500» | Abre `diagnostico.php` del panel: dice el error exacto. Si dice `syntax error, unexpected ','` (sin la palabra *token*), ese dominio corre **PHP 7**: súbelo a 8.2 en MultiPHP Manager (paso 1). También sirve cPanel → *Errors* |
| Renombraste `.htaccess` para probar | **Devuélvele el nombre al tiro**: sin él, cualquiera puede descargar `.env` (con la contraseña de la base). Si estuvo expuesto, cambia la contraseña del usuario MySQL y `APP_CLAVE` |
| El sitio funciona pero sin datos | Que exista `public_html/panel/config/database.php` y que el `.env` del panel tenga bien la base |
| «Roomies todavía no recibe avisos» | Falta importar `sql/roomies.sql` (paso 2) |
| No llega el correo de confirmación | Paso 6; revisa spam; prueba con `roomies.remitente` igual a una casilla que exista |
| «carpeta uploads sin permisos» | Paso 5, permisos de `uploads/roomies/` |
| Las fotos no suben | Paso 1, `upload_max_filesize` y `post_max_size` |
| El mapa de Roomies sale gris | El navegador bloquea `tile.openstreetmap.org` (raro); revisa extensiones de bloqueo |
