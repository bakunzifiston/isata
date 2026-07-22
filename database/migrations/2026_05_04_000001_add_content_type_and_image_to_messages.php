<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('messages')) {
            return;
        }

        if (! Schema::hasColumn('messages', 'content_type')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->string('content_type', 20)->default('text')->after('content');
            });
        }

        if (! Schema::hasColumn('messages', 'content_image')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->string('content_image')->nullable()->after('content_type');
            });
        }

        DB::table('messages')->whereNull('content_type')->update(['content_type' => 'text']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('messages')) {
            return;
        }

        Schema::table('messages', function (Blueprint $table) {
            if (Schema::hasColumn('messages', 'content_image')) {
                $table->dropColumn('content_image');
            }
            if (Schema::hasColumn('messages', 'content_type')) {
                $table->dropColumn('content_type');
            }
        });
    }
};
