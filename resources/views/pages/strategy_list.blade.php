@extends('layouts.app', ['activePage' => 'strategy', 'titlePage' => __('strategy List'), 'pageSlug' => 'strategy'])

@section('content')
<div class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header card-header-primary">
            <h4 class="card-title ">Strategy</h4>
            <p class="card-category"> Strategies Setting </p>
              <div class="pull-right"><a href="{{ route('strategy.add') }}" class="btn btn-fill btn-primary">Add New</a> </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table">
                <thead class=" text-primary">
                  <th>
                    ID
                  </th>
                  <th>
                      Strategy Name
                  </th>
                  <th>
                      Buy %
                  </th>
                  <th>
                     Sell %
                  </th>
                  <th>
                      Time interval
                  </th>
                  <th>
                      Order Repet
                  </th>
                  <th>
                      Order Again time
                  </th>
                  <th>
                      Stop Loss
                  </th>
                  <th>
                      Strategy key
                  </th>
                  <th>
                      Edit
                  </th>
                </thead>
                <tbody>
                @foreach($table_data as $currrent_data)
                  <tr>
                    <td>
                     {{$currrent_data->id}}
                    </td>
                    <td>
                        {{$currrent_data->strategy_name}}
                    </td>
                    <td>
                        {{$currrent_data->percentage_up}}
                    </td>
                    <td>
                        {{$currrent_data->percentage_down}}
                    </td>
                      <td>
                          {{$currrent_data->time_interval}}
                      </td>
                      <td>
                          {{$currrent_data->order_repet}}
                      </td>
                      <td>
                          {{$currrent_data->order_again_time}}
                      </td>
                      <td>
                          {{$currrent_data->stop_loss}}
                      </td>
                      <td>
                          {{$currrent_data->strategy_key}}
                      </td>
                      <td>
<a href="{{route('strategy.add',array('id'=>$currrent_data->id))}}" >Edit</a>
                      </td>

                  </tr>
                  @endforeach
              </table>
                {{$table_data->links()}}
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection
