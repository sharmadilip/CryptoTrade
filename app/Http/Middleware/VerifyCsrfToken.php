<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */

    protected $except = [
        '/main_thread_alog',
        '/main_thread_binance_btc_two_minutes',
        '/binance_price_rising_alert',
        '/binance_tiker_data',
        '/insert_price_to_db_every_minute',
        '/test_block_for_every_hook'
    ];
}
