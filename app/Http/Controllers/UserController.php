<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $pageSize = $request->input('page_size');
        $filter = $request->input('filter');
        $sortColumn = $request->input('sort_column', 'first_name');
        $sortDesc = $request->input('sort_desc', false) ? 'desc' : 'asc';

        $query = User::with(['position.department', 'position.designation']);

        if ($filter) {
            $query->where(function ($q) use ($filter) {
                $q->where('first_name', 'like', "%{$filter}%")
                    ->orWhere('last_name', 'like', "%{$filter}%")
                    ->orWhere('email', 'like', "%{$filter}%")
                    ->orWhere('phone', 'like', "%{$filter}%")
                    ->orWhere('address', 'like', "%{$filter}%")
                    ->orWhere('role', 'like', "%{$filter}%");
            });
        }

        if (in_array($sortColumn, ['first_name', 'last_name', 'email', 'phone', 'address', 'role'])) {
            $query->orderBy($sortColumn, $sortDesc);
        }

        if ($pageSize) {
            $users = $query->paginate($pageSize);
        } else {
            $users = $query->get();
        }

        return $this->success($users);
    }

    public function show(User $user)
    {
        $user->load(['position.department', 'position.designation']);

        return $this->success([
            'status' => true,
            'data' => $user
        ]);
    }

    public function store(UserRequest $request)
    {
        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'A user with this email already exists.',
            ]);
        }

        $userData = $request->all();
        $userData['password'] = Hash::make($request->password);

        $user = User::create($userData);

        return response()->json([
            'status' => 'success',
            'message' => __('messages.success.created'),
            'user' => $user,
        ]);
    }


    public function update(UserRequest $request, string $id)
    {
        $user = User::findOrFail($id);

        if ($request->email && $request->email !== $user->email) {
            if (User::where('email', $request->email)->where('id', '!=', $id)->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This email is already in use by another user.',
                ]);
            }
        }

        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Current password is incorrect.',
                ]);
            }

            if ($request->filled('new_password')) {
                $user->password = Hash::make($request->new_password);
            }
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = md5(uniqid() . now()) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/image', $imageName);

            $user->image = $imageName;
        }

        $user->fill($request->except(['image', 'password', 'current_password', 'new_password']))->save();

        return response()->json([
            'status' => 'success',
            'message' => __('messages.success.updated'),
            'user' => $user,
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        if ($request->filled('password')) {
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Password is incorrect.',
                ], 403);
            }
        }

        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('messages.success.deleted'),
            'user' => $user,
        ]);
    }

    public function resetPassword(Request $request, string $id)
    {
        $request->validate([
            'new_password' => 'required|min:6',
        ]);

        $user = User::findOrFail($id);
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password has been reset successfully.',
        ]);
    }
}
