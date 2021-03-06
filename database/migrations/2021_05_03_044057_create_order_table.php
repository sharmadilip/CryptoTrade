<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_table', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id');
            $table->float('quantity');
            $table->float('price');
            $table->string('coin');
            $table->integer('order_status')->comment("0 for not complete 1 for complete");
            $table->integer('order_type')->comment("0 for buy 1 for sell");
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
        Schema::dropIfExists('order_table');
    }
}
