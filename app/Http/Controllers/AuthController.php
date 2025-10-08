<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /** POST /api/auth/register */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email', Rule::unique('users','email')],
            'password' => ['required','string','min:8'], // expect password + password_confirmation
            'phone'    => ['nullable','string','max:30'],
            'pub_id'   => ['nullable','exists:pubs,id'],
            'role'     => ['nullable','string','in:Admin,Manager,Employee'], // optional
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'phone'    => $data['phone'] ?? null,
            'pub_id'   => $data['pub_id'] ?? null,
        ]);

        if (!empty($data['role'])) {
            $user->assignRole($data['role']);
        } else {
            $user->assignRole('Employee'); // sensible default
        }

        return response()->json([
            'message' => 'User registered',
            'user'    => $user->only(['id','name','email','phone','pub_id']),
        ], 201);
    }

    /** POST /api/auth/login */
    public function login(Request $request)
    {
        $cred = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required','string'],
        ]);

        // Optional: throttle brute force (requires throttle middleware on route too)
        if (!$token = Auth::guard('api')->attempt($cred)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return $this->respondWithToken($token);
    }

    /** GET /api/me */
    public function me(Request $request)
    {
        $user = $request->user('api')->load([
            'pub:id,name,pub_number',
            'managedPubs:id,name,pub_number,manager_id',
        ]);

        // attach roles for frontend UI gating
        $roles = $user->getRoleNames();
        return response()->json([
            'user'  => $user->only(['id','name','email','phone','pub_id']),
            'roles' => $roles,
            'pub'   => $user->pub,
            'managed_pubs' => $user->managedPubs,
        ]);
    }

    /** POST /api/auth/logout */
    public function logout()
    {
        try {
            Auth::guard('api')->logout(); // blacklist current token
        } catch (\Throwable $e) {
            // ignore
        }
        return response()->json(['message' => 'Logged out']);
    }

    /** POST /api/auth/refresh */
    public function refresh()
    {
        try {
            $newToken = Auth::guard('api')->refresh();
            return $this->respondWithToken($newToken);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Token refresh failed'], 401);
        }
    }

    protected function respondWithToken(string $token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'expires_in'   => Auth::guard('api')->factory()->getTTL() * 60,
        ]);
    }

    /** GET /api/users  (Admin only) */
    public function users(Request $request)
    {
        if (!$request->user('api')->hasRole('Admin')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        // Use pagination for safety
        return response()->json(User::query()
            ->select('id','name','email','phone','pub_id','created_at')
            ->orderByDesc('id')
            ->paginate(20));
    }
}
