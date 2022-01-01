<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="canonical" href="https://crazy-cerf.3-129-42-143.plesk.page/">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Black Dashboard') }}</title>
        <!-- Favicon -->
        <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('black') }}/img/apple-icon.png">
        <link rel="icon" type="image/png" href="{{ asset('black') }}/img/favicon.png">
        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,600,700,800" rel="stylesheet" />
        <link href="https://use.fontawesome.com/releases/v5.0.6/css/all.css" rel="stylesheet">
        <!-- Icons -->
        <link href="{{ asset('black') }}/css/nucleo-icons.css" rel="stylesheet" />
        <!-- CSS -->
        <link href="{{ asset('black') }}/css/black-dashboard.css?v=1.0.0" rel="stylesheet" />
        <link href="{{ asset('black') }}/css/theme.css" rel="stylesheet" />
    </head>
    <body class="{{ $class ?? '' }}">
        @auth()
            <div class="wrapper">
                    @include('layouts.navbars.sidebar')
                <div class="main-panel">
                    @include('layouts.navbars.navbar')

                    <div class="content">
                        @yield('content')
                    </div>

                    @include('layouts.footer')
                </div>
            </div>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        @else
            @include('layouts.navbars.navbar')
            <div class="wrapper wrapper-full-page">
                <div class="full-page {{ $contentClass ?? '' }}">
                    <div class="content">
                        <div class="container">
                            @yield('content')
                        </div>
                    </div>
                    @include('layouts.footer')
                </div>
            </div>
        @endauth

        <script src="{{ asset('black') }}/js/core/jquery.min.js"></script>
        <script src="{{ asset('black') }}/js/core/popper.min.js"></script>
        <script src="{{ asset('black') }}/js/core/bootstrap.min.js"></script>
        <script src="{{ asset('black') }}/js/plugins/perfect-scrollbar.jquery.min.js"></script>
        <!--  Google Maps Plugin    -->
        <!-- Place this tag in your head or just before your close body tag. -->
        {{-- <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_KEY_HERE"></script> --}}
        <!-- Chart JS -->
        {{-- <script src="{{ asset('black') }}/js/plugins/chartjs.min.js"></script> --}}
        <!--  Notifications Plugin    -->
        <script src="{{ asset('black') }}/js/plugins/bootstrap-notify.js"></script>
<script>
    var symbol='';
    @if(isset($symbol))
        symbol="{{$symbol}}";
    @endif
</script>
        <script src="{{ asset('black') }}/js/black-dashboard.min.js?v=1.0.0"></script>
        <script src="{{ asset('black') }}/js/theme.js"></script>


        @stack('js')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>


            jQuery(document).ready(function($) {

                    var token="{{csrf_token()}}";
                    var intervalID=0;
                    var coin_quntity=0;
                    var inr_amount=0;
                    var buy_price=0;
                    var sell_price=0;
                    var current_interval;

                    //----------------------buy sale js form ----------------------------------
                    $("#coin_trade_form #symbol").on("change",function (){
                        symbol=$(this).val();
                       var coin_quntity=0;
                       var inr_amount=0;
                        $.post("{{route("change_coin_trade_price")}}", {"_token":token,"symbol":symbol}, function (responce) {
                        });
                        get_coins_trade_data(symbol);
                        clearInterval(current_interval);
                        current_interval= read_js_price_values(symbol);
                    });

                    function get_coins_trade_data(symbol)
                    {
                        $.post("{{route("coins.buy_sell_form_data")}}", {
                            "_token": token,
                            "symbol": symbol
                        }, function (responce) {
                            var return_data = JSON.parse(responce);
                            if(return_data['coin_trade']) {
                                $("#quantity").val(return_data['coin_trade']['slot_value']);
                                coin_quntity = return_data['coin_blance'];
                                $("#inr_amount").val(return_data['money_value']);
                                inr_amount = return_data['money_value']
                                $("#total_coins").val(return_data['coin_blance']);
                                //$("#buy_shift_dif").val(return_data['coin_trade']['add_value']);
                                //$("#sell_shift_dif").val(return_data['coin_trade']['add_value']);
                            }
                        });
                    }
                    function buy_or_sell(symbol)
                    {
                        $.post("{{route("coins.buy_sell_rate")}}", {"_token":token,"symbol":symbol}, function (responce) {
                            var return_data=JSON.parse(responce);
                            $("#buy_price").val(return_data['lowest_sell_bid']);
                            $("#sell_price").val(return_data['highest_buy_bid']);

                        });
                    }

                    function genrate_notification(message,type)
                    {
                        $.notify({
                            // options
                            message: message
                        },{
                            // settings
                            type: type
                        });
                    }
                //------------------------end buy sale form----------------------------------
                var chart;
                if($("#chart").length>0) {
                    var my_chart_data = [];
                    $.get("{{route("tiker.chart")}}", function (responce) {
                        var my_chart_data = JSON.parse(responce);
                        var options = {
                            series: [{
                                data: my_chart_data
                            }],
                            chart: {
                                type: 'candlestick',
                                height: 350
                            },
                            title: {
                                text: 'Trading Coin '+symbol,
                                align: 'left'
                            },
                            xaxis: {
                                type: 'datetime'
                            },
                            yaxis: {
                                tooltip: {
                                    enabled: true
                                }
                            }
                        };

                        chart = new ApexCharts(document.querySelector("#chart"), options);
                        chart.render();
                    })

                }

                //----------sell or buy ajax function---------
                    if($("#coin_trade_form").length >0) {

                        $(window).bind('keypress', function (e) {
                            if (e.keyCode == 113) {
                                $('#myTabContent [data-key="q"]').trigger('click').addClass('active');
                                setTimeout(function () {
                                    $('#myTabContent [data-key="q"]').removeClass('active');
                                }, 100);

                                //-----------buy code-------------------

                                buy_price = parseFloat($("#buy_price").val());
                                var inr_value = parseFloat($("#inr_amount").val());
                                if (inr_value > 0) {
                                    var quantity = parseFloat(((inr_value / 100) * parseInt($("#inr_percent").val()))).toFixed(2);
                                    $.post("{{route("coins.buy_coins")}}", {
                                        "_token": token,
                                        'quantity': quantity,
                                        "symbol": symbol
                                    }, function (responce) {
                                        var return_data = JSON.parse(responce);
                                        if (return_data['status'] == "1") {

                                            genrate_notification(return_data['data'],'success');

                                        }
                                        else{

                                            genrate_notification(return_data['data'],'danger');
                                            }
                                        get_coins_trade_data(symbol);

                                    });
                                }
                            }
                            if (e.keyCode == 119) {

                                $('#myTabContent>.active [data-key="w"]').trigger('click').addClass('active');
                                setTimeout(function () {
                                    $('#myTabContent>.active [data-key="w"]').removeClass('active');
                                }, 100);
                                //---------sell code-----------------------
                                var coin_total = parseFloat($("#total_coins").val());
                                if (coin_total > 0) {
                                    sell_price = parseFloat($("#sell_price").val());

                                    var quantity = parseFloat(((coin_total / 100) * parseInt($("#coin_percent").val()))).toFixed(2);
                                    $.post("{{route("coins.sell_coins")}}", {
                                        "_token": token,
                                        "rate": sell_price,
                                        'quantity': quantity,
                                        "symbol": symbol
                                    }, function (responce) {
                                        var return_data = JSON.parse(responce);
                                        if (return_data['status'] == "1") {
                                            get_coins_trade_data(symbol);
                                            genrate_notification(return_data['data'],'success');
                                        }
                                        else{
                                            genrate_notification(return_data['data'],'danger');
                                        }

                                    });
                                }
                            }
                            if (e.keyCode == 97) {

                                $('#myTabContent>.active [data-key="a"]').trigger('click').addClass('active');
                                setTimeout(function () {
                                    $('#myTabContent>.active [data-key="a"]').removeClass('active');
                                }, 100);
                                buy_price = parseFloat($("#inr_percent").val());
                                buy_price = buy_price + 5;
                                $("#inr_percent").val((buy_price).toFixed(0));
                                sell_price = parseFloat($("#coin_percent").val());
                                sell_price = sell_price + 5;
                                $("#coin_percent").val((sell_price).toFixed(0));
                            }
                            if (e.keyCode == 115) {
                                $('#myTabContent>.active [data-key="s"]').trigger('click').addClass('active');
                                setTimeout(function () {
                                    $('#myTabContent>.active [data-key="s"]').removeClass('active');
                                }, 100);

                                buy_price = parseFloat($("#inr_percent").val());
                                buy_price = buy_price - 5;
                                $("#inr_percent").val((buy_price).toFixed(0));
                                sell_price = parseFloat($("#coin_percent").val());
                                sell_price = sell_price - 5;
                                $("#coin_percent").val((sell_price).toFixed(0));
                            }
                             if(e.keyCode==99)
                            {
                                    $.post("{{route("coins.clear_all_bids")}}", {
                                        "_token": token,
                                        "symbol": symbol
                                    }, function (responce) {
                                        get_coins_trade_data(symbol);
                                        genrate_notification(responce,'success');

                                    });
                                }
                             if(e.keyCode==114)
                             {
                                 $("#reload_data_buy_sell").click();
                             }

                        });


                    }
                    function read_js_price_values(symbol)
                    {  var intervel_count=0;
                       var my_intervel= setInterval(  function(){  $.getJSON( 'storage/json/'+symbol+'_price.json?count='+intervel_count, function(responce) {
                            var data =JSON.parse(JSON.stringify(responce));
                     $("#alert_content_action_tr").html('<td>'+symbol+'</td><td>'+data['highest_buy_bid']+'</td><td>'+data['lowest_sell_bid']+'</td>');
                                intervel_count=  intervel_count+1;
                        })},5000
                     );
                       return my_intervel;
                    }
                   $("#reload_data_buy_sell").on("click",function (){
                       if(symbol!='') {
                           get_coins_trade_data(symbol);

                       }
                   })

                  //----key press fun end-------------
                $("#run_main_thread_bot").on("click",function (){
                   $.get("{{route("home.bots_alog")}}",{"_token": token},function (responce){
                       var data=JSON.parse(JSON.stringify(responce))
                       if(data['coin']!='')
                       {
                           genrate_notification("Bot has run successfully",'success');
                       }
                       else {
                           genrate_notification("Error in bot",'danger');
                       }
                   })


                })
                $("#syc_all_data_db").on("click",function (){
                    $.get("{{route("home.sys_all_data")}}", {"_token": token},function (responce){

                        if(responce=="true")
                        {
                            genrate_notification("Data has syc successfully",'success');
                        }
                        else {
                            genrate_notification("Error in data syc",'danger');
                        }
                    })


                })
                $("#reset_boat_data").on("click",function (){
                    $.get("{{route("home.reset_boat_data")}}", {"_token": token},function (responce){

                        if(responce=="true")
                        {
                            genrate_notification("Bot reset successfully",'success');
                        }
                        else {
                            genrate_notification("Error in data syc",'danger');
                        }
                    })


                })
            })
        </script>
        <script>
            $(document).ready(function() {
                $().ready(function() {
                    $sidebar = $('.sidebar');
                    $navbar = $('.navbar');
                    $main_panel = $('.main-panel');

                    $full_page = $('.full-page');

                    $sidebar_responsive = $('body > .navbar-collapse');
                    sidebar_mini_active = true;
                    white_color = false;

                    window_width = $(window).width();

                    fixed_plugin_open = $('.sidebar .sidebar-wrapper .nav li.active a p').html();



                    $('.switch-sidebar-mini input').on("switchChange.bootstrapSwitch", function() {
                        var $btn = $(this);

                        if (sidebar_mini_active == true) {
                            $('body').removeClass('sidebar-mini');
                            sidebar_mini_active = false;
                            blackDashboard.showSidebarMessage('Sidebar mini deactivated...');
                        } else {
                            $('body').addClass('sidebar-mini');
                            sidebar_mini_active = true;
                            blackDashboard.showSidebarMessage('Sidebar mini activated...');
                        }

                        // we simulate the window Resize so the charts will get updated in realtime.
                        var simulateWindowResize = setInterval(function() {
                            window.dispatchEvent(new Event('resize'));
                        }, 180);

                        // we stop the simulation of Window Resize after the animations are completed
                        setTimeout(function() {
                            clearInterval(simulateWindowResize);
                        }, 1000);
                    });

                    $('.switch-change-color input').on("switchChange.bootstrapSwitch", function() {
                            var $btn = $(this);

                            if (white_color == true) {
                                $('body').addClass('change-background');
                                setTimeout(function() {
                                    $('body').removeClass('change-background');
                                    $('body').removeClass('white-content');
                                }, 900);
                                white_color = false;
                            } else {
                                $('body').addClass('change-background');
                                setTimeout(function() {
                                    $('body').removeClass('change-background');
                                    $('body').addClass('white-content');
                                }, 900);

                                white_color = true;
                            }
                    });
                });
            });
        </script>

        @stack('js')
    </body>
</html>
