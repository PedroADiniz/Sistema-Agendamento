<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

// Controller base: traz o $this->authorize() (Policies) para os outros controllers
abstract class Controller
{
    use AuthorizesRequests, ValidatesRequests;
}
