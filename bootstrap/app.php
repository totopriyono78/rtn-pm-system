<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Railway (and most PaaS providers) terminate TLS at their edge proxy and
        // forward plain HTTP to the container, adding X-Forwarded-* headers.
        // Without trusting that proxy, Laravel thinks every request is HTTP,
        // so asset()/Vite/url() generate http:// links -> browsers block them
        // as mixed content on an https:// page. Trusting all proxies is safe
        // here because the container is only reachable through Railway's edge.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'client.active' => \App\Http\Middleware\EnsureClientIsActive::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        // Client Portal (SRS 4.3) memakai guard 'client' sendiri -- tanpa ini,
        // middleware 'auth:client' bawaan Laravel akan selalu redirect ke
        // route('login') (halaman staf) begitu saja, tidak peduli guard mana
        // yang dipakai, karena Authenticate::redirectTo() default hardcode
        // ke 'login'. Arahkan balik ke login portal untuk path /portal/*.
        $middleware->redirectGuestsTo(function ($request) {
            return $request->is('portal/*') ? route('client.login') : route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
