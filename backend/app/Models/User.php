<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

// Model do usuário. Implementa JWTSubject para funcionar com o login JWT.
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    // campos que podem ser preenchidos em massa (create/update)
    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
    ];

    // campos que nunca aparecem no JSON de resposta
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // conversões automáticas de tipo dos campos
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',    // a senha já é salva com hash
            'role' => UserRole::class, // o campo role vira o enum UserRole
        ];
    }

    // diz qual campo identifica o usuário dentro do token (o id)
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    // adiciona o perfil (role) dentro do token, para usar nas permissões
    public function getJWTCustomClaims(): array
    {
        return [
            'role' => $this->role instanceof UserRole ? $this->role->value : $this->role,
        ];
    }

    // true se o usuário for admin
    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    // true se o usuário for atendente
    public function isAtendente(): bool
    {
        return $this->role === UserRole::ATENDENTE;
    }

    // um usuário (atendente) tem várias disponibilidades
    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class);
    }

    // um usuário (atendente) tem vários agendamentos
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
