<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('sender');
            $table->string('receiver');
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->json('response')->nullable();
            $table->string('status')->default('pending');
            $table->string('response_status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emails');
    }
};