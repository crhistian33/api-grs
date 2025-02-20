<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\UserCollection;
use App\Http\Resources\v1\UserResource;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;

use function Laravel\Prompts\error;

class UserController extends Controller
{
    use ApiResponse;

    protected $user;

    public function __construct()
    {
        $this->middleware('auth:api');
    }
}
