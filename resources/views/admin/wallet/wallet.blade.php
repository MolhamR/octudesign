@extends('layouts.admin')

@push('title', get_phrase('Students Wallets'))

@push('meta')
@endpush

@push('css')
    <style>
        #addFundsModal .select2-container,
        #addFundsModal .form-control {
            width: 100% !important;
        }
    </style>
@endpush

@section('content')
    <div class="ol-card radius-8px">
        <div class="ol-card-body my-3 py-12px px-20px">
            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap flex-md-nowrap">
                <h4 class="title fs-16px">
                    <i class="fi-rr-wallet me-2"></i>
                    {{ get_phrase('Students Wallets') }} <span class="text-muted">({{ $students->total() }})</span>
                </h4>

                <button class="btn ol-btn-primary" data-bs-toggle="modal" data-bs-target="#addFundsModal">
                    <span class="fi-rr-plus"></span>
                    <span>{{ get_phrase('Add Funds') }}</span>
                </button>
            </div>
        </div>
    </div>

    <div class="ol-card">
        <div class="ol-card-body p-3">
            <div class="row row-cols-lg-2 mb-30px">
                <div class="col">
                    <form action="{{ route('admin.wallet') }}" method="get" class="form-inline">
                        <input type="text" name="search" class="form-control ol-form-control" placeholder="{{ get_phrase('Search by user name or email') }}" value="{{ request()->input('search') }}">
                    </form>
                </div>
            </div>

            @include('admin.wallet.partials.table', ['students' => $students])
        </div>
    </div>

    <!-- Add Funds Modal (generic) -->
    <div class="modal fade" id="addFundsModal" tabindex="-1" aria-labelledby="addFundsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ get_phrase('Add Funds to User') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.wallet.add') }}" method="POST">
                        @csrf
                        <div class="fpb7 mb-2">
                            <label for="userSelect" class="form-label ol-form-label">{{ get_phrase('Select a User') }}</label>
                            <select name="user_id" id="userSelect" class="form-control ol-form-control select2" style="width: 100%;" required>
                                <option value="">{{ get_phrase('Select a user') }}</option>
                                @foreach ($allStudents as $student)
                                    <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="fpb7 mb-2">
                            <label for="amount" class="form-label ol-form-label">{{ get_phrase('Amount') }}</label>
                            <input type="number" step="any" name="amount" id="amount" class="form-control ol-form-control" placeholder="{{ get_phrase('Enter amount') }}" required>
                        </div>

                        <input type="hidden" name="transaction_type" value="Manual recharge">

                        <div class="fpb7 mb-2">
                            <button type="submit" class="btn ol-btn-primary">{{ get_phrase('Add Funds') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Individual Modals -->
    @foreach ($students as $student)
        <div class="modal fade" id="addFundsModal{{ $student->id }}" tabindex="-1" aria-labelledby="addFundsModalLabel{{ $student->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ get_phrase('Add Funds to') }} {{ $student->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('admin.wallet.add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $student->id }}">

                            <div class="fpb7 mb-2">
                                <label class="form-label ol-form-label">{{ get_phrase('Amount') }}</label>
                                <input type="number" step="any" name="amount" class="form-control ol-form-control" placeholder="{{ get_phrase('Enter amount') }}" required>
                            </div>

                            <input type="hidden" name="transaction_type" value="Manual recharge">

                            <div class="fpb7 mb-2">
                                <button type="submit" class="btn ol-btn-primary">{{ get_phrase('Add Funds') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@push('js')
    <script>
        "use strict";
        $('document').ready(function() {
            if (typeof $.fn.select2 !== 'undefined') {
                $('#userSelect').select2({
                    dropdownParent: $('#addFundsModal'),
                    width: '100%'
                });
            }
        });
    </script>
@endpush