<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\{LoginRequest, RegisterRequest};
use App\Models\Admin;
use Exception;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    /**
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $admin = Admin::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
            ]);
            return response()->json($this->getResponse($admin));
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => 'Error registering admin: ' . $e->getMessage(),
                "error" => $e->getTrace()[0]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $admin = Admin::query()->whereEmail($request->input('email'))
                ->first();

            if (!$admin || !Hash::check($request->input('password'), $admin->password)) {
                return response()->json(['error' => 'Invalid credentials'], Response::HTTP_BAD_REQUEST);
            }

            return response()->json($this->getResponse($admin));
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => 'Error registering admin: ' . $e->getMessage(),
                "error" => $e->getTrace()[0]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->tokens()->delete();
            return response()->json(['message' => 'Successfully logged out.']);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => 'Error registering admin: ' . $e->getMessage(),
                "error" => $e->getTrace()[0]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Admin $user
     * @return array
     */
    protected function getResponse(Admin $user): array
    {
        return [
            'user' => $user,
            'token' => $user->createToken('adminToken', ['admin-access'])->plainTextToken,
        ];
    }
}
