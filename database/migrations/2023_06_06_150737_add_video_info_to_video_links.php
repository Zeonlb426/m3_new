<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table): void {
            $table->unsignedSmallInteger('video_type')->nullable();
            $table->string('video_id', 50)->nullable();
        });
        Schema::table('master_classes', function (Blueprint $table): void {
            $table->unsignedSmallInteger('video_type')->nullable();
            $table->string('video_id', 50)->nullable();
        });
        Schema::table('success_histories', function (Blueprint $table): void {
            $table->unsignedSmallInteger('video_type')->nullable();
            $table->string('video_id', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table): void {
            $table->dropColumn(['video_type', 'video_id']);
        });
        Schema::table('master_classes', function (Blueprint $table): void {
            $table->dropColumn(['video_type', 'video_id']);
        });
        Schema::table('success_histories', function (Blueprint $table): void {
            $table->dropColumn(['video_type', 'video_id']);
        });
    }
};
