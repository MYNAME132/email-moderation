<?php

namespace App\Http\Controllers;

use App\Contracts\SuggestedResponseServiceInterface;
use App\Http\Requests\GetSuggestedResponsesRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Support\Facades\Log;



class SuggestedResponseController extends Controller
{
    public function __construct(
        private SuggestedResponseServiceInterface $suggestedResponseService
    ) {}

    public function getSuggestedResponses(GetSuggestedResponsesRequest $request): JsonResponse
    {
        $emailId = $request->getEmailId();

        Log::info('Incoming get suggested responses request', [
            'email_id' => $emailId,
        ]);

        $result = $this->suggestedResponseService->getByEmail($emailId, $request);

        return response()->json($result->toArray());
    }

    public function selectResponse(string $responseId): JsonResponse
    {
        Log::info('Selecting suggested response', [
            'response_id' => $responseId
        ]);

        $result = $this->suggestedResponseService->selectResponse($responseId);

        return response()->json($result);
    }
}
