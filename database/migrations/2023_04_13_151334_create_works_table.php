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
        Schema::create('works', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedSmallInteger('status')->default(0)->index();
            $table->integer('order_column')->default(0)->index();
            $table->unsignedInteger('likes_total_count')->default(0)->index();

            $table
                ->foreignId('user_id')
                ->index()
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
            ;
            $table
                ->foreignId('author_id')
                ->index()
                ->references('id')
                ->on('work_authors')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
            ;
            $table
                ->foreignId('competition_id')
                ->index()
                ->references('id')
                ->on('competitions')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
            ;
            $table
                ->foreignId('theme_id')
                ->nullable()
                ->index()
                ->references('id')
                ->on('themes')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
            ;
            $table->foreignId('work_type_id')
                ->references('id')
                ->on('work_types')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
            ;

            $table->string('work_video')->nullable();
            $table->text('work_text')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('works');
    }
};
