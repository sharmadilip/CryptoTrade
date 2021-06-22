@extends('layouts.app', ['activePage' => 'buysellpage', 'titlePage' => __('Buy or sale page'), 'pageSlug' => 'pages.SalePage'])

@section('content')
<div class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header card-header-primary">
            <h4 class="card-title ">Buy Or Sell </h4>

          </div>
          <div class="card-body">
              <form name="sell_form" id="coin_trade_form" action="#" autocomplete="off">
              <div class="row">
                  <div class="col-xl-12">
                      <div class="row">
                          <div class="col-6">
                              <div class="row">
                                  <div class="col-6"><label>Select Coin</label></div>
                                  <div class="col-6">
                                      <select  name="symbol" id="symbol" class="form-control bg-darker">
                                          <option value="">--Select--</option>
                                          @foreach($trade_coins as $coins)
                                              <option value="{{$coins}}">{{$coins}}</option>
                                          @endforeach
                                      </select>
                                  </div>
                              </div>
                          </div>

                          <div class="col-4">
                              <div class="row">
                                  <div class="col-6"><button type="button" id="reload_data_buy_sell" class="btn btn-sm btn-fill btn-primary">Use R to reload</button></div>
                                  <div class="col-6"> </div>
                              </div>
                          </div>

                      </div>
                  </div>

                  <div class="w-100 py-2"></div>
                  <div class="col-xl-12">
                      <div class="row">
                          <div class="col-6">
                              <div class="row">
                                  <div class="col-6"><label>Avalible Coin</label></div>
                                  <div class="col-6"><input type="text" name="total_coins" id="total_coins" class="form-control">
                                  </div>
                              </div>
                          </div>
                          <div class="col-6">
                              <div class="row">
                                  <div class="col-6"><label>INR Amount</label></div>
                                  <div class="col-6"><input type="text" id="inr_amount" name="inr_amount" class="form-control"
                                                            placeholder="Remain Empty if dont have"></div>
                              </div>
                          </div>
                      </div>
                  </div>
                  <div class="w-100 py-2"></div>
                <!--
                  <div class="col-xl-12">
                      <div class="row">
                          <div class="col-6">
                              <div class="row">
                                  <div class="col-6"><label>Buy Price.</label></div>
                                  <div class="col-6"><input type="number" id="buy_price" name="buy_price" class="form-control"
                                                            placeholder="INR"></div>
                              </div>
                          </div>
                          <div class="col-6">
                              <div class="row">
                                  <div class="col-6"><label>Sell Price.</label></div>
                                  <div class="col-6"><input type="number"  id="sell_price" name="sell_price" class="form-control"
                                                            placeholder="INR"></div>
                              </div>
                          </div>
                      </div>
                  </div>
                  <div class="w-100 py-2"></div>
                  -->
                  <div class="col-xl-12">
                      <div class="row">
                          <div class="col-6">
                              <div class="row">
                                  <div class="col-6"><label>Purchase INR(%).</label></div>
                                  <div class="col-6"><input type="number" id="inr_percent" name="inr_percent" class="form-control"
                                                            placeholder="INR" min="10" value="50" max="100" step="10"></div>
                              </div>
                          </div>
                          <div class="col-6">
                              <div class="row">
                                  <div class="col-6"><label>Sell Coin(%).</label></div>
                                  <div class="col-6"><input type="number" id="coin_percent" name="coin_percent" class="form-control"
                                                            placeholder="coin" value="50" min="10" step="10" max="100"></div>
                              </div>
                          </div>
                      </div>
                  </div>
                  <div class="w-100 py-2"></div>
                  <!--
                  <div class="col-xl-12">
                      <div class="row">
                          <div class="col-6">
                              <div class="row">
                                  <div class="col-6"><label>Buy Shift Diff.</label></div>
                                  <div class="col-6"><input type="text" id="buy_shift_dif" name="buy_shift_dif" class="form-control"
                                                            placeholder="eg. 0.20 (in paisa)"></div>
                              </div>
                          </div>
                          <div class="col-6">
                              <div class="row">
                                  <div class="col-6"><label>Sell Shift Diff.</label></div>
                                  <div class="col-6"><input type="text" id="sell_shift_dif" name="sell_shift_dif" class="form-control"
                                                            placeholder="eg. 0.20 (in paisa)"></div>
                              </div>
                          </div>
                      </div>
                  </div>
                  -->
              </div>
              </form>
        </div>
      </div>

    </div>
        <div class="col-md-6 ">
            <div class="card">
                <div class="card-header card-header-primary">
                    <h4 class="card-title ">Price Info</h4>

                </div>
                <div class="card-body ps ps--active-y" style="max-height: 400px; overflow: auto;" id="alert_content_action">
                    <table class="table">
                        <tr>
                            <th>Coin Name</th>
                            <th>Buy Pirce</th>
                            <th>Sell Pirce</th>
                        </tr>
                        <tr id="alert_content_action_tr"></tr>
                    </table>

                </div>
            </div>

        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header card-header-primary">
                    <h4 class="card-title ">Order System (Use C for cancel all bids) </h4>

                </div>
                <div class="card-body">
                    <div class="row" id="myTabContent">

                        <div class="keys mb-4 mx-auto text-center">
                            <div class='key__button1'>Buy</div>
                            <div class='key__button1'>Sell</div>
                            <div class="w-100 py-3"></div>
                            <div data-key='q' class='key__button'>Q</div>
                            <div data-key='w' class='key__button'>w</div>
                           <div class="w-100 py-3"></div>
                            <div data-key='a' class='key__button'>A+</div>
                            <div data-key='s' class='key__button'>S-</div>

                        </div>




                    </div>
                </div>
            </div>

        </div>

  </div>
</div>

@endsection

