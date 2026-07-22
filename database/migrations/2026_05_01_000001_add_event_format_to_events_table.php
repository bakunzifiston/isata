<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('events')) {
            return;
        }

        if (! Schema::hasColumn('events', 'event_format')) {
            Schema::table('events', function (Blueprint $table) {
                $table->string('event_format', 20)->nullable()->after('time');
            });
        }

        foreach (DB::table('events')->whereNull('event_format')->cursor() as $row) {
            $format = (! empty($row->meeting_link) && empty($row->venue))
                ? 'online'
                : 'physical';
            DB::table('events')->where('id', $row->id)->update(['event_format' => $format]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('events') || ! Schema::hasColumn('events', 'event_format')) {
            return;
        }

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('event_format');
        });
    }
};
