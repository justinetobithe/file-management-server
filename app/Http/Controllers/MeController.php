<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user()->load(['position', 'position.department', 'position.designation']);

        return new UserResource($user);
    }
}
