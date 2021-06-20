@extends('layouts.app', ['page' => __('settings'), 'pageSlug' => 'coins.save'])

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="title">{{ __('Coins Add') }}</h5>
                </div>
                <form method="post" action="{{ route('coins.save') }}" autocomplete="off">
                    <div class="card-body">
                        @csrf
                        @method('post')

                        @include('alerts.success')

                        <div class="form-group{{ $errors->has("coin_name") ? ' has-danger' : '' }}">
                            <label>Coin Name </label>
                            <input type="text" name="coin_name" class="form-control{{ $errors->has('coin_name') ? ' is-invalid' : '' }}" value="" placeholder="{{ __('value') }}">
                            @include('alerts.feedback', ['field' => "coin_name"])
                        </div>
                        <div class="form-group{{ $errors->has("buy_deff") ? ' has-danger' : '' }}">
                            <label>Buy Difference in (% in 10 minutes) </label>
                            <input type="text" name="buy_deff" class="form-control{{ $errors->has('buy_deff') ? ' is-invalid' : '' }}" value="" placeholder="{{ __('value') }}">
                            @include('alerts.feedback', ['field' => "buy_deff"])
                        </div>
                        <div class="form-group{{ $errors->has("sell_deff") ? ' has-danger' : '' }}">
                            <label>Sell Difference  in (% in 10 minutes)</label>
                            <input type="text" name="sell_deff" class="form-control{{ $errors->has('sell_deff') ? ' is-invalid' : '' }}" value="" placeholder="{{ __('value') }}">
                            @include('alerts.feedback', ['field' =>" field"])
                        </div>
                        <div class="form-group{{ $errors->has("bid_diffrance") ? ' has-danger' : '' }}">
                            <label>Minimum Diffrence </label>
                            <input type="text" name="bid_diffrance" class="form-control{{ $errors->has('bid_diffrance') ? ' is-invalid' : '' }}" value="" placeholder="{{ __('value') }}">
                            @include('alerts.feedback', ['field' => "bid_diffrance"])
                        </div>
                        <div class="form-group{{ $errors->has("add_value") ? ' has-danger' : '' }}">
                            <label>Add In price(for buy or sell) </label>
                            <input type="text" name="add_value" class="form-control{{ $errors->has('add_value') ? ' is-invalid' : '' }}" value="" placeholder="{{ __('value') }}">
                            @include('alerts.feedback', ['field' => "add_value"])
                        </div>
                        <div class="form-group{{ $errors->has("slot_value") ? ' has-danger' : '' }}">
                            <label>Quantity slot </label>
                            <input type="text" name="slot_value" class="form-control{{ $errors->has('slot_value') ? ' is-invalid' : '' }}" value="" placeholder="{{ __('value') }}">
                            @include('alerts.feedback', ['field' => "slot_value"])
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-fill btn-primary">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
