<?php

namespace App\Services\Helpers;

use Illuminate\Support\Facades\Log;

class EmailParser
{
    private function extractText(string $body): string
    {
        $body = quoted_printable_decode($body);
        $text = html_entity_decode($body, ENT_QUOTES | ENT_HTML5);
        $text = strip_tags($text);
        $text = preg_replace('/https?:\/\/[^\s]+/', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    private function extractLinks(string $body): array
    {
        $links = [];

        preg_match_all('/href=["\'](https?:\/\/[^"\']+)["\']/i', $body, $matches);
        if (!empty($matches[1])) {
            $links = $matches[1];
        }

        preg_match_all('/https?:\/\/[^\s"<>()]+/', $body, $matches2);
        $links = array_merge($links, $matches2[0] ?? []);

        return array_values(array_unique($links));
    }

    private function findFirstTextPart($structure, string $partNumber = ''): ?array
    {
        if ($structure->type == 0 && in_array(strtolower($structure->subtype), ['plain', 'html'])) {
            return [
                'partNumber' => $partNumber,
                'encoding'   => $structure->encoding ?? 0
            ];
        }

        if (isset($structure->parts)) {
            $prefix = $partNumber ? $partNumber . '.' : '';
            foreach ($structure->parts as $index => $subpart) {
                $result = $this->findFirstTextPart($subpart, $prefix . ($index + 1));
                if ($result) {
                    return $result;
                }
            }
        }

        return null;
    }

    private function fetchPart($conn, string $emailNumber, string $partNumber): string
    {
        return imap_fetchbody($conn, $emailNumber, $partNumber);
    }

    private function decodeBody(string $body, string $encoding): string
    {
        switch ($encoding) {
            case ENC7BIT:
            case ENC8BIT:
                return $body;
            case ENCBINARY:
                return imap_binary($body);
            case ENCBASE64:
                return imap_base64($body);
            case ENCQUOTEDPRINTABLE:
                return quoted_printable_decode($body);
            default:
                return $body;
        }
    }

    /**
     * Get the raw body of the email, properly decoded.
     */
    private function getRawBody($conn, string $emailNumber): string
    {
        $structure = imap_fetchstructure($conn, $emailNumber);
        Log::debug('Email structure', ['structure' => json_encode($structure)]);

        $part = $this->findFirstTextPart($structure, '');
        if ($part) {
            $body = $this->fetchPart($conn, $emailNumber, $part['partNumber']);
            return $this->decodeBody($body, $part['encoding']);
        }

        Log::debug('Selected MIME part', $part ?? []);

        $body = imap_fetchbody($conn, $emailNumber, '1.1');
        if (empty($body)) {
            $body = imap_fetchbody($conn, $emailNumber, '1');
        }
        return $this->decodeBody($body, $structure->encoding ?? 0);
    }

    /**
     * Public method: parse an email and return cleaned body and links.
     *
     * @param resource $conn IMAP connection
     * @param string $emailNumber
     * @return array ['body' => string, 'links' => array]
     */
    public function parse($conn, string $emailNumber): array
    {
        $rawBody = $this->getRawBody($conn, $emailNumber);
        Log::debug('Decoded body snippet', ['body' => substr($rawBody, 0, 500)]);

        return [
            'body'  => $this->extractText($rawBody),
            'links' => $this->extractLinks($rawBody),
        ];
    }
}
