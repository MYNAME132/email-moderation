<?php

use App\Http\Controllers\ClassificationRuleController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\OllamaController;
use App\Http\Controllers\SuggestedResponseController;
use App\Http\Middleware\RequestLogger;
use Illuminate\Support\Facades\Route;

Route::middleware([RequestLogger::class])->group(function () {
    Route::post('/send-email', [EmailController::class, 'sendEmail']);
    Route::get('/emails', [EmailController::class, 'getEmails']);
    Route::post('/emails/{emailId}/send', [EmailController::class, 'sendEmailById']);
    Route::get('/emails/{emailId}/suggested-responses', [SuggestedResponseController::class, 'getSuggestedResponses']);
    Route::patch('/suggested-responses/{responseId}/select', [SuggestedResponseController::class, 'selectResponse']);
    Route::patch('/suggested-responses/{responseId}', [SuggestedResponseController::class, 'updateResponse']);
    Route::post('/ollama/{emailId}/generate/', [OllamaController::class, 'generate']);

    // Classification rules
    Route::get('/classification/blocked-domains', [ClassificationRuleController::class, 'getBlockedDomains']);
    Route::post('/classification/blocked-domains', [ClassificationRuleController::class, 'storeBlockedDomain']);
    Route::delete('/classification/blocked-domains/{id}', [ClassificationRuleController::class, 'deleteBlockedDomain']);
    Route::get('/classification/keywords', [ClassificationRuleController::class, 'getKeywords']);
    Route::post('/classification/keywords', [ClassificationRuleController::class, 'storeKeyword']);
    Route::delete('/classification/keywords/{id}', [ClassificationRuleController::class, 'deleteKeyword']);
});
