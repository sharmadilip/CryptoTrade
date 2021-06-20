@extends('layouts.app', ['page' => __('settings'), 'pageSlug' => 'strategy.save'])

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="title">{{ __('strategy Edit') }}</h5>
                </div>
                <form method="post" action="{{ route('strategy.save') }}" autocomplete="off">
                    <div class="card-body">
                        @csrf
                        @method('post')

                        @include('alerts.success')

                        <div class="form-group{{ $errors->has("strategy_name") ? ' has-danger' : '' }}">
                            <label>Strategy Name </label>
                            <input type="text" name="strategy_name" class="form-control{{ $errors->has('strategy_name') ? ' is-invalid' : '' }}" value="{{$data->strategy_name}}" placeholder="{{ __('value') }}">
                            @include('alerts.feedback', ['field' => "strategy_name"])
                        </div>
                        <div class="form-group{{ $errors->has("percentage_up") ? ' has-danger' : '' }}">
                            <label> Buy % </label>
                            <input type="number" name="percentage_up" class="form-control{{ $errors->has('percentage_up') ? ' is-invalid' : '' }}" value="{{$data->percentage_up}}" placeholder="{{ __('value') }}">
                            @include('alerts.feedback', ['field' => "percentage_up"])
                        </div>
                        <div class="form-group{{ $errors->has("percentage_down") ? ' has-danger' : '' }}">
                            <label> Sell %</label>
                            <input type="number" name="percentage_down" class="form-control{{ $errors->has('percentage_down') ? ' is-invalid' : '' }}" value="{{$data->percentage_down}}" placeholder="{{ __('value') }}">
                            @include('alerts.feedback', ['field' =>" field"])
                        </div>
                        <div class="form-group{{ $errors->has("time_interval") ? ' has-danger' : '' }}">
                            <label>Time Interval for check it </label>
                            <input type="number" name="time_interval" class="form-control{{ $errors->has('time_interval') ? ' is-invalid' : '' }}" value="{{$data->time_interval}}" placeholder="{{ __('value') }}">
                            @include('alerts.feedback', ['field' => "time_interval"])
                        </div>
                        <div class="form-group{{ $errors->has("order_repet") ? ' has-danger' : '' }}">
                            <label>Order repet in number </label>
                            <input type="number" name="order_repet" class="form-control{{ $errors->has('order_repet') ? ' is-invalid' : '' }}" value="{{$data->order_repet}}" placeholder="{{ __('value') }}">
                            @include('alerts.feedback', ['field' => "order_repet"])
                        </div>
                        <div class="form-group{{ $errors->has("order_again_time") ? ' has-danger' : '' }}">
                            <label>Wait for order again In minutes </label>
                            <input type="number" name="order_again_time" class="form-control{{ $errors->has('order_again_time') ? ' is-invalid' : '' }}" value="{{$data->order_again_time}}" placeholder="{{ __('value') }}">
                            @include('alerts.feedback', ['field' => "order_again_time"])
                        </div>
                        <div class="form-group{{ $errors->has("strategy_key") ? ' has-danger' : '' }}">
                            <label>Strateg Key </label>
                            <input type="text" name="strategy_key" class="form-control{{ $errors->has('strategy_key') ? ' is-invalid' : '' }}" value="{{$data->strategy_key}}" placeholder="{{ __('value') }}">
                            @include('alerts.feedback', ['field' => "strategy_key"])
                        </div>
                    </div>
                    <div class="card-footer">
                        <input type="hidden" name="edit_id" value="{{$data->id}}">
                        <button type="submit" class="btn btn-fill btn-primary">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
