<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('supports_subject')->default(true);
            $table->boolean('supports_audio')->default(false);
            $table->timestamps();
        });

        DB::table('channels')->insert([
            ['name' => 'Email', 'slug' => 'email', 'supports_subject' => true, 'supports_audio' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SMS', 'slug' => 'sms', 'supports_subject' => false, 'supports_audio' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Beep Call', 'slug' => 'beep_call', 'supports_subject' => false, 'supports_audio' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Social Media', 'slug' => 'social_media', 'supports_subject' => true, 'supports_audio' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
