<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RequestApproval\TakeActionRequest;
use App\Http\Resources\RequestApprovalResource;
use App\Models\RequestApproval;
use App\Services\RequestApprovalService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class RequestApprovalController extends Controller
{
    /**
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        return RequestApprovalResource::collection(RequestApproval::query()->get());
    }

    /**
     * @return AnonymousResourceCollection
     */
    public function pendingRequest(): AnonymousResourceCollection
    {
        return RequestApprovalResource::collection(
            RequestApproval::whereStatus(RequestApproval::STATUS_PENDING)->get()
        );
    }

    /**
     * @param TakeActionRequest $request
     * @return JsonResponse
     */
    public function takeAction(TakeActionRequest $request): JsonResponse
    {
        $requestApproval = RequestApproval::find($request->input('request_approval_id'));
        return match ($request->input('action')) {
            RequestApproval::ACTION_APPROVE => $this->approveRequest($requestApproval),
            default => $this->deleteRequest($requestApproval)
        };
    }

    /**
     * @param RequestApproval $requestApproval
     * @return JsonResponse
     */
    protected function approveRequest(RequestApproval $requestApproval): JsonResponse
    {
        try {
            DB::beginTransaction();
                RequestApprovalService::approveRequest($requestApproval);
            $requestApproval->status = RequestApproval::STATUS_APPROVED;
            $requestApproval->save();
            DB::commit();
            return response()->json(['message' => 'Request approved successfully.']);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                "success" => false,
                "message" => 'Error approving request: ' . $e->getMessage(),
                "error" => $e->getTrace()[0]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param RequestApproval $requestApproval
     * @return JsonResponse
     */
    protected function deleteRequest(RequestApproval $requestApproval): JsonResponse
    {
        try {
            $requestApproval->delete();
            return response()->json(['message' => 'Request declined and deleted successfully.']);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => 'Error deleting request: ' . $e->getMessage(),
                "error" => $e->getTrace()[0]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
