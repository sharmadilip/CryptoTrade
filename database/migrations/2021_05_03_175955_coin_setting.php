<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CoinSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin_setting', function (Blueprint $table) {
            $table->id();
            $table->string('coin_name');
            $table->float('buy_deff',14, 6);
            $table->float('sell_deff',14, 6);
            $table->float('bid_diffrance',14, 6);
            $table->float('add_value',14, 6);
            $table->float('slot_value',10,2);
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
        Schema::drop('coin_setting');
    }
}
