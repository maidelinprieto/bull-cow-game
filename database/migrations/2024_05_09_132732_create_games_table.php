<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->increments('id');
			$table->string('name');
			$table->integer('age');
            $table->integer('random_number');
            $table->date('time_to_open');
            $table->date('time_to_close');
            $table->integer('attempts');
            $table->boolean('is_won');
            $table->string('eval');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
