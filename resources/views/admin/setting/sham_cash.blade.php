@extends('layouts.admin')
@push('title', get_phrase('ShamCash Settings'))
@push('meta')@endpush
@push('css')@endpush
@section('content')
    <div class="ol-card radius-8px">
        <div class="ol-card-body my-3 py-4 px-20px">
            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap flex-md-nowrap">
                <h4 class="title fs-16px">
                    <i class="fi fi-rr-wallet me-2"></i>
                    {{ get_phrase('ShamCash Settings') }}
                </h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <div class="ol-card p-4">
                <h3 class="title text-14px mb-3">{{ get_phrase('ShamCash Settings') }}</h3>
                <div class="ol-card-body">
                    <form class="required-form" action="{{ route('admin.sham_cash.settings.update') }}" method="post">
                        @csrf
                        <div class="fpb-7 mb-3">
                            <label class="form-label ol-form-label" for="sham_cash_account">{{ get_phrase('Platform ShamCash account') }}<span>*</span></label>
                            <input type="text" name="sham_cash_account" id="sham_cash_account" class="form-control ol-form-control" maxlength="64" value="{{ $sham_cash_account }}" required>
                            <small class="form-text text-muted">{{ get_phrase('The account that receives students top-up transfers.') }}</small>
                        </div>

                        <div class="fpb-7 mb-3">
                            <label class="form-label ol-form-label" for="system_withdraw_account">{{ get_phrase('System withdrawal account') }}<span>*</span></label>
                            <input type="text" name="system_withdraw_account" id="system_withdraw_account" class="form-control ol-form-control" maxlength="64" value="{{ $system_withdraw_account }}" required>
                            <small class="form-text text-muted">{{ get_phrase('The account where system profits are transferred.') }}</small>
                        </div>

                        <div class="fpb-7 mb-3">
                            <label class="form-label ol-form-label" for="sham_cash_api_key">{{ get_phrase('ShamCash API key') }}<span>*</span></label>
                            <input type="text" name="sham_cash_api_key" id="sham_cash_api_key" class="form-control ol-form-control" value="{{ $sham_cash_api_key }}" required>
                        </div>

                        <div class="fpb-7 mb-3">
                            <label class="form-label ol-form-label" for="sham_cash_base_url">{{ get_phrase('ShamCash API URL') }}<span>*</span></label>
                            <input type="text" name="sham_cash_base_url" id="sham_cash_base_url" class="form-control ol-form-control" value="{{ $sham_cash_base_url }}" placeholder="https://api.shamcash.com/v1" required>
                        </div>

                        <div class="fpb-7 mb-3">
                            <button type="submit" class="btn ol-btn-primary">{{ get_phrase('Update ShamCash settings') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection