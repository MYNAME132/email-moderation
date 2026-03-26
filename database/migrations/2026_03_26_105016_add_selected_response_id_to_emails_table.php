<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->uuid('selected_response_id')
                ->nullable()
                ->after('response_decision');

            $table->foreign('selected_response_id')
                ->references('id')
                ->on('suggested_responses')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->dropForeign(['selected_response_id']);
            $table->dropColumn('selected_response_id');
        });
    }
};
