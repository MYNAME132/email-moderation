<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {

            $table->uuid('id')->primary();

            $table->uuid('email_id')->unique();

            $table->jsonb('body');

            $table->timestamps();

            $table->foreign('email_id')
                ->references('id')
                ->on('emails')
                ->cascadeOnDelete();

            $table->index('email_id');
            $table->index('created_at');
        });

        DB::statement('CREATE INDEX documents_body_gin ON documents USING GIN (body)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
