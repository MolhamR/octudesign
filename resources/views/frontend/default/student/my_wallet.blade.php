@extends('layouts.default')

@push('title', get_phrase('My Wallet'))

@push('css')
    <style>
        .wallet-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 30vh;
            text-align: center;
            flex-direction: column;
        }

        .balance-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px 40px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }

        .balance-amount {
            font-size: 2.6rem;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 15px;
        }

        .shamcash-account-box {
            background: #f1f4f8;
            border: 1px dashed #c4ccd8;
            border-radius: 10px;
            padding: 12px 16px;
            word-break: break-all;
        }

        .step-img {
            border: 1px solid #e3e8ee;
            border-radius: 10px;
            max-width: 100%;
        }

        /* Transparent, logo-only top-up trigger */
        .shamcash-topup-trigger {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            text-align: center;
        }

        .shamcash-topup-trigger:hover {
            opacity: .85;
        }

        .shamcash-topup-trigger .sc-logo {
            width: 104px;
            height: 104px;
            display: block;
        }

        .shamcash-topup-trigger .sc-trigger-text {
            font-size: 1.4rem;
            font-weight: 700;
            color: #2c3145;
            line-height: 1.4;
        }

        .sc-step-block {
            max-width: 360px;
            margin: 0 auto;
            position: relative;
        }

        .sc-step-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #28a745;
            color: #fff;
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .sc-steps-arrow {
            color: #9aa4b2;
            font-size: 20px;
            margin: 10px 0;
        }

        .sha-pending-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fff7e6;
            border: 1px solid #ffd591;
            color: #ad6800;
            border-radius: 50px;
            padding: 4px 14px;
            font-size: 13px;
        }
    </style>
@endpush

