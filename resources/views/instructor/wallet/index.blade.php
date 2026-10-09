@extends('layouts.instructor')

@push('title', get_phrase('My Wallet'))

@section('content')
    <div class="ol-card radius-8px">
        <div class="ol-card-body my-3 py-12px px-20px">
            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap flex-md-nowrap">
                <h4 class="title fs-16px">
                    <i class="fi-rr-wallet me-2"></i>
                    {{ get_phrase('My Wallet') }}
                </h4>
                <div class="d-flex align-items-center gap-2">
                    <small class="text-muted">{{ get_phrase('Your ShamCash account') }}:</small>
                    @if (auth()->user()->sham_cash_account)
                        <span class="fw-bold">{{ auth()->user()->sham_cash_account }}</span>
                        <a href="javascript:void(0);" class="text-12px" data-bs-toggle="modal" data-bs-target="#shamCashAccountModal">{{ get_phrase('Change') }}</a>
                    @else
                        <a href="javascript:void(0);" class="btn ol-btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#shamCashAccountModal">{{ get_phrase('Add ShamCash account') }}</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-4">
            <div class="ol-card">
                <div class="ol-card-body p-4 text-center">
                    <p class="sub-title fs-14px fw-semibold mb-2">{{ get_phrase('Current Balance') }}</p>
                    <h3 class="title fw-bold">{{ number_format($wallet->balance, 2) }} {{ $wallet->currency }}</h3>
                    @if (auth()->user()->sham_cash_account && $wallet->balance > 0)
                        <form action="{{ route('instructor.wallet.withdraw') }}" method="post" id="walletWithdrawForm" class="mt-3">
                            @csrf
                            <button type="button" class="btn ol-btn-primary w-100" data-bs-toggle="modal" data-bs-target="#withdrawConfirmModal">
                                <span class="fi-rr-hand-holding-usd me-2"></span>
                                {{ get_phrase('Withdraw to my ShamCash account') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="ol-card">
                <div class="ol-card-body p-3">
                    <h6 class="title fs-15px mb-3">{{ get_phrase('Transactions') }}</h6>
                    @if ($transactions->count() > 0)
                        <div class="table-responsive">
                            <table class="table eTable eTable-2">
                                <thead>
                                    <tr>
                                        <th scope="col">{{ get_phrase('Date') }}</th>
                                        <th scope="col">{{ get_phrase('Description') }}</th>
                                        <th scope="col">{{ get_phrase('Amount') }}</th>
                                        <th scope="col">{{ get_phrase('Type') }}</th>
                                        <th scope="col">{{ get_phrase('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transactions as $transaction)
                                        <tr>
                                            <td>{{ $transaction->created_at->format('Y-m-d H:i:s') }}</td>
                                            <td>{{ $transaction->description }}</td>
                                            <td>{{ number_format($transaction->amount, 2) }}</td>
                                            <td>{{ get_phrase('Withdrawal') }}</td>
                                            <td>
                                                <span class="badge bg-success">{{ get_phrase('Completed') }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        @include('instructor.no_data')
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Withdraw confirm modal -->
    <div class="modal fade" id="withdrawConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ get_phrase('Confirm withdrawal') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ get_phrase('Your whole wallet balance will be sent to your ShamCash account. Are you sure you want to continue?') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ get_phrase('Cancel') }}</button>
                    <button type="button" class="btn ol-btn-primary" id="confirmWithdrawBtn">{{ get_phrase('Confirm withdrawal') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ShamCash account modal -->
    <div class="modal fade" id="shamCashAccountModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ get_phrase('ShamCash account') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="fpb7 mb-2">
                        <label class="form-label ol-form-label">{{ get_phrase('Your ShamCash account number') }} <span class="text-danger">*</span></label>
                        <input type="text" id="instructorScAccountInput" class="form-control ol-form-control" maxlength="32" value="{{ auth()->user()->sham_cash_account ?? '' }}">
                        <div id="instructorScAccountError" class="text-danger text-12px mt-1" style="display:none;"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ get_phrase('Cancel') }}</button>
                    <button type="button" class="btn ol-btn-primary" id="instructorSaveAccountBtn">{{ get_phrase('Save account') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        "use strict";
        $(function() {
            $('#confirmWithdrawBtn').on('click', function() {
                var btn = $(this).attr('disabled', true).text('Processing...');
                $('#walletWithdrawForm').submit();
            });

            $('#instructorSaveAccountBtn').on('click', function() {
                var account = $('#instructorScAccountInput').val().trim();
                $('#instructorScAccountError').hide();
                if (account.length !== 32) {
                    $('#instructorScAccountError').text("{{ get_phrase('Your ShamCash account must be exactly 32 characters.') }}").show();
                    return;
                }
                var btn = $(this).attr('disabled', true);
                $.post("{{ route('instructor.wallet.sham_cash.account.update') }}", { sham_cash_account: account, _token: "{{ csrf_token() }}" })
                    .done(function(res) {
                        if (res.success) {
                            window.location.reload();
                        } else {
                            $('#instructorScAccountError').text(res.message || "{{ get_phrase('Something went wrong.') }}").show();
                        }
                    })
                    .fail(function() {
                        $('#instructorScAccountError').text("{{ get_phrase('Something went wrong.') }}").show();
                    })
                    .always(function() {
                        btn.removeAttr('disabled');
                    });
            });
        });
    </script>
@endpush