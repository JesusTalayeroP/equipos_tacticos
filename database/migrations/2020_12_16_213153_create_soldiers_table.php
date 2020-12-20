<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSoldiersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('soldiers', function (Blueprint $table) {

            $table->id();

            $table->string('name', 50);

            $table->string('surname', 100);

            $table->date('birthdate');

            $table->date('incorporation_date');

            $table->string('badge_number',5)->unique();

            $table->enum('rank', ['soldier', 'sergeant', 'captain']);

            $table->enum('state', ['active', 'retired', 'died']);

            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('soldiers');
    }
}
