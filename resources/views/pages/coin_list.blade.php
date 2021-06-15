@extends('layouts.app', ['activePage' => 'coins', 'titlePage' => __('Coins List'), 'pageSlug' => 'coins'])

@section('content')
<div class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header card-header-primary">
            <h4 class="card-title ">Coins</h4>
            <p class="card-category"> Coins Setting Values</p>
              <div class="pull-right"><a href="{{ route('coins.add') }}" class="btn btn-fill btn-primary">Add New</a> </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table">
                <thead class=" text-primary">
                  <th>
                    ID
                  </th>
                  <th>
                      Coin Name
                  </th>
                  <th>
                    Buy Diffrence
                  </th>
                  <th>
                      Sell Diffrence
                  </th>
                  <th>
                      Bid Diffrence
                  </th>
                  <th>
                      Add margin
                  </th>
                  <th>
                     Quantity
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
                        {{$currrent_data->coin_name}}
                    </td>
                    <td>
                        {{$currrent_data->buy_deff}}
                    </td>
                    <td>
                        {{$currrent_data->sell_deff}}
                    </td>
                      <td>
                          {{$currrent_data->bid_diffrance}}
                      </td>
                      <td>
                          {{$currrent_data->add_value}}
                      </td>
                      <td>
                          {{$currrent_data->slot_value}}
                      </td>
                      <td>
<a href="{{route('coins.add',array('id'=>$currrent_data->id))}}" >Edit</a>
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
