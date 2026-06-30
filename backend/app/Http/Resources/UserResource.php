<?php

namespace App\Http\Resources;

use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// Monta o JSON de saída de um usuário (nunca mostra a senha)
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // garante que role seja o enum para pegar o rótulo bonito
        $role = $this->role instanceof UserRole ? $this->role : UserRole::from($this->role);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $role->value,         // admin | atendente
            'role_label' => $role->label(), // Administrador | Atendente
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
