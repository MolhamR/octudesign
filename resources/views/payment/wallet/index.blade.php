@php
    $payment_details = $payment_details ?? [];
    $payment_gateway = $payment_gateway ?? null;
    $balance = $balance ?? 0;
    $amount = $payment_details['payable_amount'] ?? 0;
    $user_details = Auth::user();
    $identifier = $payment_gateway->identifier ?? 'wallet';
    $has_enough = $balance >= $amount;
@endphp

<style>
    .wpay-card {
        background: #fff;
        border: 1px solid #eef1f6;
        border-radius: 18px;
        padding: 22px 24px;
        box-shadow: 0 6px 18px rgba(20, 30, 60, .05);
    }
    .wpay-rows { display: grid; gap: 12px; margin-bottom: 20px; }
    .wpay-row {
        display: flex; align-items: center; justify-content: space-between;
        padding: 14px 16px; border-radius: 12px; background: #f7fafc;
    }
    .wpay-row .lbl { display: flex; align-items: center; gap: 8px; color: #7b8499; font-weight: 600; margin: 0; }
    .wpay-row .val { font-weight: 800; font-size: 18px; margin: 0; }
    .wpay-row .val.ok { color: #1d7a44; }
    .wpay-row .val.no { color: #c0392b; }
    .wpay-pay {
        width: 100%; border: none; border-radius: 14px; padding: 16px;
        background: linear-gradient(135deg, #13BE95 0%, #00907f 100%);
        color: #fff; font-weight: 800; font-size: 16px;
        display: inline-flex; align-items: center; justify-content: center; gap: 10px;
    }
    .wpay-pay:hover { color: #fff; opacity: .95; }
    .wpay-pay:disabled { background: #c6ccd8; cursor: not-allowed; }
    .wpay-note {
        display: flex; align-items: flex-start; gap: 10px;
        background: #fff8ec; border: 1px solid #ffe0b3; color: #9a5b00;
        border-radius: 12px; padding: 12px 14px; font-size: 13px;
    }
</style>

<div class="wpay-card">
    <div class="wpay-rows">
        <div class="wpay-row">
            <p class="lbl"><i class="fi fi-rr-shopping-cart"></i> {{ get_phrase('Amount to Pay') }}</p>
            <p class="val">{{ number_format($amount, 2) }}</p>
        </div>
        <div class="wpay-row">
            <p class="lbl"><i class="fi fi-rr-wallet"></i> {{ get_phrase('Wallet Balance') }}</p>
            <p class="val {{ $has_enough ? 'ok' : 'no' }}">{{ number_format($balance, 2) }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('wallet.pay') }}" class="wallet-form" id="wallet-payment-form">
        @csrf
        <input type="hidden" name="amount" value="{{ $amount }}">
        <input type="hidden" name="user_id" value="{{ $user_details->id }}">
        <input type="hidden" name="identifier" value="{{ $identifier }}">

        @if ($has_enough)
            <button type="submit" class="wpay-pay" id="wallet-pay-now">
                <i class="fi fi-rr-check-circle"></i>
                {{ get_phrase('Pay with Wallet') }}
            </button>
        @else
            <div class="wpay-note mb-3">
                <i class="fi fi-rr-exclamation"></i>
                <div>
                    <strong>{{ get_phrase('Insufficient Wallet Balance') }}</strong><br>
                    {{ get_phrase('You do not have enough balance to make this payment.') }}
                </div>
            </div>
            <button type="button" class="wpay-pay" disabled>
                <i class="fi fi-rr-lock"></i>
                {{ get_phrase('Pay with Wallet') }}
            </button>
        @endif
    </form>
</div>

<script>
    "use strict";
    (function() {
        var form = document.getElementById('wallet-payment-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                var btn = document.getElementById('wallet-pay-now');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> {{ get_phrase('Processing...') }}';
                }
            });
        }
    })();
</script>
