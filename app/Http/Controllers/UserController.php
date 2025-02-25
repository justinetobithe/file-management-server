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

        $query = User::with('department');

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
        $user->load('department');

        return $this->success([
            'status' => true,
            'data' => $user
        ]);
    }

    public function store(UserRequest $request)
    {
        $existingUser = User::where('email', $request->email)->first();

        if ($existingUser) {
            return response()->json([
                'status' => 'success',
                'message' => 'Role added to existing user.',
                'data' => $existingUser,
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

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = md5(uniqid() . now()) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/image', $imageName);

            $user->image = $imageName;
        }

        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->fill($request->except(['image', 'password']))->save();

        return response()->json([
            'status' => 'success',
            'message' => __('messages.success.updated'),
            'user' => $user,
        ]);
    }

    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('messages.success.deleted'),
            'user' => $user,
        ]);
    }
}
