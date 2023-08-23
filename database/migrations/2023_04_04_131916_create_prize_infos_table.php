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
        Schema::create('prize_infos', function (Blueprint $table) {
            $table->id();

            $table->jsonb('titles_content')->nullable();
            $table
                ->foreignId('competition_id')
                ->unique()
                ->references('id')
                ->on('competitions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete()
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
        Schema::dropIfExists('prize_infos');
    }
};
