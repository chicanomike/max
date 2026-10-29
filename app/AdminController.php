<?php

declare(strict_types=1);

namespace App;

use Throwable;

final class AdminController
{
    public function register(Router $router): void
    {
        $router->add('GET', '/admin', [$this, 'dashboard']);
        $router->add('POST', '/admin-login', [$this, 'login']);
        $router->add('POST', '/admin-logout', [$this, 'logout']);
        $router->add('POST', '/admin-status', [$this, 'updateStatus']);
        $router->add('POST', '/admin-delete', [$this, 'delete']);
        $router->add('POST', '/admin-retry', [$this, 'retryNotification']);
    }

    public function dashboard(): void
    {
        if (!Session::isAdmin()) {
            $this->loginPage();
        }

        $filters = [
            'q' => trim(substr((string) ($_GET['q'] ?? ''), 0, 100)),
            'servicio' => trim(substr((string) ($_GET['servicio'] ?? ''), 0, 120)),
            'desde' => $this->validDate((string) ($_GET['desde'] ?? '')),
            'hasta' => $this->validDate((string) ($_GET['hasta'] ?? '')),
        ];
        $flash = (string) ($_SESSION['admin_flash'] ?? '');
        unset($_SESSION['admin_flash']);

        try {
            $repository = new SubmissionRepository(Database::connection());
            $requests = $repository->search($filters);
            $services = $repository->services();
        } catch (Throwable $exception) {
            error_log('Unable to load admin requests: ' . $exception->getMessage());
            $this->dashboardError('No se pudieron cargar las solicitudes. Revisa la conexión a la base de datos.');
        }

        $csrf = self::escape(Session::csrfToken());
        $html = <<<'HTML'
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Solicitudes | MAX</title>
    <style>
        :root{color-scheme:light;--blue:#0056B3;--red:#D62828;--yellow:#FFC107;--white:#FFFFFF;--ink:var(--blue);--muted:var(--blue);--paper:var(--white);--line:var(--yellow);--sans:"Segoe UI",Tahoma,sans-serif}
        *{box-sizing:border-box}body{margin:0;background:var(--paper);color:var(--ink);font:14px var(--sans)}a{color:inherit}.shell{width:min(1280px,calc(100% - 40px));margin:0 auto}.bar{background:var(--white);border-bottom:1px solid var(--line)}.bar-inner{min-height:70px;display:flex;align-items:center;justify-content:space-between;gap:20px}.brand{color:var(--blue);font-size:20px;font-weight:800;letter-spacing:.04em}.bar-side{display:flex;align-items:center;gap:14px;color:var(--muted);font-size:13px}.bar button,.button{min-height:36px;padding:0 12px;border:1px solid var(--blue);border-radius:4px;background:var(--white);color:var(--blue);font:600 13px var(--sans);cursor:pointer}.bar button:hover,.button:hover{background:var(--blue);color:var(--white)}.main{padding:36px 0 64px}.heading{display:flex;align-items:end;justify-content:space-between;gap:20px;margin-bottom:22px}.heading h1{margin:0 0 5px;color:var(--blue);font:400 32px Georgia,"Times New Roman",serif}.heading p{margin:0;color:var(--muted)}.notice{margin:0 0 18px;padding:12px 14px;border-left:4px solid var(--red);background:var(--yellow);color:var(--blue)}.filters{display:grid;grid-template-columns:2fr 1.2fr repeat(2,1fr) auto;align-items:end;gap:10px;margin-bottom:20px;padding:16px 0;border-top:1px solid var(--line);border-bottom:1px solid var(--line)}label{display:grid;gap:6px;color:var(--blue);font-size:12px;font-weight:700}input,select,textarea{width:100%;min-height:38px;padding:8px 9px;border:1px solid var(--line);border-radius:3px;background:var(--white);color:var(--blue);font:14px var(--sans)}input:focus,select:focus,textarea:focus{border-color:var(--blue);outline:2px solid var(--yellow);outline-offset:1px}.primary{border-color:var(--red);background:var(--red);color:var(--white)}.primary:hover{border-color:var(--blue);background:var(--blue);color:var(--white)}.table-wrap{overflow-x:auto;border:1px solid var(--line);background:var(--white)}table{width:100%;border-collapse:collapse;min-width:1080px}th,td{padding:12px 11px;border-bottom:1px solid var(--line);text-align:left;vertical-align:top}th{background:var(--blue);color:var(--white);font-size:11px;text-transform:uppercase}td{line-height:1.5}.request-id{color:var(--red);font-weight:800}.detail{max-width:270px;white-space:pre-wrap;overflow-wrap:anywhere}.subtle{color:var(--muted);font-size:12px}.state-form{display:flex;gap:6px}.state-form select{min-width:126px}.actions{display:grid;gap:7px}.danger,.whatsapp-error{color:var(--red)}.whatsapp-error{max-width:210px;font-size:12px}.empty{padding:30px;text-align:center;color:var(--muted)}.count{margin:10px 0;color:var(--muted);font-size:12px}.login{width:min(420px,calc(100% - 32px));margin:10vh auto;padding:28px;background:var(--white);border:1px solid var(--line);border-top:4px solid var(--yellow)}.login h1{margin:0 0 8px;color:var(--blue);font:400 28px Georgia,"Times New Roman",serif}.login p{margin:0 0 22px;color:var(--muted);line-height:1.5}.login form{display:grid;gap:15px}.login .button{height:42px}.error{margin:0 0 15px;color:var(--red)}
        button:focus-visible,a:focus-visible,input:focus-visible,select:focus-visible,textarea:focus-visible{outline:3px solid var(--yellow);outline-offset:2px}
        html,body{background:var(--white);color:var(--blue)}
        ::placeholder{color:var(--blue);opacity:1}
        ::selection{background:var(--blue);color:var(--white)}
        .bar{background:var(--blue);border-bottom:2px solid var(--yellow)}
        .bar-side{color:var(--white)}
        .bar button,.button{border-color:var(--blue);background:var(--white);color:var(--blue)}
        .bar button:hover,.button:hover{background:var(--blue);color:var(--white)}
        .heading h1,.brand{color:var(--blue)}
        .heading p,.subtle,.empty,.count{color:var(--blue)}
        .notice{border:1px solid var(--yellow);border-left:4px solid var(--yellow);background:var(--blue);color:var(--white)}
        .filters{border-color:var(--yellow)}
        label{color:var(--blue)}
        input,select,textarea{border-color:var(--yellow);background:var(--white);color:var(--blue);-webkit-text-fill-color:var(--blue)}
        option{background:var(--white);color:var(--blue)}
        input:focus,select:focus,textarea:focus{border-color:var(--yellow);outline-color:var(--yellow)}
        .primary{border-color:var(--yellow);background:var(--red);color:var(--white)}
        .primary:hover{background:var(--blue);color:var(--white)}
        .table-wrap{border-color:var(--yellow);background:var(--white)}
        th{border-bottom:2px solid var(--yellow);background:var(--blue);color:var(--white)}
        td{border-color:var(--yellow);background:var(--white);color:var(--blue)}
        tbody tr:nth-child(even) td{background:var(--blue);color:var(--white)}
        tbody tr:nth-child(even) .detail,tbody tr:nth-child(even) .subtle{color:var(--white)}
        .request-id{color:var(--red)}
        .danger,.whatsapp-error,.error{color:var(--red)}
        .login{border:1px solid var(--yellow);border-top:5px solid var(--red);background:var(--white);box-shadow:0 8px 20px var(--blue)}
        .login h1,.login p,.login .brand{color:var(--blue)}
        @media(max-width:760px){.shell{width:calc(100% - 28px)}.bar-inner{min-height:62px}.main{padding-top:26px}.heading{align-items:start;flex-direction:column}.filters{grid-template-columns:1fr 1fr}.filters label:first-child,.filters button{grid-column:1/-1}.bar-side{gap:8px}.heading h1{font-size:28px}}
    </style>
</head>
<body>
    <header class="bar"><div class="shell bar-inner"><a class="brand" href="admin">MAX / SOLICITUDES</a><div class="bar-side"><span>__ADMIN_EMAIL__</span><form method="post" action="admin-logout"><input type="hidden" name="csrf_token" value="__CSRF__"><button type="submit">Cerrar sesión</button></form></div></div></header>
    <main class="shell main">
        <div class="heading"><div><h1>Solicitudes recibidas</h1><p>Consulta y seguimiento de clientes.</p></div><span>__COUNT__ registros</span></div>
        __FLASH__
        <form class="filters" method="get" action="admin">
            <label>Nombre o teléfono<input type="search" name="q" value="__QUERY__" maxlength="100"></label>
            <label>Servicio<select name="servicio"><option value="">Todos</option>__SERVICES__</select></label>
            <label>Desde<input type="date" name="desde" value="__FROM__"></label>
            <label>Hasta<input type="date" name="hasta" value="__TO__"></label>
            <button class="button primary" type="submit">Filtrar</button>
        </form>
        <div class="table-wrap"><table>
            <thead><tr><th>Solicitud</th><th>Cliente</th><th>Servicio / descripción</th><th>Registro</th><th>Estado</th><th>WhatsApp</th><th>Acciones</th></tr></thead>
            <tbody>__ROWS__</tbody>
        </table></div>
        <p class="count">__COUNT__ solicitudes mostradas.</p>
    </main>
    <script>
        document.querySelectorAll('[data-confirm-delete]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (!window.confirm('¿Eliminar permanentemente esta solicitud?')) {
                    event.preventDefault();
                    return;
                }
                form.elements.confirmed.value = '1';
            });
        });
        document.querySelectorAll('[data-confirm-retry]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (!window.confirm('¿Reintentar el envío de la notificación?')) event.preventDefault();
            });
        });
    </script>
