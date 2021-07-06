@extends('layouts.app', ['pageSlug' => 'dashboard'])

@section('content')

    <div class="row">
        <div class="col-lg-8">
            <div class="card card-chart">

                <div class="card-body bg-white">
                    <div class="">
                        <div id="chart" ></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-chart">
                <div class="card-header">

                    <h3 class="card-title"><i class="tim-icons icon-send text-success"></i>Coins Value</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table tablesorter" id="">
                            <thead class=" text-primary">
                            <th>Coin</th>
                            <th>Quantity</th>
                            </thead>
                            <tbody>
                            @foreach($coins_value as $key=> $coins)
                            <tr>
                                <td>{{$key}}</td>
                                <td>{{$coins}}</td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6 col-md-12">
            <div class="card card-tasks">
                <div class="card-header ">
                    <h6 class="title d-inline">Log Details</h6>
                 <!--   <p class="card-category d-inline"><a href="#" id="clear_log_file">Clear Logs</a> </p> -->
                </div>
                <div class="card-body ">
                    <div class="table-full-width table-responsive bg-white log_data_table">
                   {{$logs_data}}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-12">
            <div class="card card-tasks">
                <div class="card-header">
                    <h4 class="card-title">Order Book (last 10 orders)</h4>
                </div>
                <div class="card-body">
                    <div class="table-full-width table-responsive">
                        <table class="table" id="">
                            <thead class=" text-primary">
                                <tr>
                                    <th>
                                        Coin
                                    </th>
                                    <th>
                                        Price
                                    </th>
                                    <th>
                                        Quantity
                                    </th>
                                    <th class="text-center">
                                        Type
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($open_oder as $my_data)
                                <tr>
                                    <td>
                                      {{$my_data->coin}}
                                    </td>
                                    <td>
                                        {{$my_data->price}}
                                    </td>
                                    <td>
                                        {{$my_data->quantity}}
                                    </td>
                                    <td class="text-center">
                                      @if($my_data->order_type=="1")
                                          Sell
                                        @else
                                        Buy
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('js')


@endpush
