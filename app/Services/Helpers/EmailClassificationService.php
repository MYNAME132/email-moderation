<?php

namespace App\Services\Helpers;

use App\Models\Email;
use App\Enums\ResponseDecisionEnum;

class EmailClassificationService
{
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

        $blocked = [
            //'linkedin.com',
            'codecademy.com',
            'greenhouse.io',
        ]; // can be add to admin dashbord or smth like this will work as well

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

        $keywords = [
            'notification',
            'newsletter',
            'update',
            'policy',
            'privacy',
            'digest',
        ];

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

        $keywords = [
            'unsubscribe',
            'manage preferences',
            'privacy policy',
            'email settings',
            'manage my consent',
        ];

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
