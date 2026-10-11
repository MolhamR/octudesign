@extends('layouts.default')

@push('title', get_phrase('Waiting for payment'))

@push('css')
    <style>
        .scv-wrap {
            --scv-primary: #13BE95;
            --scv-primary-dark: #00907f;
            --scv-ink: #1a2238;
            --scv-muted: #7b8499;
        }

        .scv-card {
            max-width: 640px;
            margin: 40px auto 0;
            background: #fff;
            border: 1px solid #eef1f6;
            border-radius: 22px;
            box-shadow: 0 14px 34px rgba(20, 30, 60, .07);
            overflow: hidden;
        }

        .scv-head {
            background: linear-gradient(135deg, #13BE95 0%, #00907f 100%);
            color: #fff;
            padding: 30px 28px 26px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .scv-head::after {
            content: "";
            position: absolute;
            width: 220px; height: 220px; border-radius: 50%;
            background: rgba(255, 255, 255, .12);
            top: -110px; right: -70px;
        }

        .scv-head img {
            width: 56px; height: 56px;
            background: #fff;
            border-radius: 16px;
            padding: 8px;
        }

        .scv-head h4 {
            color: #fff;
            margin: 12px 0 6px;
            font-weight: 800;
        }

        .scv-head p {
            opacity: .92;
            font-size: 13px;
            margin: 0 auto;
            max-width: 440px;
        }

        .scv-body { padding: 26px 28px 30px; }

        .scv-status {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #fff4e5;
            border: 1px solid #ffd59e;
            color: #b5670a;
            border-radius: 50px;
            padding: 9px 22px;
            font-weight: 700;
            font-size: 14px;
        }

        .scv-status.verified {
            background: #e6f7ee;
            border-color: #8fdab2;
            color: #1d7a44;
        }

        .scv-label {
            font-size: 13px;
            font-weight: 700;
            color: var(--scv-ink);
            margin-bottom: 8px;
        }

        .scv-account {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: #f6f7fa;
            border: 1px dashed #c9d2de;
            border-radius: 14px;
            padding: 16px 18px;
        }

        .scv-account .val {
            font-family: monospace;
            font-size: 17px;
            font-weight: 800;
            color: var(--scv-ink);
            word-break: break-all;
            user-select: all;
        }

        .scv-copy {
            flex: 0 0 auto;
            border: none;
            background: var(--scv-primary);
            color: #fff;
            border-radius: 10px;
            padding: 9px 16px;
            font-weight: 700;
            font-size: 13px;
        }

        .scv-copy:hover { background: var(--scv-primary-dark); color: #fff; }

        .scv-myaccount {
            background: #f6f7fa;
            border: 1px solid #e6e9ef;
            border-radius: 14px;
            padding: 16px 18px;
        }

        .scv-input-row {
            display: flex;
            gap: 10px;
        }

        .scv-input-row .form-control {
            font-family: monospace;
            letter-spacing: .5px;
        }

        .scv-steps {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-top: 22px;
        }

        .scv-step {
            background: #f7fafc;
            border-radius: 14px;
            padding: 16px 10px;
            text-align: center;
        }

        .scv-step .num {
            width: 28px; height: 28px;
            border-radius: 50%;
            background: var(--scv-primary);
            color: #fff; font-weight: 700; font-size: 13px;
            display: inline-flex; align-items: center; justify-content: center;
            margin-bottom: 8px;
        }

        .scv-step p {
            font-size: 12.5px;
            color: var(--scv-muted);
            margin: 0;
            line-height: 1.5;
        }

        .scv-note {
            font-size: 12px;
            color: var(--scv-muted);
            margin-top: 20px;
            text-align: center;
        }

        .scv-step-img {
            display: block;
            width: 100%;
            max-width: 130px;
            height: 74px;
            object-fit: contain;
            margin: 0 auto 10px;
            background: #fff;
            border: 1px solid #e6e9ef;
            border-radius: 10px;
        }

        @media (max-width: 767px) {
            .scv-steps { grid-template-columns: repeat(2, 1fr); }
            .scv-account { flex-direction: column; align-items: stretch; }
            .scv-account .scv-copy { width: 100%; }
        }

        @media (max-width: 575px) {
            .scv-input-row { flex-direction: column; }
            .scv-input-row .btn { width: 100%; }
        }
    </style>
@endpush

@section('content')
    <section class="my-course-content">
        <div class="profile-banner-area"></div>
        <div class="container profile-banner-area-container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="scv-wrap">
                        <div class="scv-card">
                            <div class="scv-head">
                                <img src="{{ asset('uploads/shamcash/sham_cash.svg') }}" alt="ShamCash">
                                <h4>{{ get_phrase('Waiting for payment') }}</h4>
                                <p>{{ get_phrase('Transfer any amount from your ShamCash app to the platform account below. Your wallet is recharged automatically with the full amount you send.') }}</p>
                            </div>

                            <div class="scv-body text-center">
                                <div id="scStatusText" class="scv-status">
                                    <span id="scStatusDot" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    <span id="scStatusLabel">{{ get_phrase('Waiting for payment...') }}</span>
                                </div>

                                <div class="scv-myaccount text-start mt-4">
                                    <p class="scv-label">
                                        <i class="fi fi-rr-user"></i>
                                        {{ get_phrase('Your ShamCash account number') }}
                                    </p>
                                    <div class="scv-input-row">
                                        <input type="text" id="scAccountInputVerify" class="form-control" maxlength="32" inputmode="numeric"
                                            value="{{ auth()->user()->sham_cash_account }}"
                                            placeholder="{{ get_phrase('Enter your 32-character ShamCash account number') }}">
                                        <button type="button" class="btn btn-success" id="scSaveAccountVerifyBtn">
                                            {{ get_phrase('Save') }}
                                        </button>
                                    </div>
                                    <small class="text-muted d-block mt-2">{{ get_phrase('Make sure the account number is exactly 32 characters and matches the number in your ShamCash app') }}</small>
                                    <div id="scAccountErrorVerify" class="text-danger text-12px mt-2" style="display:none;"></div>
                                </div>

                                <div class="text-start mt-4">
                                    <p class="scv-label">
                                        <i class="fi fi-rr-bank"></i>
                                        {{ get_phrase('Platform ShamCash account') }}
                                    </p>
                                    <div class="scv-account">
                                        <span class="val" id="scSystemAccount">{{ $sham_cash_account ?: get_phrase('Not available') }}</span>
                                        @if ($sham_cash_account)
                                            <button type="button" class="scv-copy" id="scCopyBtn">
                                                <i class="fi fi-rr-copy"></i> {{ get_phrase('Copy') }}
                                            </button>
                                        @endif
                                    </div>
                                    <span id="scCopyFeedback" class="text-success text-12px d-inline-block mt-2" style="display:none;">
                                        <i class="fi fi-rr-check"></i> {{ get_phrase('Copied') }}
                                    </span>
                                </div>

                                <div class="scv-steps">
                                    <div class="scv-step">
                                        <span class="num">1</span>
                                        <img src="{{ asset('uploads/shamcash/step2.png') }}" class="scv-step-img" alt="Step 1">
                                        <p>{{ get_phrase('Open your ShamCash app') }}</p>
                                    </div>
                                    <div class="scv-step">
                                        <span class="num">2</span>
                                        <img src="{{ asset('uploads/shamcash/step3.png') }}" class="scv-step-img" alt="Step 2">
                                        <p>{{ get_phrase('Copy your ShamCash account number') }}</p>
                                    </div>
                                     <div class="scv-step">
                                         <span class="num">3</span>
                                         <p>{{ get_phrase('Save your ShamCash account in your Octudesign wallet') }}</p>
                                     </div>
                                     <div class="scv-step">
                                         <span class="num">4</span>
                                         <p>{{ get_phrase('Send any amount to the platform account above and the full amount will be topped up automatically') }}</p>
                                     </div>
                                </div>

                                <p class="text-12px text-muted mt-4 mb-3">
                                    {{ get_phrase('This page checks automatically every few seconds. You can also check manually now.') }}
                                </p>
                                <button type="button" class="btn btn-outline-success" id="scCheckNowBtn">
                                    <i class="fi fi-rr-refresh"></i> {{ get_phrase('Check now') }}
                                </button>

                                <form action="{{ route('wallet.sham_cash.cancel') }}" method="POST" class="mt-3" onsubmit="return confirm('{{ get_phrase('Are you sure you want to cancel this payment? All pending top-up requests will be removed.') }}');">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="fi fi-rr-cross-circle"></i> {{ get_phrase('Cancel payment') }}
                                    </button>
                                </form>

                                <div class="mt-3">
                                    <a href="{{ route('wallet.show') }}" class="btn btn-link">{{ get_phrase('Back to wallet') }}</a>
                                </div>
                            </div>
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
            var checking = false;
            var timer = null;

            $('#scCopyBtn').on('click', function() {
                var text = $('#scSystemAccount').text().trim();
                if (!text) return;
                var done = function() {
                    $('#scCopyFeedback').fadeIn();
                    setTimeout(function() { $('#scCopyFeedback').fadeOut(); }, 1800);
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

            $('#scSaveAccountVerifyBtn').on('click', function() {
                var account = $('#scAccountInputVerify').val().trim();
                $('#scAccountErrorVerify').hide();
                if (account.length !== 32) {
                    $('#scAccountErrorVerify').text("{{ get_phrase('Your ShamCash account must be exactly 32 characters.') }}").show();
                    return;
                }
                var btn = $(this).attr('disabled', true).text("{{ get_phrase('Saving...') }}");
                $.post("{{ route('wallet.sham_cash.account.update') }}", { sham_cash_account: account, _token: "{{ csrf_token() }}" })
                    .done(function(res) {
                        if (res.success) {
                            window.location.reload();
                        } else {
                            $('#scAccountErrorVerify').text(res.message || "{{ get_phrase('Something went wrong.') }}").show();
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
                        $('#scAccountErrorVerify').text(msg).show();
                    })
                    .always(function() {
                        btn.removeAttr('disabled').text("{{ get_phrase('Save') }}");
                    });
            });

            function verifyPayment(silent) {
                if (verified) return;
                var btn = $('#scCheckNowBtn');
                if (!silent) {
                    btn.attr('disabled', true);
                }
                $.post("{{ route('wallet.sham_cash.verify_payments') }}", { _token: "{{ csrf_token() }}" })
                    .done(function(res) {
                        if (res && res.verified) {
                            verified = true;
                            if (timer) clearInterval(timer);
                            $('#scStatusText').addClass('verified');
                            $('#scStatusLabel').text(res.message || "{{ get_phrase('Payment verified') }}");
                            $('#scStatusDot').removeClass('spinner-border spinner-border-sm').addClass('d-none');
                            setTimeout(function() {
                                window.location.href = "{{ route('wallet.show') }}";
                            }, 1800);
                        }
                    })
                    .always(function() {
                        if (!verified) btn.removeAttr('disabled');
                    });
            }

            $('#scCheckNowBtn').on('click', function() {
                verifyPayment(false);
            });

            verifyPayment(true);
            timer = setInterval(function() {
                if (!verified) verifyPayment(true);
            }, 7000);
        });
    </script>
@endpush
