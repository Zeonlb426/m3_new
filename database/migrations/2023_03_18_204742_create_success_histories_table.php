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
        Schema::create('success_histories', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('short_title')->nullable();
            $table->string('title');
            $table->string('video_link')->nullable();
            $table->boolean('visible_status')->default(false)->index();
            $table->text('short_description');
            $table->longText('description');
            $table->integer('order_column')->default(0)->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('success_histories');
    }
};
