@extends('layouts.default')

@push('title', get_phrase('Waiting for payment'))

@push('css')
    <style>
        .sc-verify-container {
            max-width: 720px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
            padding: 36px 40px;
            margin-top: 40px;
        }

        .sc-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #fff4e5;
            border: 1px solid #ffd59e;
            color: #b5670a;
            border-radius: 50px;
            padding: 8px 20px;
            font-weight: 600;
        }

        .sc-status-pill.verified {
            background: #e6f7ee;
            border-color: #8fdab2;
            color: #1d7a44;
        }

        .sc-account-box {
            background: #f6f7fa;
            border: 1px dashed #c9d2de;
            border-radius: 10px;
            padding: 16px;
            word-break: break-all;
            user-select: all;
        }

        .sc-step-img {
            border: 1px solid #e6e9ef;
            border-radius: 10px;
            max-width: 100%;
        }
    </style>
@endpush

@section('content')
    <section class="my-course-content">
        <div class="profile-banner-area"></div>
        <div class="container profile-banner-area-container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="sc-verify-container mx-auto">
                        <div class="text-center mb-4">
                            <img src="{{ asset('uploads/shamcash/sham_cash.svg') }}" width="56" height="56" alt="ShamCash">
                            <h4 class="g-title mt-2 mb-1">{{ get_phrase('Waiting for payment') }}</h4>
                            <p class="text-muted mb-3">
                                {{ get_phrase('Transfer any amount from your ShamCash app to the platform account below. Your wallet will be recharged automatically with the full amount you send.') }}
                            </p>

                            <div id="scStatusText" class="sc-status-pill">
                                <span id="scStatusDot" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                <span id="scStatusLabel">{{ get_phrase('Waiting for payment...') }}</span>
                            </div>
                        </div>

                        <div class="row gy-4 justify-content-center">
                            <div class="col-md-8">
                                <h6 class="fw-bold text-center">{{ get_phrase('Send the amount to the platform account') }}</h6>
                                <div class="sc-account-box mb-2 text-center">
                                    <small class="text-muted">{{ get_phrase('Platform ShamCash account') }}</small>
                                    <div class="fw-bold" id="scSystemAccount">
                                        {{ $sham_cash_account ?? get_phrase('Not configured yet') }}
                                    </div>
                                </div>
                                @if ($sham_cash_account)
                                    <div class="text-center">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="scCopyBtn">
                                            <i class="fas fa-copy me-1"></i>{{ get_phrase('Copy account number') }}
                                        </button>
                                        <span id="scCopyFeedback" class="text-success text-12px ms-2" style="display:none;">
                                            {{ get_phrase('Copied') }}
                                        </span>
                                    </div>
                                @endif
                                <p class="text-12px text-muted mt-3 text-center">
                                    {{ get_phrase('After sending the amount, wait a few seconds. The page checks automatically every few seconds until the payment is confirmed.') }}
                                </p>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <a href="{{ route('wallet.show') }}" class="btn btn-link">{{ get_phrase('Back to wallet') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('js')
    <script>
        "use strict";
        $(function() {
            var verified = false;
            var timer = null;

            $('#scCopyBtn').on('click', function() {
                var text = $('#scSystemAccount').text().trim();
                if (!text) return;
                var done = function() {
                    $('#scCopyFeedback').show();
                    setTimeout(function() { $('#scCopyFeedback').hide(); }, 2000);
                };
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(done).catch(done);
                } else {
                    var ta = $('<textarea>').val(text).appendTo('body').select();
                    document.execCommand('copy');
                    ta.remove();
                    done();
                }
            });

            function verifyPayment() {
                $.post("{{ route('wallet.sham_cash.verify_payments') }}", { _token: "{{ csrf_token() }}" })
                    .done(function(res) {
                        if (res && res.verified) {
                            verified = true;
                            clearInterval(timer);
                            $('#scStatusText').addClass('verified');
                            $('#scStatusDot').removeClass('spinner-border spinner-border-sm');
                            $('#scStatusLabel').text(res.message || "{{ get_phrase('Payment verified!') }}");
                            setTimeout(function() {
                                window.location.href = "{{ route('wallet.show') }}";
                            }, 1500);
                        }
                    })
                    .fail(function() {
                        // keep polling
                    });
            }

            verifyPayment();
            timer = setInterval(verifyPayment, 5000);
        });
    </script>
@endpush
