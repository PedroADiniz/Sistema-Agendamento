<?php

namespace App\Http\Controllers;

use App\Http\Requests\Availability\StoreAvailabilityRequest;
use App\Http\Requests\Availability\UpdateAvailabilityRequest;
use App\Http\Resources\AvailabilityResource;
use App\Models\Availability;
use App\Services\AvailabilityService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// Controller das disponibilidades. Criar/editar/excluir: só admin.
class AvailabilityController extends Controller
{
    // recebe o service por injeção de dependência
    public function __construct(
        private readonly AvailabilityService $availabilities
    ) {}

    // GET /api/availabilities -> lista todas (ou de um atendente, se mandar ?user_id=)
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Availability::class);

        // pega o filtro de atendente da URL, se tiver
        $userId = $request->integer('user_id') ?: null;

        return ApiResponse::success(
            AvailabilityResource::collection($this->availabilities->list($userId)),
            'Disponibilidades listadas com sucesso.'
        );
    }

    // POST /api/availabilities -> cria uma janela (só admin)
    public function store(StoreAvailabilityRequest $request): JsonResponse
    {
        $this->authorize('create', Availability::class);

        $availability = $this->availabilities->create($request->validated());

        return ApiResponse::success(
            new AvailabilityResource($availability),
            'Disponibilidade criada com sucesso.',
            201
        );
    }

    // PUT/PATCH /api/availabilities/{availability} -> edita (só admin)
    public function update(UpdateAvailabilityRequest $request, Availability $availability): JsonResponse
    {
        $this->authorize('update', $availability);

        $updated = $this->availabilities->update($availability, $request->validated());

        return ApiResponse::success(
            new AvailabilityResource($updated),
            'Disponibilidade atualizada com sucesso.'
        );
    }

    // DELETE /api/availabilities/{availability} -> exclui (só admin)
    public function destroy(Availability $availability): JsonResponse
    {
        $this->authorize('delete', $availability);

        $this->availabilities->delete($availability);

        return ApiResponse::success(null, 'Disponibilidade excluída com sucesso.');
    }
}
