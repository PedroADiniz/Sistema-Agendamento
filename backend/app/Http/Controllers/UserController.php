<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

// Controller dos usuários (CRUD). Valida, checa permissão e chama o service.
class UserController extends Controller
{
    // recebe o service por injeção de dependência
    public function __construct(
        private readonly UserService $users
    ) {}

    // GET /api/users -> lista os usuários
    public function index(): JsonResponse
    {
        // checa permissão (todos podem ver)
        $this->authorize('viewAny', User::class);

        return ApiResponse::success(
            UserResource::collection($this->users->list()),
            'Usuários listados com sucesso.'
        );
    }

    // POST /api/users -> cria um usuário (só admin)
    public function store(StoreUserRequest $request): JsonResponse
    {
        // checa permissão (só admin cria)
        $this->authorize('create', User::class);

        // cria com os dados já validados
        $user = $this->users->create($request->validated());

        return ApiResponse::success(
            new UserResource($user),
            'Usuário criado com sucesso.',
            201
        );
    }

    // GET /api/users/{user} -> mostra um usuário
    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return ApiResponse::success(new UserResource($user), 'Usuário encontrado.');
    }

    // PUT/PATCH /api/users/{user} -> edita (admin: qualquer um; atendente: só ele)
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        // checa permissão de edição
        $this->authorize('update', $user);

        $data = $request->validated();

        // atendente não pode mudar o próprio tipo (evita se auto-promover a admin)
        if (! $request->user()->isAdmin()) {
            unset($data['role']);
        }

        $updated = $this->users->update($user, $data);

        return ApiResponse::success(
            new UserResource($updated),
            'Usuário atualizado com sucesso.'
        );
    }

    // DELETE /api/users/{user} -> exclui (só admin, e não a si mesmo)
    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $this->users->delete($user);

        return ApiResponse::success(null, 'Usuário excluído com sucesso.');
    }
}
