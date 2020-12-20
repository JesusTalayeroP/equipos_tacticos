<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('missions', function (Blueprint $table) {
            
            $table->id();

            $table->text('description');

            $table->enum('priority', ['normal', 'urgent', 'critic']);

            $table->date('register_date');

            $table->enum('state', ['pending', 'in_progress', 'completed', 'failed'])->default('pending');

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
        Schema::dropIfExists('missions');
    }
}
