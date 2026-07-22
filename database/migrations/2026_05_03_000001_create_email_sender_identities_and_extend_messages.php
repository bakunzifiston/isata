<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('email_sender_identities')) {
            Schema::create('email_sender_identities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
                $table->string('label')->nullable();
                $table->string('from_name');
                $table->string('from_email');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('messages')) {
            return;
        }

        Schema::table('messages', function (Blueprint $table) {
            if (! Schema::hasColumn('messages', 'sender_identity_id')) {
                $table->foreignId('sender_identity_id')
                    ->nullable()
                    ->constrained('email_sender_identities')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('messages', 'sender_custom_name')) {
                $table->string('sender_custom_name')->nullable();
            }

            if (! Schema::hasColumn('messages', 'sender_custom_email')) {
                $table->string('sender_custom_email')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                if (Schema::hasColumn('messages', 'sender_custom_email')) {
                    $table->dropColumn('sender_custom_email');
                }
                if (Schema::hasColumn('messages', 'sender_custom_name')) {
                    $table->dropColumn('sender_custom_name');
                }
                if (Schema::hasColumn('messages', 'sender_identity_id')) {
                    $table->dropConstrainedForeignId('sender_identity_id');
                }
            });
        }

        Schema::dropIfExists('email_sender_identities');
    }
};
