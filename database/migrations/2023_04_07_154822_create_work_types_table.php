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
        Schema::create('work_types', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('slug')->nullable();
            $table->jsonb('formats')->nullable();
            $table->boolean('visible_status')->default(false)->index();
        });

        Schema::create('competition_work_type', function(Blueprint $table) {
            $table
                ->foreignId('competition_id')
                ->references('id')
                ->on('competitions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete()
            ;
            $table->foreignId('work_type_id')
                ->references('id')
                ->on('work_types')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
            ;

            $table->unique(['competition_id', 'work_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('competition_work_type');
        Schema::dropIfExists('work_types');
    }
};
