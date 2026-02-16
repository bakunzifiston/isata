<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
        });

        // Update existing org roles: organization_admin -> admin, organization_member -> staff
        DB::table('users')
            ->where('role', 'organization_admin')
            ->update(['role' => 'admin']);
        DB::table('users')
            ->where('role', 'organization_member')
            ->update(['role' => 'staff']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
    }
};
