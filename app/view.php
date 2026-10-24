<?php
declare(strict_types=1);

function view_path(string $view): string
{
    if (!preg_match('#^[a-z0-9_/]+$#', $view)) {
        throw new InvalidArgumentException('Vue invalide');
    }
    return BASE_PATH . '/app/views/' . $view . '.php';
}

function partial(string $view, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require view_path($view);
}

function render(string $view, array $data = [], string $layout = 'layout'): void
{
    $data['user'] ??= current_user();
    extract($data, EXTR_SKIP);
    ob_start();
    require view_path($view);
    $content = ob_get_clean();
    require view_path($layout);
}

function send_security_headers(): void
{
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; font-src 'self'; script-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'self'; object-src 'none'");
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header('Cross-Origin-Opener-Policy: same-origin');
    if (is_https()) {
        header('Strict-Transport-Security: max-age=31536000');
    }
}
