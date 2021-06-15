@extends('layouts.app', ['activePage' => 'orderbook', 'titlePage' => __('Order Book'), 'pageSlug' => 'coins.orderbook'])

@section('content')
<div class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header card-header-primary">
            <h4 class="card-title ">Orders </h4>

          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table">
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
                  @foreach($table_data as $my_data)
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
              <div class="pagination pagination-sm"> {{$table_data->links()}}</div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection
