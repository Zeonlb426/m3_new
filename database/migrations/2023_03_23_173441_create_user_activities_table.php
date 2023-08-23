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
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedInteger('action_type')->index();

            $table->string("interacted_type", 26)->nullable();
            $table->unsignedBigInteger("interacted_id")->nullable();

            $table
                ->foreignId('user_id')
                ->nullable()
                ->index()
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete()
            ;
            $table->unsignedInteger('point')->default(0);
            $table->unsignedInteger('credits')->default(1);
            $table->index(["interacted_type", "interacted_id"]);
            $table->unique(['user_id', 'interacted_id', 'interacted_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_activities');
    }
};
