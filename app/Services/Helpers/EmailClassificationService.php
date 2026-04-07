<?php

namespace App\Services\Helpers;

use App\Models\BlockedDomain;
use App\Models\ClassificationKeyword;
use App\Models\Email;
use App\Enums\ResponseDecisionEnum;
use Illuminate\Support\Facades\Cache;

class EmailClassificationService
{
    private const CACHE_TTL = 3600;

    public function classify(
        Email $email,
    ): ResponseDecisionEnum {
        $email->loadMissing('document');

        $body = $email->document?->body ?? null;
        if ($this->isNoReply($email->sender)) {
            return ResponseDecisionEnum::REJECTED;
        }

        if ($this->isBlockedDomain($email->sender)) {
            return ResponseDecisionEnum::REJECTED;
        }

        if ($this->hasAutomatedSubject($email->subject)) {
            return ResponseDecisionEnum::REJECTED;
        }

        if ($this->hasAutomatedContent($body)) {
            return ResponseDecisionEnum::REJECTED;
        }

        return ResponseDecisionEnum::PENDING;
    }

    private function isNoReply(string $sender): bool
    {
        $sender = strtolower($sender);

        return str_contains($sender, 'no-reply') ||
            str_contains($sender, 'noreply') ||
            str_contains($sender, 'do-not-reply');
    }

    private function isBlockedDomain(string $sender): bool
    {
        $domain = $this->extractDomain($sender);

        $blocked = Cache::remember(
            'classification.blocked_domains',
            self::CACHE_TTL,
            fn() => BlockedDomain::pluck('domain')->all(),
        );

        foreach ($blocked as $blockedDomain) {
            if (str_contains($domain, $blockedDomain)) {
                return true;
            }
        }

        return false;
    }

    private function hasAutomatedSubject(?string $subject): bool
    {
        if (!$subject) return false;

        $subject = strtolower($subject);

        $keywords = Cache::remember(
            'classification.subject_keywords',
            self::CACHE_TTL,
            fn() => ClassificationKeyword::where('type', 'subject')->pluck('keyword')->all(),
        );

        foreach ($keywords as $keyword) {
            if (str_contains($subject, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function hasAutomatedContent(array|string|null $body): bool
    {
        if (empty($body)) {
            return false;
        }

        // If body is an array, try to extract the actual content
        if (is_array($body)) {
            // Handle the structure used in document creation: ['content' => string]
            if (isset($body['content']) && is_string($body['content'])) {
                $body = $body['content'];
            } else {
                // If the array doesn't contain a 'content' key, we can't use it
                return false;
            }
        }

        // Now $body should be a string; if not, bail out
        if (!is_string($body)) {
            return false;
        }

        $body = strtolower($body);

        $keywords = Cache::remember(
            'classification.body_keywords',
            self::CACHE_TTL,
            fn() => ClassificationKeyword::where('type', 'body')->pluck('keyword')->all(),
        );

        foreach ($keywords as $keyword) {
            if (str_contains($body, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function extractDomain(string $sender): string
    {
        // Handles: "Codecademy <learn@itr.mail.codecademy.com>"
        if (preg_match('/<(.+?)>/', $sender, $matches)) {
            $email = $matches[1];
        } else {
            $email = $sender;
        }

        $parts = explode('@', $email);

        return $parts[1] ?? '';
    }
}
