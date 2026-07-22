<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('messages')) {
            return;
        }

        if (! Schema::hasColumn('messages', 'sender_user_id')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->foreignId('sender_user_id')
                    ->nullable()
                    ->after('channel_id')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('messages') || ! Schema::hasColumn('messages', 'sender_user_id')) {
            return;
        }

        Schema::table('messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sender_user_id');
        });
    }
};
