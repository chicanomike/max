# MAX | Solicitudes de asesoría

Sitio web en PHP 8 para recibir solicitudes desde el formulario existente, guardarlas en MySQL y notificar a un número configurado mediante WhatsApp Business Cloud API. Incluye panel administrativo protegido, búsqueda, filtros, estados y reintento de notificaciones.

## Requisitos

- XAMPP con PHP 8+, Apache `mod_rewrite`, PDO MySQL y cURL habilitados.
- MySQL/MariaDB iniciado desde el panel de control de XAMPP.
- Una cuenta de Meta Business configurada para WhatsApp Cloud API para habilitar notificaciones.

## Instalación en XAMPP

1. Coloca el proyecto en `C:\xampp\htdocs\max` e inicia Apache y MySQL en XAMPP.
2. En phpMyAdmin, importa `database/schema.sql`. Esto crea `sistema_web` y la tabla `solicitudes`.
3. Copia `.env.example` como `.env` en la raíz del proyecto. No subas `.env` al repositorio.
4. Genera el hash para la contraseña administrativa desde PowerShell:

   ```powershell
   C:\xampp\php\php.exe -r "echo password_hash('CAMBIA_ESTA_CLAVE', PASSWORD_DEFAULT), PHP_EOL;"
   ```

   Sustituye `CAMBIA_ESTA_CLAVE` por una contraseña propia y pega el resultado en `ADMIN_PASSWORD_HASH`. Ajusta también `ADMIN_EMAIL`.
5. Genera un secreto JWT aleatorio si usarás el endpoint de autenticación heredado:

   ```powershell
   C:\xampp\php\php.exe -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
   ```

   Guarda el resultado en `JWT_SECRET`. El panel administrativo usa una sesión independiente.
6. Ajusta `DB_HOST`, `DB_NAME`, `DB_USER` y `DB_PASS` en `.env` según tu MySQL local. En XAMPP nuevo, la cuenta `root` suele tener contraseña vacía; configura una contraseña en MySQL para cualquier entorno compartido o publicado.
7. Abre `http://localhost/max/`. El panel está en `http://localhost/max/admin`.

El sitio usa `/MAX` en las reglas del `.htaccess` raíz. Si cambias el nombre de la carpeta, actualiza esas reglas.

## Configurar WhatsApp Cloud API

No se incluyen tokens ni identificadores reales. La integración queda pendiente hasta completar los valores `WHATSAPP_*` en el archivo `.env`:

1. En Meta for Developers crea o selecciona una aplicación de tipo Business, añade el producto WhatsApp y vincula una cuenta de WhatsApp Business y un número habilitado.
2. En la configuración de la API copia el **Phone Number ID** a `WHATSAPP_PHONE_NUMBER_ID`. Crea un token de acceso de usuario del sistema con los permisos necesarios, incluido `whatsapp_business_messaging`, y guárdalo en `WHATSAPP_ACCESS_TOKEN`. No lo pongas en JavaScript, HTML ni en el repositorio.
3. Escribe en `WHATSAPP_RECIPIENT` el número que recibirá las alertas en formato internacional, sin `+` ni espacios. Para el número indicado `9611505926`, el valor es `529611505926` (código de México `52` más los 10 dígitos).
4. En WhatsApp Manager crea una plantilla aprobada de tipo **Utility**, nombre `solicitud_recibida`, idioma `es_MX`, con siete variables de cuerpo en este orden:

   ```text
   NUEVA SOLICITUD RECIBIDA

   Número de solicitud: {{1}}
   Nombre: {{2}}
   Teléfono: {{3}}
   Correo electrónico: {{4}}
   Servicio solicitado: {{5}}
   Descripción: {{6}}
   Fecha y hora: {{7}}
   ```

   Envía ejemplos de valores de muestra al solicitar la aprobación de la plantilla. Si eliges otro nombre o idioma en Meta, actualiza `WHATSAPP_TEMPLATE_NAME` o `WHATSAPP_TEMPLATE_LANGUAGE` para que coincidan exactamente.
5. Define `WHATSAPP_API_VERSION` con una versión Graph API activa que esté admitida por tu aplicación. Sustituye el ejemplo `vXX.0`; mientras siga así la integración no enviará mensajes.
6. En modo de pruebas, agrega el destinatario a la lista de números permitidos y usa las credenciales de prueba proporcionadas por Meta. Comprueba el envío antes de cambiar a producción.

El formulario web inicia una notificación de empresa a cliente; por ello se usa siempre una plantilla aprobada y no un mensaje libre. La API debe aceptar el envío y devolver un identificador para que el registro pase a `enviado`; esto confirma la aceptación por Graph API, no una confirmación de entrega al dispositivo. Si falla, la solicitud queda guardada con el error y puede reintentarse en el panel.

## Uso del panel

Entra en `/admin` con `ADMIN_EMAIL` y la contraseña cuyo hash configuraste. El panel permite buscar por nombre/teléfono, filtrar por servicio y fechas, revisar los datos, actualizar estado, reintentar WhatsApp y eliminar una solicitud tras confirmación. Las operaciones de escritura requieren sesión administrativa y token CSRF.

## Estructura relevante

- `app/Config.php`: carga `.env` y variables del proceso.
- `app/Database.php`: conexión PDO MySQL.
- `app/SubmissionRepository.php`: consultas preparadas para solicitudes.
- `app/SubmissionController.php`: validación, deduplicación y registro.
- `app/WhatsAppCloudApi.php`: envío de plantillas por Cloud API.
- `app/AdminController.php`, `app/Session.php`: panel y protección de sesión.
- `database/schema.sql`: base de datos y tabla.
- `public/index.php`: página existente, formulario y front controller.
- `public/.htaccess`: reescritura de rutas al front controller.

## Pruebas rápidas

En PowerShell, desde la raíz del proyecto:

```powershell
Get-ChildItem app,public -Filter *.php -Recurse | ForEach-Object { C:\xampp\php\php.exe -l $_.FullName }
```

Envía una solicitud de prueba desde el formulario y verifica el registro en phpMyAdmin. Sin credenciales Meta válidas, la solicitud se conserva y WhatsApp aparece como pendiente/error para reintentar después.

Antes de publicar, usa HTTPS, limita los permisos de la cuenta MySQL, configura un token Meta de larga duración según las prácticas oficiales, protege copias de seguridad y revisa los avisos de privacidad/consentimiento aplicables a los datos personales.
