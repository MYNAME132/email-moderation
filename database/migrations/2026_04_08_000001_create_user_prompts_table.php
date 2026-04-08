<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_prompts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('email_id');
            $table->text('prompt');
            $table->text('response')->nullable();
            $table->timestamps();
            $table->index('email_id');
            $table->index('prompt');
            $table->foreign('email_id')
                ->references('id')
                ->on('emails')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_prompts');
    }
};
