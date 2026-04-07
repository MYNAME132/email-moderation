<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBlockedDomainRequest;
use App\Http\Requests\StoreClassificationKeywordRequest;
use App\Models\BlockedDomain;
use App\Models\ClassificationKeyword;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ClassificationRuleController extends Controller
{

    public function indexBlockedDomains(): JsonResponse
    {
        return response()->json(BlockedDomain::get(['id', 'domain', 'created_at']));
    }

    public function storeBlockedDomain(StoreBlockedDomainRequest $request): JsonResponse
    {
        Log::info('Adding blocked domain', $request->validated());

        $domain = BlockedDomain::create($request->validated());

        return response()->json($domain, 201);
    }

    public function destroyBlockedDomain(string $id): JsonResponse
    {
        $domain = BlockedDomain::findOrFail($id);

        Log::info('Removing blocked domain', ['domain' => $domain->domain]);

        $domain->delete();

        return response()->json(['message' => 'Blocked domain removed']);
    }

    public function indexKeywords(): JsonResponse
    {
        return response()->json(ClassificationKeyword::get(['id', 'keyword', 'type', 'created_at']));
    }

    public function storeKeyword(StoreClassificationKeywordRequest $request): JsonResponse
    {
        Log::info('Adding classification keyword', $request->validated());

        $keyword = ClassificationKeyword::firstOrCreate(
            ['keyword' => $request->validated('keyword'), 'type' => $request->validated('type')],
        );

        $status = $keyword->wasRecentlyCreated ? 201 : 200;

        return response()->json($keyword, $status);
    }

    public function destroyKeyword(string $id): JsonResponse
    {
        $keyword = ClassificationKeyword::findOrFail($id);

        Log::info('Removing classification keyword', ['keyword' => $keyword->keyword, 'type' => $keyword->type]);

        $keyword->delete();

        return response()->json(['message' => 'Keyword removed']);
    }
}
