<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Strategy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('strategy_data', function (Blueprint $table) {
            $table->id();
            $table->string('strategy_name');
            $table->float('percentage_up',11,2);
            $table->float('percentage_down',11,2);
            $table->integer('time_interval');
            $table->integer('order_repet');
            $table->integer('order_again_time');
            $table->integer('stop_loss');
            $table->integer('strategy_key');
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
        Schema::drop('strategy_data');
    }
}
