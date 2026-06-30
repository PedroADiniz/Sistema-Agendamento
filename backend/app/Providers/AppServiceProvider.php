<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

use App\Models\User;
use App\Policies\UserPolicy;
use App\Policies\AvailabilityPolicy;

use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\AvailabilityRepositoryInterface;
use App\Repositories\Contracts\AppointmentRepositoryInterface;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\AvailabilityRepository;
use App\Repositories\Eloquent\AppointmentRepository;

class AppServiceProvider extends ServiceProvider
{
    // liga cada interface de repositório à sua implementação Eloquent.
    // assim, quando um service pede a interface, recebe a classe concreta.
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(AvailabilityRepositoryInterface::class, AvailabilityRepository::class);
        $this->app->bind(AppointmentRepositoryInterface::class, AppointmentRepository::class);
    }

    // registra as Policies (regras de permissão admin x atendente)
    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(\App\Models\Availability::class, AvailabilityPolicy::class);
    }
}
