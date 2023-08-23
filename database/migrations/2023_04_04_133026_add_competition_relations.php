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
        Schema::create('competition_age_group', function (Blueprint $table) {
            $table
                ->foreignId('competition_id')
                ->references('id')
                ->on('competitions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete()
            ;
            $table->foreignId('age_group_id')
                ->references('id')
                ->on('age_groups')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
            ;

            $table->boolean('visible_status')->default(false);
            $table->unique(['competition_id', 'age_group_id']);
        });
        Schema::create('competition_partner', function (Blueprint $table) {
            $table
                ->foreignId('competition_id')
                ->references('id')
                ->on('competitions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete()
            ;
            $table
                ->foreignId('partner_id')
                ->references('id')
                ->on('partners')
                ->cascadeOnUpdate()
                ->cascadeOnDelete()
            ;
            $table->jsonb('titles_content')->nullable();
            $table->integer('order_column')->default(0)->index();
            $table->boolean('is_main')->default(false);

            $table->unique(['competition_id', 'partner_id']);
        });
        Schema::create('competition_lead', function (Blueprint $table) {
            $table
                ->foreignId('competition_id')
                ->references('id')
                ->on('competitions')
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
            $table->jsonb('titles_content')->nullable();
            $table->integer('order_column')->default(0)->index();

            $table->unique(['competition_id', 'lead_id']);
        });
        Schema::create('competition_master_class', function (Blueprint $table) {
            $table
                ->foreignId('master_class_id')
                ->references('id')
                ->on('master_classes')
                ->cascadeOnUpdate()
                ->cascadeOnDelete()
            ;
            $table
                ->foreignId('competition_id')
                ->references('id')
                ->on('competitions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete()
            ;
            $table->jsonb('titles_content')->nullable();
            $table->boolean('is_main')->default(false);
            $table->integer('order_column')->default(0)->index();

            $table->unique(['competition_id', 'master_class_id']);
        });
        Schema::create('competition_theme', function (Blueprint $table) {
            $table
                ->foreignId('theme_id')
                ->references('id')
                ->on('themes')
                ->cascadeOnUpdate()
                ->cascadeOnDelete()
            ;
            $table
                ->foreignId('competition_id')
                ->references('id')
                ->on('competitions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete()
            ;
            $table->jsonb('titles_content')->nullable();
            $table->integer('order_column')->default(0)->index();

            $table->unique(['competition_id', 'theme_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('competition_theme');
        Schema::drop('competition_master_class');
        Schema::drop('competition_lead');
        Schema::drop('competition_partner');
        Schema::drop('competition_age_group');
    }
};
