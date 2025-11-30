@php
    $payment_details = $payment_details ?? [];
    $payment_gateway = $payment_gateway ?? null;
    $balance = $balance ?? null;
    $amount = $payment_details['payable_amount'] ?? 0;
    $user_details = Auth::user();
    $identifier = $payment_gateway->identifier ?? 'wallet'; // e.g., 'wallet'
@endphp

<div class="row mb-4">
    <div class="col-6">
        <strong>{{ get_phrase('Amount to Pay') }}:</strong>
        <div class="h4 text-success">{{ number_format($amount, 2) }}</div>
    </div>
    <div class="col-6">
        <strong>{{ get_phrase('Wallet Balance') }}:</strong>
        <div class="h4 text-primary">{{ number_format($balance, 2) }}</div>
    </div>
</div>

<form method="POST" action="{{ route('wallet.pay') }}" class="form wallet-form" id="wallet-payment-form">
    @csrf

    {{-- Hidden Inputs --}}
    <input type="hidden" name="amount" value="{{ $amount }}">
    <input type="hidden" name="user_id" value="{{ $user_details->id }}">
    <input type="hidden" name="identifier" value="{{ $identifier }}">

    <hr class="border mb-4">

    @if(isset($balance) && $balance >= $amount)
        <!-- Wallet has enough funds -->
        <button type="submit" class="btn btn-success btn-lg w-100 py-3" id="wallet-pay-now"
           onclick="
                    var button = this;
    
                    button.disabled = true;
                    button.innerText = 'Processing...';
    
   
                    setTimeout(function() {
                    button.closest('form').submit();
                                         }
                                        , 1000);
                                            "
                                            >
            {{ get_phrase('Pay with Wallet') }} 
            <span data-toggle="tooltip" title="{{ get_phrase('Wallet Payment') }}" class="premium-icon ml-2">
                <i class="fas fa-wallet"></i>
            </span>
        </button>
    @else
        <!-- Not enough wallet funds -->
        <div class="alert alert-warning">
            <strong>{{ get_phrase('Insufficient Wallet Balance') }}</strong>
            <br>
            <small>{{ get_phrase('You do not have enough balance to make this payment.') }}</small>
        </div>
        <button class="btn btn-secondary btn-lg w-100 py-3" disabled>
            {{ get_phrase('Pay with Wallet') }} 
            <span data-toggle="tooltip" title="{{ get_phrase('Wallet Payment') }}" class="premium-icon ml-2">
                <i class="fas fa-wallet"></i>
            </span>
        </button>
    @endif
</form>
