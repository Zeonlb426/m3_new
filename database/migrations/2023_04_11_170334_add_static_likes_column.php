<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('master_classes', function(Blueprint $table) {
            $table->unsignedInteger('likes_total_count')->default(0)->index();
        });
        Schema::table('news', function(Blueprint $table) {
            $table->unsignedInteger('likes_total_count')->default(0)->index();
        });
        Schema::table('success_histories', function(Blueprint $table) {
            $table->unsignedInteger('likes_total_count')->default(0)->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('master_classes', function(Blueprint $table) {
            $table->dropColumn('likes_total_count');
        });
        Schema::table('news', function(Blueprint $table) {
            $table->dropColumn('likes_total_count');
        });
        Schema::table('success_histories', function(Blueprint $table) {
            $table->dropColumn('likes_total_count');
        });
    }
};
