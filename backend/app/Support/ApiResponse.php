<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

// Classe que monta a resposta JSON sempre no mesmo formato:
// { success, message, data }. Assim toda a API responde igual.
class ApiResponse
{
    // Resposta quando deu tudo certo
    public static function success(
        mixed $data = null,
        string $message = 'Operação realizada com sucesso.',
        int $status = 200
    ): JsonResponse {
        // devolve o JSON com success = true e os dados
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    // Resposta quando deu erro
    public static function error(
        string $message = 'Ocorreu um erro.',
        int $status = 400,
        array $errors = []
    ): JsonResponse {
        // monta o corpo padrão do erro (data sempre null aqui)
        $payload = [
            'success' => false,
            'message' => $message,
            'data' => null,
        ];

        // se vieram erros de validação por campo, adiciona na resposta
        if (! empty($errors)) {
            $payload['errors'] = $errors;
        }

        // devolve o JSON com o status HTTP certo (400, 401, 422, ...)
        return response()->json($payload, $status);
    }
}
