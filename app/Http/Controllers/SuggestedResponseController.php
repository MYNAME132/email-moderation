<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\DTO\SuggestedResponseDto;
use Symfony\Component\HttpFoundation\Request;
use App\DTO\PaginationDto;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Support\Facades\Log;


class SuggestedResponseController extends Controller
{
    public function index(Request $request, string $emailId): JsonResponse
    {
        Log::info('suggested responses ', [$emailId]);
        $perPage = $request->get('per_page', 5);

        $email = Email::find($emailId);

        if (!$email) {
            return response()->json([
                'message' => 'Email not found'
            ], 404);
        }

        $responses = $email->suggestedResponses()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $dtoCollection = array_map(
            fn($response) => SuggestedResponseDto::fromModel($response)->toArray(),
            $responses->items()
        );

        $paginationDto = new PaginationDto(
            data: $dtoCollection,
            currentPage: $responses->currentPage(),
            lastPage: $responses->lastPage(),
            perPage: $responses->perPage(),
            total: $responses->total()
        );

        return response()->json([
            'email_id' => $email->id,
            'suggested_responses' => $paginationDto->toArray()
        ]);
    }
}
