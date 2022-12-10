<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id()->nullable(false)->autoIncrement();
            $table->char('uuid', 36)->comment("(DC2Type:guid)");
            $table->date('date')->comment("(DC2Type:dateImmutable)");
            $table->integer('quantity')->nullable(false)->default(0);
            $table->tinyInteger('arrival_allowed')->nullable(false);
            $table->tinyInteger('departure_allowed')->nullable(false);
            $table->integer('minimum_stay')->nullable(false);
            $table->integer('maximum_stay')->nullable(false);
            $table->unsignedInteger('version')->nullable(false)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('availabilities');
    }
};
