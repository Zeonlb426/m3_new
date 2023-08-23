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
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('title')->index();
            $table->string('code', 5)->unique();
        });

        Schema::table('users', function(Blueprint $table) {
            $table
                ->foreignId('region_id')
                ->nullable()
                ->references('id')
                ->on('regions')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
            ;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('region_id');
        });

        Schema::dropIfExists('regions');
    }
};
