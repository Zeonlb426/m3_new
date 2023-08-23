<?php

declare(strict_types=1);

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
        Schema::create('manually_ran_scheduled_tasks', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitored_scheduled_task_id')
                ->references('id')
                ->on('monitored_scheduled_tasks');
            $table->string('command');
            $table->text('output')->nullable();
            $table->integer('exit_code')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manually_ran_scheduled_tasks');
    }
};
