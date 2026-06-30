<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use App\Support\ApiResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',          // arquivo de rotas da API
        commands: __DIR__.'/../routes/console.php', // comandos de terminal
        apiPrefix: 'api',                           // todas as rotas começam com /api
        health: '/up',                              // rota de health-check
    )
    ->withMiddleware(function (Middleware $middleware) {
        // nada extra: o auth:api é aplicado rota a rota no api.php
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // sempre responder em JSON quando a rota for de API
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $request->is('api/*') || $request->expectsJson());

        // aqui transformamos qualquer erro no nosso JSON padrão com o status certo
        $exceptions->render(function (Throwable $e, Request $request) {
            // se não for API, deixa o Laravel tratar do jeito normal
            if (! ($request->is('api/*') || $request->expectsJson())) {
                return null;
            }

            // erro de validação -> 422 com os erros por campo
            if ($e instanceof ValidationException) {
                return ApiResponse::error(
                    message: 'Os dados informados são inválidos.',
                    status: 422,
                    errors: $e->errors(),
                );
            }

            // não está logado -> 401
            if ($e instanceof AuthenticationException) {
                return ApiResponse::error('Não autenticado. Faça login para continuar.', 401);
            }

            // token inválido/expirado -> 401
            if ($e instanceof JWTException) {
                return ApiResponse::error('Token inválido ou expirado.', 401);
            }

            // logado mas sem permissão -> 403
            if ($e instanceof AuthorizationException || $e instanceof AccessDeniedHttpException) {
                return ApiResponse::error('Você não tem permissão para executar esta ação.', 403);
            }

            // não encontrado -> 404
            if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                return ApiResponse::error('Recurso não encontrado.', 404);
            }

            // método HTTP errado -> 405
            if ($e instanceof MethodNotAllowedHttpException) {
                return ApiResponse::error('Método HTTP não permitido para esta rota.', 405);
            }

            // outros erros HTTP -> mantém o status original
            if ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();
                return ApiResponse::error($e->getMessage() ?: 'Erro na requisição.', $status ?: 500);
            }

            // qualquer outro erro -> 500 (mostra a mensagem real só em modo debug)
            $message = config('app.debug')
                ? $e->getMessage()
                : 'Ocorreu um erro interno no servidor.';

            return ApiResponse::error($message, 500);
        });
    })->create();
