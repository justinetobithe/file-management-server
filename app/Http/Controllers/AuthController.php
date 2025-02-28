<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthLoginRequest;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\Student;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(UserRequest $request)
    {
        $validated = $request->validated();

        if (User::where('email', $validated['email'])->exists()) {
            return response()->json([
                'status' => false,
                'message' => __('messages.errors.email_exists'),
            ]);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = md5(uniqid() . date('u')) . '.' . pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
            $upload = $image->storeAs("/public/image", $imageName);
            if ($upload) {
                $validated['image'] = $imageName;
            }
        }

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return response()->json([
            'status' => true,
            'message' => __('messages.success.registered'),
            'user' => new UserResource($user),
        ]);
    }

    public function login(AuthLoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => __('messages.errors.user_not_found'),
                'data' => null
            ]);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => __('messages.errors.incorrect_password'),
                'data' => null
            ]);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = auth()->user();
            $token = $user->generateToken();

            $data = [
                'token' => $token,
                'user' => new UserResource($user),
            ];

            return response()->json([
                'status' => true,
                'message' => __('messages.success.login'),
                'data' => $data,
            ])->withCookie(cookie('auth_token', $token, 60));
        }

        return response()->json([
            'status' => false,
            'message' => __('messages.invalid.credentials'),
            'data' => null
        ]);
    }


    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => __('messages.success.deleted'),
        ]);
    }

    public function user()
    {
        return response()->json([
            'status' => true,
            'user' => Auth::user(),
        ]);
    }
}
