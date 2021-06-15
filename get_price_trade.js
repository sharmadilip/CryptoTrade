const dotenv = require('dotenv');
const bitbnsApi = require('bitbns');
const fs = require('fs');
var trade_coin='';
dotenv.config();
const bitbns = new bitbnsApi({
    apiKey :  process.env.BITBNS_KEY_PUBLIC,
    apiSecretKey : process.env.BITBNS_KEY_SECRET
});

var args = process.argv.slice(2);
trade_coin=args[0];
console.log(args)
function get_trade_coin() {

            try {
                const files_data_get = fs.readFileSync('storage/json/'+trade_coin+'_price.json', 'utf8')

            }
            catch (errr) {
                fs.writeFile('storage/json/'+trade_coin+'_price.json', '{"highest_buy_bid": 0,"lowest_sell_bid": 0}', err => {
                    if (err) {
                        console.error(err)

                    }
                })
            }

            const socket = bitbns.getOrderBookSocket(trade_coin, 'INR')
            var price_data= {"highest_buy_bid": 0,"lowest_sell_bid": 0};
            socket.on('news', res => {
                try {
                    const data = JSON.parse(res)
                    console.log(data);
                    if(data['type']=="sellList")
                    {  var price_get=JSON.parse(data['data']);
                        price_data['lowest_sell_bid']=price_get[0]['rate']

                    }
                    if(data['type']=="buyList")
                    {   var price_sell=JSON.parse(data['data']);
                        price_data['highest_buy_bid']=price_sell[0]['rate']
                    }
                    fs.writeFileSync('storage/json/'+trade_coin+'_price.json', JSON.stringify(price_data));

                } catch (e) {
                    console.log('Error in the Stream', e)
                }
            })

}
get_trade_coin();