@section('content')
    <section class="my-course-content">
        <div class="profile-banner-area"></div>
        <div class="container profile-banner-area-container">
            <div class="row">
                @include('frontend.default.student.left_sidebar')

                <div class="col-lg-9 px-4">
                    <h4 class="g-title">{{ get_phrase('My Wallet') }}</h4>

                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                        <button type="button" class="shamcash-topup-trigger" id="scMainTrigger">
                            <img src="{{ asset('uploads/shamcash/sham_cash.svg') }}" alt="ShamCash" class="sc-logo">
                            <span class="sc-trigger-text">{{ get_phrase('Click here to top up your wallet with ShamCash') }}</span>
                        </button>

                        @if (\App\Models\WalletTransaction::where('user_id', auth()->id())->where('transaction_type', 'ShamCash top-up')->where('status', 'pending')->exists())
                            <a href="{{ route('wallet.sham_cash.verify') }}" class="sha-pending-pill">
                                <span class="spinner-grow spinner-grow-sm" aria-hidden="true"></span>
                                {{ get_phrase('You have a pending top-up waiting for payment') }}
                            </a>
                        @endif
                    </div>

                    <div class="wallet-container">
                        <div class="balance-card">
                            <h5>{{ get_phrase('Wallet Balance') }}</h5>
                            <p class="balance-amount">{{ number_format($wallet->balance, 2) }} {{ $wallet->currency }}</p>

                            @if (auth()->user()->sham_cash_account)
                                <small class="text-muted d-block">
                                    {{ get_phrase('ShamCash account') }}:
                                    <span class="fw-bold">{{ auth()->user()->sham_cash_account }}</span>
                                </small>
                                <small class="d-block mt-1">
                                    <a href="javascript:void(0);" id="scOpenChangeModal">{{ get_phrase('Change account') }}</a>
                                </small>
                            @else
                                <small class="text-muted">{{ get_phrase('No ShamCash account saved yet.') }}</small>
                            @endif
                        </div>
                    </div>

                    <div class="card p-4">
                        <h5>{{ get_phrase('Transaction History') }}</h5>
                        <div class="table-responsive mt-3">
                            <table class="table">
                                <thead class="transaction-header">
                                    <tr>
                                        <th>{{ get_phrase('Date') }}</th>
                                        <th>{{ get_phrase('Description') }}</th>
                                        <th>{{ get_phrase('Amount') }}</th>
                                        <th>{{ get_phrase('Type') }}</th>
                                        <th>{{ get_phrase('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transactions as $t)
                                        <tr>
                                            <td>{{ $t->created_at ? $t->created_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                                            <td>{{ $t->description }}</td>
                                            <td>{{ number_format($t->amount, 2) }}</td>
                                            <td>
                                                @if ($t->transaction_type === 'ShamCash top-up')
                                                    {{ get_phrase('ShamCash top-up') }}
                                                @elseif ($t->transaction_type === 'Withdraw')
                                                    {{ get_phrase('Withdrawal') }}
                                                @elseif ($t->transaction_type === 'Payment')
                                                    {{ get_phrase('Payment') }}
                                                @else
                                                    {{ ucfirst($t->transaction_type) }}
                                                @endif
                                            </td>
                                            <td>
                                                @if ($t->status === 'pending')
                                                    <span class="badge bg-warning text-dark">{{ get_phrase('Pending') }}</span>
                                                @else
                                                    <span class="badge bg-success">{{ get_phrase('Completed') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">{{ get_phrase('No transactions found.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if ($transactions->hasPages())
                            <div class="entry-pagination mt-3">
                                <nav>
                                    {{ $transactions->links() }}
                                </nav>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Direct top-up form (submitted when a ShamCash account already exists) -->
    <form action="{{ route('wallet.sham_cash.top_up') }}" method="POST" id="scDirectTopUpForm" style="display:none;">
        @csrf
    </form>

    <!-- ShamCash Top-up Modal -->
    <div class="modal fade" id="shamCashTopUpModal" tabindex="-1" aria-labelledby="shamCashTopUpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="shamCashTopUpModalLabel">
                        <img src="{{ asset('uploads/shamcash/sham_cash.svg') }}" width="28" height="28" alt="ShamCash" class="me-2">
                        {{ get_phrase('Top-up your wallet with ShamCash') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="shamCashStep1">
                        <p>{{ get_phrase('You need a ShamCash account to top-up. If you do not have one, please follow the steps below.') }}</p>

                        <div class="sc-step-block text-center mt-3">
                            <span class="sc-step-badge">1</span>
                            <img src="{{ asset('uploads/shamcash/step2.png') }}" class="step-img mb-2" alt="Step 1">
                            <p class="text-12px text-muted mb-0">{{ get_phrase('Press receive in the ShamCash app to show your account number') }}</p>
                        </div>

                        <div class="text-center sc-steps-arrow"><i class="fas fa-arrow-down"></i></div>

                        <div class="sc-step-block text-center">
                            <span class="sc-step-badge">2</span>
                            <img src="{{ asset('uploads/shamcash/step3.png') }}" class="step-img mb-2" alt="Step 2">
                            <p class="text-12px text-muted mb-0">{{ get_phrase('Copy your ShamCash account number') }}</p>
                        </div>

                        <div class="mb-2 mt-4">
                            <label class="form-label ol-form-label">{{ get_phrase('Your ShamCash account number') }} <span class="text-danger">*</span></label>
                            <input type="text" id="scAccountInput" class="form-control ol-form-control" maxlength="32" inputmode="numeric" value="{{ auth()->user()->sham_cash_account }}" placeholder="{{ get_phrase('Enter your 32-character ShamCash account number') }}">
                            <div id="scAccountError" class="text-danger text-12px mt-1" style="display:none;"></div>
                        </div>
                        <button type="button" class="btn btn-success" id="scSaveAccountBtn">{{ get_phrase('Save account and continue') }}</button>
                    </div>

                    <div id="shamCashStep2" style="display:none;">
                        <div class="shamcash-account-box mb-3">
                            <small class="text-muted">{{ get_phrase('Your ShamCash account') }}</small>
                            <div class="fw-bold" id="scSavedAccount"></div>
                            <a href="javascript:void(0);" id="scChangeAccountBtn" class="text-12px">{{ get_phrase('Change account') }}</a>
                        </div>

                        <p class="text-12px text-muted">
                            {{ get_phrase('After submitting, transfer the amount from your ShamCash app to the platform account that will appear on the next page. Your wallet will be recharged automatically once the payment is received.') }}
                        </p>
                        <button type="button" class="btn btn-success" id="scGoToPaymentBtn">{{ get_phrase('Request top-up') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        "use strict";
        $(function() {
            var savedAccount = "{{ auth()->user()->sham_cash_account ?? '' }}";

            function showStep1() {
                $('#shamCashStep1').show();
                $('#shamCashStep2').hide();
            }

            function showStep2() {
                $('#shamCashStep1').hide();
                $('#shamCashStep2').show();
            }

            function goToPayment() {
                $('#scDirectTopUpForm').submit();
            }

            // Clicking the ShamCash logo: existing users go straight to the
            // waiting-for-payment page, new users open the how-to popup.
            $('#scMainTrigger').on('click', function() {
                if (savedAccount) {
                    goToPayment();
                } else {
                    $('#scAccountInput').val('');
                    showStep1();
                    $('#shamCashTopUpModal').modal('show');
                }
            });

            // Change the saved account (images are hidden until the user opts to change).
            $('#scOpenChangeModal').on('click', function() {
                $('#scSavedAccount').text(savedAccount);
                showStep2();
                $('#shamCashTopUpModal').modal('show');
            });

            $('#scChangeAccountBtn').on('click', function() {
                $('#scAccountInput').val(savedAccount);
                showStep1();
            });

            $('#scGoToPaymentBtn').on('click', function() {
                goToPayment();
            });

            $('#scSaveAccountBtn').on('click', function() {
                var account = $('#scAccountInput').val().trim();
                $('#scAccountError').hide();
                if (account.length !== 32) {
                    $('#scAccountError').text("{{ get_phrase('Your ShamCash account must be exactly 32 characters.') }}").show();
                    return;
                }

                var btn = $(this).attr('disabled', true).text("{{ get_phrase('Saving...') }}");
                $.post("{{ route('wallet.sham_cash.account.update') }}", { sham_cash_account: account, _token: "{{ csrf_token() }}" })
                    .done(function(res) {
                        if (res.success) {
                            savedAccount = account;
                            goToPayment();
                        } else {
                            $('#scAccountError').text(res.message || "{{ get_phrase('Something went wrong.') }}").show();
                        }
                    })
                    .fail(function(xhr) {
                        var msg = "{{ get_phrase('Something went wrong.') }}";
                        try {
                            var data = JSON.parse(xhr.responseText);
                            if (data && data.errors && data.errors.sham_cash_account) {
                                msg = data.errors.sham_cash_account[0];
                            }
                        } catch (e) {}
                        $('#scAccountError').text(msg).show();
                    })
                    .always(function() {
                        btn.removeAttr('disabled').text("{{ get_phrase('Save account and continue') }}");
                    });
            });

            $('#shamCashTopUpModal').on('hidden.bs.modal', function() {
                $('#scAccountError').hide();
            });
        });
    </script>
@endpush
