<?php
namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class system_settings extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("INSERT INTO `system_settings` (`id`, `setting_label`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'USD price used by System', 'USD_INR', '74.14', '2021-06-20 11:18:03', '2021-06-20 11:18:03'),
(2, 'Coins will used for price alert', 'coins', '[\"XRP\",\"DOGE\",\"BAT\",\"BTC\",\"BTT\",\"XLM\",\"DENT\",\"NEO\",\"LTC\",\"SYLO\",\"EOS\",\"TRX\",\"DBC\",\"CAS\",\"TEL\",\"BNS\",\"GAS\",\"XEM\",\"REQ\",\"BNB\",\"ETH\",\"ADA\",\"PHB\",\"OM\",\"SHIB\",\"THETA\",\"MATIC\",\"OMG\",\"CHR\",\"CHZ\",\"ENJ\",\"VET\"]', '2021-06-20 10:40:34', '2021-06-20 10:40:34'),
(8, 'Bot Trading Coin', 'trade_coin', 'DOGE', '2021-06-20 10:40:34', '2021-06-20 10:40:34'),
(9, '% Change for coin price alert Up', 'BTC_UP', '2', '2021-06-20 10:40:34', '2021-06-20 10:40:34'),
(10, '% Change for coin price alert down', 'BTC_DOWN', '-2', '2021-06-20 10:40:34', '2021-06-20 10:40:34'),
(14, 'Telegram token value used for alert system', 'telegram_token', 'bot1715721766:AAH-s-T39RxY17a5lJ4NZJhnVo_p1ZsUujM', '2021-06-20 10:40:34', '2021-06-20 10:40:34'),
(15, 'Telegram Chat Value for notification', 'telegram_chat', '-1001217565625', '2021-06-20 10:40:34', '2021-06-20 10:40:34'),
(16, 'Changes in for 2 hr for check order it or not', 'change_two_hours', '20', '2021-06-20 10:40:34', '2021-06-20 10:40:34'),
(17, 'Time Value for place order % changes in minutes', 'up_side_minutes', '10', '2021-06-20 10:40:34', '2021-06-20 10:40:34'),
(18, 'Bot status o for disable and 1 for Enable', 'run_the_bot', '0', '2021-06-20 10:40:34', '2021-06-20 10:40:34'),
(21, 'Strategy  (0= bear,1 =bullish , 2= Percentage ,3 =Grid)', 'strategy_value', '2', '2021-06-20 10:40:34', '2021-06-20 10:40:34');
");
    }
}
