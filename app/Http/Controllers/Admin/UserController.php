<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\{User\CreateUserRequest, User\DeleteUserRequest, User\UpdateUserRequest};
use App\Models\User;
use App\Services\UserService;
use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * @param CreateUserRequest $request
     * @return JsonResponse
     */
    public function createUser(CreateUserRequest $request): JsonResponse
    {
        try {
            $data = UserService::createUser($request->validated(), true);
            return response()->json([
                'data' => $data,
                'message' => 'User create request sent successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => 'Error creating user: ' . $e->getMessage(),
                "error" => $e->getTrace()[0]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param UpdateUserRequest $request
     * @return JsonResponse
     */
    public function updateUser(UpdateUserRequest $request): JsonResponse
    {
        try {
            $user = User::find($request->input('user_id'));
            $data = UserService::updateUser($user, $request->validated(), true);
            return response()->json([
                'data' => $data,
                'message' => 'User update request sent successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => 'Error updating user: ' . $e->getMessage(),
                "error" => $e->getTrace()[0]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param DeleteUserRequest $request
     * @return JsonResponse
     */
    public function deleteUser(DeleteUserRequest $request): JsonResponse
    {
        try {
            $user = User::find($request->input('user_id'));
            $data = UserService::deleteUser($user, true);
            return response()->json([
                'data' => $data,
                'message' => 'User delete request sent successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => 'Error deleting user: ' . $e->getMessage(),
                "error" => $e->getTrace()[0]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
