@extends('layouts.default')

@push('title', get_phrase('My Wallet'))

@push('css')
    <style>
        .wlt-wrap {
            --wlt-primary: #13BE95;
            --wlt-primary-dark: #00907f;
            --wlt-ink: #1a2238;
            --wlt-muted: #7b8499;
        }

        .wlt-hero {
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            padding: 26px 28px;
            color: #fff;
            background: linear-gradient(135deg, #13BE95 0%, #00907f 100%);
            box-shadow: 0 12px 30px rgba(19, 190, 149, .25);
        }

        .wlt-hero::before,
        .wlt-hero::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, .12);
            pointer-events: none;
        }

        .wlt-hero::before {
            width: 220px;
            height: 220px;
            top: -90px;
            left: -40px;
        }

        .wlt-hero::after {
            width: 160px;
            height: 160px;
            bottom: -80px;
            right: -30px;
        }

        .wlt-hero-inner {
            position: relative;
            z-index: 2;
        }

        .wlt-balance-label {
            font-size: 14px;
            opacity: .9;
            margin-bottom: 4px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .wlt-balance {
            font-size: 44px;
            font-weight: 800;
            line-height: 1.15;
            margin: 0 0 2px;
        }

        .wlt-balance small {
            font-size: 18px;
            font-weight: 600;
            opacity: .9;
        }

        .wlt-hero-sub {
            font-size: 13px;
            opacity: .9;
            margin-bottom: 20px;
        }

        .wlt-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .wlt-btn-sham {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #fff;
            color: var(--wlt-primary-dark);
            font-weight: 700;
            border: none;
            border-radius: 14px;
            padding: 12px 18px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, .08);
            transition: transform .15s ease, box-shadow .15s ease;
        }

        .wlt-btn-sham:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 22px rgba(0, 0, 0, .12);
            color: var(--wlt-primary-dark);
        }

        .wlt-btn-sham img {
            width: 26px;
            height: 26px;
        }

        .wlt-btn-ghost {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, .16);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, .4);
            border-radius: 14px;
            padding: 12px 18px;
            font-weight: 600;
        }

        .wlt-btn-ghost:hover {
            background: rgba(255, 255, 255, .26);
            color: #fff;
        }

        .wlt-pending {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fff8ec;
            border: 1px solid #ffe0b3;
            border-radius: 14px;
            padding: 14px 18px;
            color: #9a5b00;
            font-weight: 600;
        }

        .wlt-card {
            background: #fff;
            border: 1px solid #eef1f6;
            border-radius: 18px;
            padding: 22px 24px;
            box-shadow: 0 4px 14px rgba(20, 30, 60, .04);
        }

        .wlt-card-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--wlt-ink);
            margin: 0 0 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .wlt-steps {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
        }

        .wlt-step {
            text-align: center;
            padding: 16px 10px;
            border-radius: 14px;
            background: #f7fafc;
        }

        .wlt-step .num {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--wlt-primary);
            color: #fff;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
        }

        .wlt-step p {
            font-size: 13px;
            color: var(--wlt-muted);
            margin: 0;
            line-height: 1.5;
        }

        .wlt-step-img {
            width: 100%;
            max-height: 140px;
            object-fit: contain;
            border: 1px solid #eef1f6;
            border-radius: 10px;
            margin: 6px 0 8px;
            background: #fff;
        }

        .wlt-step-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            border-radius: 14px;
            background: #e9f9f4;
            color: #00907f;
            font-size: 26px;
            margin: 6px 0 8px;
        }

        .wlt-account-value {
            font-family: monospace;
            font-size: 15px;
            font-weight: 700;
            color: var(--wlt-ink);
            word-break: break-all;
        }

        .wlt-tx-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 4px;
            border-bottom: 1px solid #f1f3f8;
        }

        .wlt-tx-item:last-child {
            border-bottom: none;
        }

        .wlt-tx-icon {
            flex: 0 0 auto;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .wlt-tx-icon.in { background: #e6f7ee; color: #1d7a44; }
        .wlt-tx-icon.out { background: #fdecec; color: #c0392b; }

        .wlt-tx-body {
            flex: 1;
            min-width: 0;
        }

        .wlt-tx-desc {
            font-weight: 600;
            color: var(--wlt-ink);
            margin: 0;
            font-size: 14px;
        }

        .wlt-tx-date {
            font-size: 12px;
            color: var(--wlt-muted);
            margin: 2px 0 0;
        }

        .wlt-tx-amount {
            font-weight: 800;
            font-size: 16px;
            white-space: nowrap;
        }

        .wlt-tx-amount.in { color: #1d7a44; }
        .wlt-tx-amount.out { color: #c0392b; }

        .wlt-badge {
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 50px;
        }

        .wlt-badge.pending { background: #fff4e5; color: #b5670a; }
        .wlt-badge.completed { background: #e6f7ee; color: #1d7a44; }

        .wlt-empty {
            text-align: center;
            padding: 40px 10px;
            color: var(--wlt-muted);
        }

        .wlt-empty i {
            font-size: 42px;
            color: #cfd6e4;
            display: block;
            margin-bottom: 10px;
        }

        .sc-step-block { max-width: 360px; margin: 0 auto; position: relative; }
        .sc-step-badge {
            display: inline-flex; align-items: center; justify-content: center;
            width: 28px; height: 28px; border-radius: 50%;
            background: #13BE95; color: #fff; font-weight: 700; font-size: 14px; margin-bottom: 8px;
        }
        .sc-steps-arrow { color: #9aa4b2; font-size: 20px; margin: 10px 0; }
        .step-img { border: 1px solid #e3e8ee; border-radius: 10px; max-width: 100%; }
        .shamcash-account-box {
            background: #f1f4f8; border: 1px dashed #c4ccd8;
            border-radius: 10px; padding: 12px 16px; word-break: break-all;
        }

        @media (max-width: 767px) {
            .wlt-balance { font-size: 34px; }
            .wlt-steps { grid-template-columns: 1fr; }
            .wlt-hero { padding: 22px; }
        }
    </style>
@endpush

@section('content')
    <section class="my-course-content">
        <div class="profile-banner-area"></div>
        <div class="container profile-banner-area-container">
            <div class="row">
                @include('frontend.default.student.left_sidebar')

                <div class="col-lg-9 px-4 wlt-wrap">
                    <h4 class="g-title mb-3">{{ get_phrase('My Wallet') }}</h4>

                    <!-- Balance hero -->
                    <div class="wlt-hero mb-3">
                        <div class="wlt-hero-inner">
                            <div class="wlt-balance-label">
                                <i class="fi fi-rr-wallet"></i>
                                {{ get_phrase('Wallet Balance') }}
                            </div>
                            <p class="wlt-balance">
                                {{ number_format($wallet->balance, 2) }}
                                <small>{{ $wallet->currency }}</small>
                            </p>
                            <p class="wlt-hero-sub">{{ get_phrase('Use your balance to pay for courses and books instantly.') }}</p>

                            <div class="wlt-actions">
                                <button type="button" class="wlt-btn-sham" id="scMainTrigger">
                                    <img src="{{ asset('uploads/shamcash/sham_cash.svg') }}" alt="ShamCash">
                                    {{ get_phrase('Top up the balance using ShamCash') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    @if (\App\Models\WalletTransaction::where('user_id', auth()->id())->where('transaction_type', 'ShamCash top-up')->where('status', 'pending')->exists())
                        <a href="{{ route('wallet.sham_cash.verify') }}" class="wlt-pending mb-3 text-decoration-none">
                            <span class="spinner-grow spinner-grow-sm" aria-hidden="true"></span>
                            {{ get_phrase('You have a pending top-up waiting for payment') }}
                            <i class="fi fi-rr-angle-small-left ms-auto"></i>
                        </a>
                    @endif

                    <!-- Transactions -->
                    <div class="wlt-card">
                        <p class="wlt-card-title">
                            <i class="fi fi-rr-list-check"></i>
                            {{ get_phrase('Transaction History') }}
                        </p>

                        @forelse($transactions as $t)
                            @php
                                $is_in = in_array($t->transaction_type, ['ShamCash top-up', 'Deposit', 'Wallet recharge', 'Refund']);
                                $label_map = [
                                    'ShamCash wallet recharge' => get_phrase('ShamCash wallet recharge'),
                                    'Wallet recharge' => get_phrase('Wallet recharge'),
                                    'Wallet withdrawal' => get_phrase('Wallet withdrawal'),
                                    'ShamCash top-up' => get_phrase('ShamCash top-up'),
                                    'Withdraw' => get_phrase('Withdrawal'),
                                    'Payment' => get_phrase('Payment'),
                                ];
                                $tx_label = $label_map[$t->description] ?? ($label_map[$t->transaction_type] ?? ($t->description ?: ucfirst($t->transaction_type)));
                            @endphp
                            <div class="wlt-tx-item">
                                <span class="wlt-tx-icon {{ $is_in ? 'in' : 'out' }}">
                                    <i class="fi {{ $is_in ? 'fi-rr-arrow-small-down' : 'fi-rr-arrow-small-up' }}"></i>
                                </span>
                                <div class="wlt-tx-body">
                                    <p class="wlt-tx-desc">{{ $tx_label }}</p>
                                    <p class="wlt-tx-date">{{ $t->created_at ? $t->created_at->format('Y-m-d H:i') : get_phrase('N/A') }}</p>
                                </div>
                                <div class="text-end">
                                    <span class="wlt-badge {{ $t->status === 'pending' ? 'pending' : 'completed' }}">
                                        {{ $t->status === 'pending' ? get_phrase('Pending') : get_phrase('Completed') }}
                                    </span>
                                </div>
                                <div class="text-end wlt-tx-amount {{ $is_in ? 'in' : 'out' }}">
                                    {{ $is_in ? '+' : '-' }}{{ number_format($t->amount, 2) }}
                                </div>
                            </div>
                        @empty
                            <div class="wlt-empty">
                                <i class="fi fi-rr-receipt"></i>
                                {{ get_phrase('No transactions yet.') }}
                            </div>
                        @endforelse

                        @if ($transactions->hasPages())
                            <div class="entry-pagination mt-3">
                                {{ $transactions->links() }}
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

@endsection

@push('js')
    <script>
        "use strict";
        $(function() {
            // Start the ShamCash top-up flow. The ShamCash account itself is
            // added / edited on the waiting-for-payment (verify) page.
            $('#scMainTrigger').on('click', function() {
                $('#scDirectTopUpForm').submit();
            });
        });
    </script>
@endpush
