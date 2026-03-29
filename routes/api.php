<?php

use App\Http\Controllers\EmailController;
use App\Http\Controllers\SuggestedResponseController;
use App\Http\Middleware\RequestLogger;
use Illuminate\Support\Facades\Route;

Route::middleware([RequestLogger::class])->group(function () {
    Route::post('/send-email', [EmailController::class, 'sendEmail']);
    Route::get('/emails', [EmailController::class, 'getEmails']);
    Route::get('/emails/{emailId}/suggested-responses', [SuggestedResponseController::class, 'getSuggestedResponses']);
    Route::patch('/suggested-responses/{responseId}/select', [SuggestedResponseController::class, 'selectResponse']);
});
