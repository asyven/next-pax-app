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
        Schema::create('prices', function (Blueprint $table) {
            $table->id()->nullable(false)->autoIncrement();
            $table->char('property_id', 36)->comment("(DC2Type:guid)");
            $table->integer('duration')->nullable(false);
            $table->integer('amount')->nullable(false);
            $table->char('currency', 3);
            $table->char('persons', 255)->comment("(DC2Type:integerArray)");
            $table->char('weekdays', 255)->comment("(DC2Type:integerArray)");
            $table->integer('minimum_stay')->nullable(false);
            $table->integer('maximum_stay')->nullable(false);
            $table->integer('extra_person_price')->nullable(false);
            $table->char('extra_person_price_currency', 3);
            $table->date('period_from')->comment("(DC2Type:dateWrap)");
            $table->date('period_till')->comment("(DC2Type:dateWrap)");
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
        Schema::dropIfExists('prices');
    }
};
