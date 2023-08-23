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
        Schema::create('user_total_credits', function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
            ;
            $table->unsignedInteger('count_total')->default(0);
            $table->unsignedInteger('count_register')->default(0);
            $table->unsignedInteger('count_likes')->default(0);
            $table->unsignedInteger('count_works')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_total_credits');
    }
};
