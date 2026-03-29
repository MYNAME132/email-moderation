<?php

namespace App\Services;

use App\Contracts\SuggestedResponseServiceInterface;
use App\DTO\PaginationDto;
use App\DTO\SuggestedResponseDto;
use App\Models\Email;
use App\Models\SuggestedResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Request;

class SuggestedResponseService implements SuggestedResponseServiceInterface
{
    public function getByEmail(string $emailId, Request $request): PaginationDto
    {
        $perPage = $request->get('per_page', 5);

        $email = Email::findOrFail($emailId);

        $responses = $email->suggestedResponses()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $dtoCollection = array_map(
            fn($response) => SuggestedResponseDto::fromModel($response)->toArray(),
            $responses->items()
        );

        return new PaginationDto(
            data: $dtoCollection,
            currentPage: $responses->currentPage(),
            lastPage: $responses->lastPage(),
            perPage: $responses->perPage(),
            total: $responses->total()
        );
    }

    public function selectResponse(string $responseId): array
    {
        return DB::transaction(function () use ($responseId) {

            $selectedResponse = SuggestedResponse::findOrFail($responseId);

            $emailId = $selectedResponse->email_id;

            $selectedResponse->update([
                'is_selected' => true
            ]);

            SuggestedResponse::where('email_id', $emailId)
                ->where('id', '!=', $responseId)
                ->delete();

            return [
                'message' => 'Suggested response selected successfully',
                'selected_response_id' => $responseId
            ];
        });
    }
}
