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
            $table->index('sender');
            $table->index('receiver');
            $table->index('status');
            $table->index('response_status');
            $table->index('response_decision');
            $table->index(['sender', 'receiver']);
            $table->index(['status', 'response_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->dropIndex(['sender']);
            $table->dropIndex(['receiver']);
            $table->dropIndex(['status']);
            $table->dropIndex(['response_status']);
            $table->dropIndex(['response_decision']);
            $table->dropIndex(['sender', 'receiver']);
            $table->dropIndex(['status', 'response_status']);
        });
    }
};
