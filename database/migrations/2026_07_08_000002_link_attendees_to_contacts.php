<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendees', function (Blueprint $table) {
            $table->foreignId('contact_id')->nullable()->after('event_id')->constrained()->cascadeOnDelete();
        });

        if (Schema::hasTable('contacts') && Schema::hasTable('attendees')) {
            $attendees = DB::table('attendees')
                ->join('events', 'attendees.event_id', '=', 'events.id')
                ->select(
                    'attendees.id as attendee_id',
                    'events.organization_id',
                    'attendees.name',
                    'attendees.email',
                    'attendees.phone',
                    'attendees.organization as company'
                )
                ->get();

            foreach ($attendees as $row) {
                $contactId = DB::table('contacts')->where([
                    'organization_id' => $row->organization_id,
                    'email' => $row->email,
                ])->value('id');

                if (! $contactId) {
                    $contactId = DB::table('contacts')->insertGetId([
                        'organization_id' => $row->organization_id,
                        'name' => $row->name,
                        'email' => strtolower($row->email),
                        'phone' => $row->phone,
                        'company' => $row->company,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('attendees')->where('id', $row->attendee_id)->update([
                    'contact_id' => $contactId,
                ]);
            }
        }

        Schema::table('attendees', function (Blueprint $table) {
            $table->dropColumn(['name', 'email', 'phone', 'organization']);
            $table->unique(['event_id', 'contact_id']);
        });
    }

    public function down(): void
    {
        Schema::table('attendees', function (Blueprint $table) {
            $table->dropUnique(['event_id', 'contact_id']);
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('organization')->nullable();
        });

        if (Schema::hasTable('contacts') && Schema::hasTable('attendees')) {
            $rows = DB::table('attendees')
                ->join('contacts', 'attendees.contact_id', '=', 'contacts.id')
                ->select('attendees.id as attendee_id', 'contacts.name', 'contacts.email', 'contacts.phone', 'contacts.company')
                ->get();

            foreach ($rows as $row) {
                DB::table('attendees')->where('id', $row->attendee_id)->update([
                    'name' => $row->name,
                    'email' => $row->email,
                    'phone' => $row->phone,
                    'organization' => $row->company,
                ]);
            }
        }

        Schema::table('attendees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('contact_id');
        });
    }
};
