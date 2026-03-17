<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EnsureEventsTableCommand extends Command
{
    protected $signature = 'isata:ensure-events-table';

    protected $description = 'Create the events table if it does not exist (run when migrations are stuck or table is missing)';

    public function handle(): int
    {
        if (Schema::hasTable('events')) {
            $this->info('The events table already exists.');
            return self::SUCCESS;
        }

        $this->info('Creating events table...');

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('date');
            $table->time('time')->nullable();
            $table->string('venue')->nullable();
            $table->string('meeting_link')->nullable();
            $table->string('status')->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        $this->info('Events table created successfully. You can create events in the app now.');

        return self::SUCCESS;
    }
}
