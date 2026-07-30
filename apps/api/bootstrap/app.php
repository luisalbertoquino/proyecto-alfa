<?php

use App\Modules\Pedidos\Exceptions\StockInsuficienteException;
use App\Shared\Http\Middleware\ResolvePublicTenant;
use App\Shared\Http\Middleware\ResolveTenant;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api_v1.php',
        apiPrefix: 'api/v1',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'resolve-tenant' => ResolveTenant::class,
            'resolve-public-tenant' => ResolvePublicTenant::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // Forma de error estándar del proyecto (docs/architecture/apis.md,
        // docs/standards/api.md): { "error": { "codigo", "mensaje", "detalles" } }.
        // Catálogo de códigos: se va ampliando módulo por módulo, ver
        // docs/standards/api.md "Decisiones futuras".
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return match (true) {
                $e instanceof StockInsuficienteException => response()->json(['error' => [
                    'codigo' => 'STOCK_INSUFICIENTE',
                    'mensaje' => $e->getMessage(),
                    'detalles' => ['producto_id' => $e->productoId, 'disponible' => $e->disponible],
                ]], 422),
                $e instanceof ValidationException => response()->json(['error' => [
                    'codigo' => 'VALIDACION',
                    'mensaje' => 'Los datos enviados no son válidos.',
                    'detalles' => $e->errors(),
                ]], 422),
                $e instanceof NotFoundHttpException => response()->json(['error' => [
                    'codigo' => 'NO_ENCONTRADO',
                    'mensaje' => 'El recurso solicitado no existe.',
                    'detalles' => (object) [],
                ]], 404),
                $e instanceof AuthenticationException => response()->json(['error' => [
                    'codigo' => 'NO_AUTENTICADO',
                    'mensaje' => 'Debes iniciar sesión.',
                    'detalles' => (object) [],
                ]], 401),
                default => null,
            };
        });
    })->create();