</body>
</html>
HTML;
        $serviceOptions = '';
        foreach ($services as $service) {
            $selected = $filters['servicio'] === $service ? ' selected' : '';
            $serviceOptions .= '<option value="' . self::escape($service) . '"' . $selected . '>' . self::escape($service) . '</option>';
        }
        $rows = '';
        foreach ($requests as $request) {
            $id = (int) $request['id'];
            $requestNumber = sprintf('#%03d', $id);
            $statusOptions = '';
            foreach (['pendiente' => 'Pendiente', 'en_proceso' => 'En proceso', 'completada' => 'Completada'] as $value => $label) {
                $selected = $request['estado'] === $value ? ' selected' : '';
                $statusOptions .= '<option value="' . $value . '"' . $selected . '>' . $label . '</option>';
            }
            $error = $request['whatsapp_error'] === null ? '' : '<div class="whatsapp-error">' . self::escape($request['whatsapp_error']) . '</div>';
            $retryForm = $request['whatsapp_estado'] === 'enviado' ? '' : '<form method="post" action="admin-retry" data-confirm-retry><input type="hidden" name="csrf_token" value="' . $csrf . '"><input type="hidden" name="id" value="' . $id . '"><button class="button" type="submit">Reintentar WhatsApp</button></form>';
            $rows .= '<tr>'
                . '<td><span class="request-id">' . $requestNumber . '</span></td>'
                . '<td><strong>' . self::escape($request['nombre']) . '</strong><br>' . self::escape($request['telefono']) . '<br><a href="mailto:' . self::escape($request['correo']) . '">' . self::escape($request['correo']) . '</a></td>'
                . '<td><strong>' . self::escape($request['servicio']) . '</strong><div class="detail">' . self::escape($request['descripcion']) . '</div></td>'
                . '<td>' . self::escape(date('d/m/Y H:i', strtotime($request['fecha_registro']))) . '</td>'
                . '<td><form class="state-form" method="post" action="admin-status"><input type="hidden" name="csrf_token" value="' . $csrf . '"><input type="hidden" name="id" value="' . $id . '"><select name="estado" aria-label="Estado de ' . $requestNumber . '">' . $statusOptions . '</select><button class="button" type="submit">Guardar</button></form></td>'
                . '<td>' . self::escape(ucfirst($request['whatsapp_estado'])) . '<div class="subtle">Intentos: ' . (int) $request['whatsapp_intentos'] . '</div>' . $error . '</td>'
                . '<td><div class="actions">' . $retryForm . '<form method="post" action="admin-delete" data-confirm-delete><input type="hidden" name="csrf_token" value="' . $csrf . '"><input type="hidden" name="id" value="' . $id . '"><input type="hidden" name="confirmed" value="0"><button class="button danger" type="submit">Eliminar</button></form></div></td>'
                . '</tr>';
        }
        if ($rows === '') {
            $rows = '<tr><td class="empty" colspan="7">No hay solicitudes para estos filtros.</td></tr>';
        }

        $html = str_replace(
            ['__ADMIN_EMAIL__', '__CSRF__', '__COUNT__', '__FLASH__', '__QUERY__', '__FROM__', '__TO__', '__SERVICES__', '__ROWS__'],
            [self::escape(Session::adminEmail()), $csrf, (string) count($requests), $flash === '' ? '' : '<p class="notice">' . self::escape($flash) . '</p>', self::escape($filters['q']), self::escape($filters['desde']), self::escape($filters['hasta']), $serviceOptions, $rows],
            $html
        );
        Response::html($html);
    }

    public function login(): void
    {
        if (!Session::verifyCsrf((string) ($_POST['csrf_token'] ?? ''))) {
            $this->loginPage('La sesión venció. Recarga el formulario.');
        }
        if (!RateLimiter::allow('admin-login:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 5, 300)) {
            $this->loginPage('Demasiados intentos. Inténtalo más tarde.', 429);
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $configuredEmail = (string) Config::get('ADMIN_EMAIL', '');
        $passwordHash = (string) Config::get('ADMIN_PASSWORD_HASH', '');

        if (
            $configuredEmail === ''
            || $passwordHash === ''
            || !hash_equals(strtolower($configuredEmail), strtolower($email))
            || !password_verify($password, $passwordHash)
        ) {
            $this->loginPage('Correo o contraseña incorrectos.', 401);
        }

        Session::loginAdmin($configuredEmail);
        header('Location: admin', true, 303);
        exit;
    }

    public function logout(): void
    {
        $this->requireAdmin();
        $this->requireCsrf();
        Session::logout();
        header('Location: admin', true, 303);
        exit;
    }

    public function updateStatus(): void
    {
        $this->requireAdmin();
        $this->requireCsrf();
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $status = (string) ($_POST['estado'] ?? '');
        if ($id === false || !in_array($status, ['pendiente', 'en_proceso', 'completada'], true)) {
            $this->flashAndRedirect('No se pudo actualizar el estado.');
        }

        try {
            $repository = new SubmissionRepository(Database::connection());
            $changed = $repository->updateStatus((int) $id, $status);
            $this->flashAndRedirect($changed ? 'Estado actualizado.' : 'No se encontró la solicitud o ya tenía ese estado.');
        } catch (Throwable $exception) {
            error_log('Unable to update request status: ' . $exception->getMessage());
            $this->flashAndRedirect('No se pudo actualizar el estado.');
        }
    }

    public function delete(): void
    {
        $this->requireAdmin();
        $this->requireCsrf();
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false || ($_POST['confirmed'] ?? '') !== '1') {
            $this->flashAndRedirect('La eliminación no fue confirmada.');
        }

        try {
            $deleted = (new SubmissionRepository(Database::connection()))->delete((int) $id);
            $this->flashAndRedirect($deleted ? 'Solicitud eliminada.' : 'La solicitud ya no existe.');
        } catch (Throwable $exception) {
            error_log('Unable to delete request: ' . $exception->getMessage());
            $this->flashAndRedirect('No se pudo eliminar la solicitud.');
        }
    }

    public function retryNotification(): void
    {
        $this->requireAdmin();
        $this->requireCsrf();
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            $this->flashAndRedirect('Solicitud no válida.');
        }

        try {
            $repository = new SubmissionRepository(Database::connection());
            $request = $repository->find((int) $id);
            if ($request === null) {
                $this->flashAndRedirect('No se encontró la solicitud.');
            }
            if ($request['whatsapp_estado'] === 'enviado') {
                $this->flashAndRedirect('La notificación ya fue enviada.');
            }

            $delivery = (new WhatsAppCloudApi())->sendRequest($request);
            $repository->markWhatsAppResult((int) $id, $delivery['sent'], $delivery['error']);
            if (!$delivery['sent']) {
                error_log('WhatsApp retry for request #' . $id . ' failed: ' . $delivery['error']);
            }
            $this->flashAndRedirect($delivery['sent'] ? 'Notificación enviada.' : 'El envío sigue pendiente; revisa la configuración de WhatsApp.');
        } catch (Throwable $exception) {
            error_log('Unable to retry WhatsApp notification: ' . $exception->getMessage());
            $this->flashAndRedirect('No se pudo reintentar la notificación.');
        }
    }

    private function loginPage(string $error = '', int $status = 200): never
    {
        $csrf = self::escape(Session::csrfToken());
        $errorHtml = $error === '' ? '' : '<p class="error">' . self::escape($error) . '</p>';
        $html = '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Acceso administrativo | MAX</title>'
            . '<style>:root{color-scheme:light;--blue:#0056B3;--red:#D62828;--yellow:#FFC107;--white:#FFFFFF;--ink:var(--blue);--muted:var(--blue);--paper:var(--white);--line:var(--yellow);--sans:"Segoe UI",Tahoma,sans-serif}*{box-sizing:border-box}html,body{min-height:100%;margin:0;background:var(--blue);color:var(--blue)}.login{width:min(420px,calc(100% - 32px));margin:10vh auto;padding:30px;border:1px solid var(--yellow);border-top:5px solid var(--red);background:var(--white);box-shadow:0 8px 20px var(--blue)}.brand{margin-bottom:28px;color:var(--blue);font-size:20px;font-weight:800}.login h1{margin:0 0 8px;color:var(--blue);font:400 28px Georgia,"Times New Roman",serif}.login p{color:var(--blue);line-height:1.5}form{display:grid;gap:15px;margin-top:22px}label{display:grid;gap:6px;color:var(--blue);font-size:12px;font-weight:700}input{width:100%;height:42px;padding:8px 10px;border:1px solid var(--yellow);border-radius:3px;background:var(--white);color:var(--blue);-webkit-text-fill-color:var(--blue);font:14px var(--sans)}input:focus{border-color:var(--blue);outline:2px solid var(--yellow);outline-offset:1px}button{height:42px;border:1px solid var(--red);border-radius:4px;background:var(--red);color:var(--white);font:700 14px var(--sans);cursor:pointer}button:hover{border-color:var(--blue);background:var(--blue)}button:focus-visible{outline:3px solid var(--yellow);outline-offset:2px}.error{color:var(--red)!important}</style></head><body><main class="login"><div class="brand">MAX / ADMINISTRACIÓN</div><h1>Iniciar sesión</h1><p>Acceso al registro de solicitudes.</p>'
            . $errorHtml . '<form method="post" action="admin-login"><input type="hidden" name="csrf_token" value="' . $csrf . '"><label>Correo electrónico<input type="email" name="email" autocomplete="username" maxlength="254" required></label><label>Contraseña<input type="password" name="password" autocomplete="current-password" required></label><button type="submit">Entrar</button></form></main></body></html>';
        Response::html($html, $status);
    }

    private function dashboardError(string $message): never
    {
        Response::html('<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Panel MAX</title><style>:root{--blue:#0056B3;--red:#D62828;--yellow:#FFC107;--white:#FFFFFF}*{box-sizing:border-box}body{min-height:100vh;display:grid;place-items:center;margin:0;padding:20px;background:var(--blue);color:var(--white);font:16px "Segoe UI",Tahoma,sans-serif}.message{width:min(520px,100%);padding:28px;border:2px solid var(--yellow);background:var(--white);color:var(--blue);line-height:1.6}a{display:inline-block;margin-top:12px;padding:8px 14px;border:1px solid var(--red);border-radius:4px;background:var(--red);color:var(--white);text-decoration:none}</style></head><body><main class="message"><p>' . self::escape($message) . '</p><a href="admin">Volver</a></main></body></html>', 503);
    }

    private function requireAdmin(): void
    {
        if (!Session::isAdmin()) {
            http_response_code(403);
            Response::html('Acceso no autorizado.', 403);
        }
    }

    private function requireCsrf(): void
    {
        if (!Session::verifyCsrf((string) ($_POST['csrf_token'] ?? ''))) {
            Response::html('La solicitud venció. Recarga la página e inténtalo de nuevo.', 419);
        }
    }

    private function flashAndRedirect(string $message): never
    {
        $_SESSION['admin_flash'] = $message;
        header('Location: admin', true, 303);
        exit;
    }

    private function validDate(string $date): string
    {
        if ($date === '') {
            return '';
        }

        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        return $parsed !== false && $parsed->format('Y-m-d') === $date ? $date : '';
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}