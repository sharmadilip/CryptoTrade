@extends('layouts.app', ['page' => __('settings'), 'pageSlug' => 'settings'])

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="title">{{ __('Update Settings') }}</h5>
                </div>
                <form method="post" action="{{ route('settings.update') }}" autocomplete="off">
                    <div class="card-body">
                        @csrf
                        @method('post')

                        @include('alerts.success')
                        @foreach($table_data as $my_data)
                        <div class="form-group{{ $errors->has($my_data->setting_key) ? ' has-danger' : '' }}">
                            <label><bold>{{ $my_data->setting_label }}(Key:{{ $my_data->setting_key }})</bold></label>
                            <input type="text" name="{{$my_data->setting_key}}" class="form-control{{ $errors->has('setting_key') ? ' is-invalid' : '' }}" value="{{$my_data->setting_value}}" placeholder="{{ __('value') }}">
                            @include('alerts.feedback', ['field' => $my_data->setting_key])
                        </div>
                        @endforeach

                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-fill btn-primary">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="title">{{ __('Add Settings') }}</h5>
                </div>
                <form method="post" action="{{ route('settings.addSetting') }}" autocomplete="off">
                    <div class="card-body">
                        @csrf
                        @method('post')
                        <div class="form-group{{ $errors->has('setting_label') ? ' has-danger' : '' }}">
                            <label>Setting Description</label>
                            <input type="text" name="setting_label" class="form-control{{ $errors->has('setting_label') ? ' is-invalid' : '' }}" value="" placeholder="{{ __('value') }}">
                            @include('alerts.feedback', ['field' => 'setting_label'])
                        </div>
                            <div class="form-group{{ $errors->has('setting_key') ? ' has-danger' : '' }}">
                                <label>Setting Key</label>
                                <input type="text" name="setting_key" class="form-control{{ $errors->has('setting_key') ? ' is-invalid' : '' }}" value="" placeholder="{{ __('value') }}">
                                @include('alerts.feedback', ['field' => 'setting_key'])
                            </div>
                        <div class="form-group{{ $errors->has('setting_value') ? ' has-danger' : '' }}">
                            <label>Setting Value</label>
                            <input type="text" name="setting_value" class="form-control{{ $errors->has('setting_value') ? ' is-invalid' : '' }}" value="" placeholder="{{ __('value') }}">
                            @include('alerts.feedback', ['field' => 'setting_value'])
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
