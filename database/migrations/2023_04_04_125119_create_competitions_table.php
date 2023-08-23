<?php

declare(strict_types=1);

use App\Enums\Competition\TileSize;
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
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('title');
            $table->string('slug')->index();
            $table->string('period')->nullable();
            $table->longText('content')->nullable();
            $table->longText('short_content');
            $table->jsonb('titles_content')->nullable();
            $table->string('tile_size')->default(TileSize::SMALL->value);
            $table->boolean('visible_status')->default(false)->index();
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
        Schema::dropIfExists('competitions');
    }
};
