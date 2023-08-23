<?php

declare(strict_types=1);

use App\Enums\MasterClass\AdditionalSign;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new  class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_classes', function (Blueprint $table) {
            $markups = AdditionalSign::values();

            $table->id();
            $table->timestamps();
            $table->string('title');
            $table->string('video_link')->nullable();
            $table->foreignId('age_group_id')
                ->references('id')
                ->on('age_groups')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
            ;
            $table->foreignId('lead_id')
                ->nullable()
                ->references('id')
                ->on('leads')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
            ;
            $table
                ->jsonb('additional_signs')
                ->index()
                ->default(\json_encode(
                    \array_combine(
                        $markups,
                        \array_fill(0, count($markups), false)
                    )
                ))
            ;
            $table->boolean('visible_status')->default(false)->index();
            $table->longText('content')->nullable();
            $table->integer('order_column')->default(0)->index();
        });

        Schema::create('course_master_class', function(Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')
                ->references('id')
                ->on('courses')
                ->cascadeOnUpdate()
                ->cascadeOnDelete()
            ;
            $table->foreignId('master_class_id')
                ->references('id')
                ->on('master_classes')
                ->cascadeOnUpdate()
                ->cascadeOnDelete()
            ;
            $table->unique(['course_id', 'master_class_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course_master_class');
        Schema::dropIfExists('master_classes');
    }
};
