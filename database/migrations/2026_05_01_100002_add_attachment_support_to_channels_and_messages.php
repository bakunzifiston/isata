<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('channels') && ! Schema::hasColumn('channels', 'supports_attachment')) {
            Schema::table('channels', function (Blueprint $table) {
                $table->boolean('supports_attachment')->default(false)->after('supports_audio');
            });
        }

        if (Schema::hasTable('channels')) {
            DB::table('channels')->whereIn('slug', ['email', 'sms'])->update(['supports_attachment' => true]);
        }

        if (Schema::hasTable('messages') && ! Schema::hasColumn('messages', 'attachment_file')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->string('attachment_file')->nullable()->after('audio_file');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('messages') && Schema::hasColumn('messages', 'attachment_file')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropColumn('attachment_file');
            });
        }

        if (Schema::hasTable('channels') && Schema::hasColumn('channels', 'supports_attachment')) {
            Schema::table('channels', function (Blueprint $table) {
                $table->dropColumn('supports_attachment');
            });
        }
    }
};
