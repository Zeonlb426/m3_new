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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('visible_status')->default(false)->index();
            $table->longText('description')->nullable();
            $table->integer('order_column')->default(0)->index();
        });

        Schema::create('course_lead', function(Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')
                ->references('id')
                ->on('courses')
                ->cascadeOnUpdate()
                ->cascadeOnDelete()
            ;
            $table->foreignId('lead_id')
                ->references('id')
                ->on('leads')
                ->cascadeOnUpdate()
                ->cascadeOnDelete()
            ;
            $table->unique(['course_id', 'lead_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course_lead');
        Schema::dropIfExists('courses');
    }
};
