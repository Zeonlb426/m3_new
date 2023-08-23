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
        Schema::table('user_activities', function (Blueprint $table): void {
            $table->softDeletes();
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->string('interacted_type', 26)->nullable(false)->change();
            $table->unsignedBigInteger('interacted_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_activities', function (Blueprint $table): void {
            $table->dropSoftDeletes();
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->string('interacted_type', 26)->nullable()->change();
            $table->unsignedBigInteger('interacted_id')->nullable()->change();
        });
    }
};
