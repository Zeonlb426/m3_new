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
        Schema::table('competition_master_class', function (Blueprint $table): void {
            $table->jsonb('theme_ids')->nullable(false)->default('[]');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competition_master_class', function (Blueprint $table): void {
            $table->dropColumn('theme_ids');
        });
    }
};
