<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classification_keywords', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('keyword');
            $table->string('type'); // 'subject' | 'body'
            $table->timestamps();

            $table->unique(['keyword', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classification_keywords');
    }
};
