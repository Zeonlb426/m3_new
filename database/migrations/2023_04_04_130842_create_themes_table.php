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
        Schema::create('themes', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('description');
            $table->string('tile_size')->default(TileSize::SMALL->value);
        });

        Schema::create('lead_theme', function(Blueprint $table) {
            $table
                ->foreignId('theme_id')
                ->references('id')
                ->on('themes')
                ->cascadeOnUpdate()
                ->cascadeOnDelete()
            ;
            $table
                ->foreignId('lead_id')
                ->references('id')
                ->on('leads')
                ->cascadeOnUpdate()
                ->cascadeOnDelete()
            ;

            $table->unique(['theme_id', 'lead_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lead_theme');
        Schema::dropIfExists('themes');
    }
};
