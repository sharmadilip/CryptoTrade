const dotenv = require('dotenv');
const bitbnsApi = require('bitbns');
const fs = require('fs');
var mysql = require('mysql');
var trade_coin='';
dotenv.config();
const bitbns = new bitbnsApi({
    apiKey :  process.env.BITBNS_KEY_PUBLIC,
    apiSecretKey : process.env.BITBNS_KEY_SECRET
});
var con = mysql.createConnection({
    host: process.env.DB_HOST,
    user: process.env.DB_USERNAME,
    password: process.env.DB_PASSWORD,
    database:process.env.DB_DATABASE
});
function get_trade_coin() {
    con.connect(function (err) {
        if (err) throw err;
        var sql = "SELECT setting_value FROM system_settings WHERE setting_key='trade_coin'";
        con.query(sql, function (err, result) {
            if (err) throw err;
            var my_result = Object.values(JSON.parse(JSON.stringify(result)));
            trade_coin = my_result[0]['setting_value'];

            const socket = bitbns.getOrderBookSocket(trade_coin, 'INR')
            var price_data= {"highest_buy_bid": 0,"lowest_sell_bid": 0};
            socket.on('news', res => {
                try {
                    const data = JSON.parse(res)
                    if(data['type']=="sellList")
                    {  var price_get=JSON.parse(data['data']);
                        price_data['lowest_sell_bid']=price_get[0]['rate']

                    }
                    if(data['type']=="buyList")
                    {   var price_sell=JSON.parse(data['data']);
                        price_data['highest_buy_bid']=price_sell[0]['rate']
                    }
                    fs.writeFileSync('storage/json/price.json', JSON.stringify(price_data));

                } catch (e) {
                    console.log('Error in the Stream', e)
                }
            })
        });

    });
    return trade_coin;
}
get_trade_coin();

