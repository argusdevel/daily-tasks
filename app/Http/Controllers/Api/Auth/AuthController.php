<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * @uses registration
     * @uses login
     * @uses refresh
     */

    /**
     * User registration using name, email, password and password confirmation
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function registration(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|max:12|confirmed',
            'password_confirmation' => 'required|min:8|max:12',
        ]);

        if ($validation->fails()) {
            return response()->json(['status' => 'error', 'message' => $validation->messages()->first(), 'data' => []], 400);
        }

        $data = $request->all();

        $user = new User();

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = $data['password'];

        $user->save();

        return response()->json(['status' => 'success', 'message' => '', 'data' => ['redirect' => '/login']], 201);
    }

    /**
     * Authorization using email(login) and password
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['status' => 'error', 'message' => 'Incorrect Login or Password', 'data' => []], 401);
        }

        return response()->json(['status' => 'success', 'message' => '', 'data' => ['token' => $token]]);
    }

    /**
     * Refresh User token
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        try {
            $token = auth()->refresh();
        } catch (TokenInvalidException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => []], 401);
        }

        return response()->json(['status' => 'success', 'message' => '', 'data' => ['token' => $token]]);
    }
}
