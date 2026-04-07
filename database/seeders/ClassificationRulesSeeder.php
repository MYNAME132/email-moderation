<?php

namespace Database\Seeders;

use App\Models\BlockedDomain;
use App\Models\ClassificationKeyword;
use Illuminate\Database\Seeder;

class ClassificationRulesSeeder extends Seeder
{
    public function run(): void
    {
        $blockedDomains = [
            'codecademy.com',
            'greenhouse.io',
        ];

        foreach ($blockedDomains as $domain) {
            BlockedDomain::firstOrCreate(['domain' => $domain]);
        }

        $subjectKeywords = [
            'notification',
            'newsletter',
            'update',
            'policy',
            'privacy',
            'digest',
        ];

        foreach ($subjectKeywords as $keyword) {
            ClassificationKeyword::firstOrCreate(['keyword' => $keyword, 'type' => 'subject']);
        }

        $bodyKeywords = [
            'unsubscribe',
            'manage preferences',
            'privacy policy',
            'email settings',
            'manage my consent',
        ];

        foreach ($bodyKeywords as $keyword) {
            ClassificationKeyword::firstOrCreate(['keyword' => $keyword, 'type' => 'body']);
        }
    }
}
