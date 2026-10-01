# MAX Security API

Este proyecto es una base segura para una API en PHP con enfoque en seguridad web.

## Estructura

- public/index.php: punto de entrada de la API
- app/: clases PHP reutilizables
- .env.example: variables de entorno
- .htaccess: reglas de seguridad y rewrite

## Requisitos

- PHP 8.1+
- Apache con mod_rewrite habilitado
- XAMPP o cualquier servidor PHP compatible

## Puesta en marcha

1. Copia `.env.example` a `.env` y ajusta tus valores.
2. Mueve la carpeta del proyecto dentro de tu carpeta web de XAMPP, por ejemplo: `C:\xampp\htdocs\MAX`.
3. Asegúrate de que Apache tenga `mod_rewrite` habilitado.
4. Abre la API en:
   - http://localhost/MAX/health
   - http://localhost/MAX/api/v1/health

## Seguridad aplicada

- Headers de seguridad HTTP
- CORS configurable
- Validación de entrada
- Uso de PDO con prepared statements
- Hashing de contraseñas con `password_hash`
- JWT con expiración
- Rate limiting básico
- Errores sanitizados

## Demo de login

Usuario demo:

- Email: admin@max.local
- Contraseña: admin123

## Endpoint principal

- `POST /api/v1/login`
- `GET /api/v1/profile`
- `GET /api/v1/posts`

## Nota

Este es un starter seguro para aprender y ampliar. En producción debes usar HTTPS real, base de datos real, auditoría, logs y pruebas automatizadas.
