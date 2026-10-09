@extends('layouts.default')

@push('title', get_phrase('Cart'))
@push('meta')@endpush
@push('css')@endpush

@section('content')
    <!------------------- Breadcum Area Start  ------>
    <section class="breadcum-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="eNtry-breadcum">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ get_phrase('Home') }}</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('library') }}">{{ get_phrase('Library') }}</a></li>
                                <li class="breadcrumb-item active" aria-current="page">{{ get_phrase('Shopping cart') }}</li>
                            </ol>
                        </nav>
                        <h3 class="g-title">{{ get_phrase('Shopping cart') }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!------------------- Breadcum Area End  --------->

    <!-------------- Cart list Item Start   --------------->
    <div class="eNtery-item cart-items">
        <div class="container">
            <div class="entry_panel">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="mb-20">
                            <h4 class="g-title text-20">{{ get_phrase('Cart items') }}</h4>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        @php $count_items_price = 0; @endphp
                        @if (count($cart_items) > 0)
                            <div class="row">
                                @foreach ($cart_items as $book)
                                    <div class="col-lg-12 col-md-12 col-sm-6 mb-30 pb-4">
                                        <div class="row">
                                            <div class="col-lg-3 col-md-3">
                                                <img class="radius-10 object-fit-cover w-100" height="160px" src="{{ get_image($book->cover_image) }}" alt="book-cover">
                                            </div>
                                            <div class="col-lg-9 col-md-9">
                                                <div class="entry-details">
                                                    <div class="entry-title en-title">
                                                        <h3 class="ellipsis-2">{{ ucfirst($book->title) }}</h3>
                                                    </div>
                                                    <p class="description ellipsis-2">{{ ellipsis($book->description ?? '', 150) }}</p>
                                                    <div class="learn-creator">
                                                        <div class="creator">
                                                            <h4>
                                                                @if ($book->is_paid == 0)
                                                                    <b class="text-dark">{{ get_phrase('Free') }}</b>
                                                                @else
                                                                    @if ($book->discount_flag == 1)
                                                                        @php
                                                                            $count_items_price += $book->discounted_price;
                                                                        @endphp
                                                                        <b class="text-dark">{{ currency(number_format($book->discounted_price, 2)) }}</b>
                                                                        <del class="text-12px">{{ currency(number_format($book->price, 2)) }}</del>
                                                                    @else
                                                                        @php
                                                                            $count_items_price += $book->price;
                                                                        @endphp
                                                                        <b class="text-dark">{{ currency(number_format($book->price, 2)) }}</b>
                                                                    @endif
                                                                @endif
                                                            </h4>
                                                        </div>
                                                        <div class="learn-more">
                                                            <a data-bs-toggle="tooltip" title="{{ get_phrase('Delete') }}" href="{{ route('library.cart.delete', $book->id) }}">
                                                                <i class="fa-solid fa-trash-can text-20px"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            @include('frontend.default.empty')
                        @endif
                    </div>
                    <div class="col-lg-4">
                        <div class="cart-total-price px-0 px-lg-4">
                            <h2 class="text-20">{{ get_phrase('Payment summary') }}</h2>

                            <h4 class="price_type sub_total mb-4">
                                <span>{{ get_phrase('Sub total') }}</span>
                                <span>{{ currency(number_format($count_items_price, 2)) }}</span>
                            </h4>

                            @php
                                $coupon_discount = $count_items_price * ($discount / 100);
                                $tax = (get_settings('course_selling_tax') / 100) * ($count_items_price - $coupon_discount);
                            @endphp

                            @if ($discount)
                                <h4 class="price_type tax mb-4">
                                    <span>
                                        {{ get_phrase('Discount') }} ({{ $discount }}{{ get_phrase('%') }})
                                    </span>
                                    <span>- {{ currency(number_format($coupon_discount, 2)) }}</span>
                                </h4>
                            @endif

                            <h4 class="price_type tax mb-4">
                                <span>
                                    {{ get_phrase('Tax') }} ({{ get_settings('course_selling_tax') }}{{ get_phrase('%') }})
                                </span>
                                <span>+ {{ currency(number_format($tax, 2)) }}</span>
                            </h4>

                            <h4 class="price_type total mb-4">
                                <span>{{ get_phrase('Total') }}</span>
                                @php $payable = $count_items_price - $coupon_discount + $tax; @endphp
                                <span>{{ currency(number_format($payable, 2)) }}</span>
                            </h4>

                            <form action="{{ route('library.payout') }}" method="post" class="mt-20">@csrf
                                <input type="hidden" name="payable" value="{{ $payable }}">
                                <input type="hidden" name="coupon_code" value="{{ request()->query('coupon') }}">
                                <input type="hidden" name="coupon_discount" value="{{ $coupon_discount }}">
                                <input type="hidden" name="tax" value="{{ $tax }}">
                                <input type="hidden" name="items" value="{{ json_encode($cart_items->pluck('id')) }}">

                                <div class="row mt-3 g-1">
                                    <div class="col-md-12">
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" name="coupon" placeholder="{{ get_phrase('Apply coupon') }}" value="{{ request()->query('coupon') }}">
                                            <button type="button" value="{{ get_phrase('Apply') }}" class="input-group-text eBtn gradient text-white" id="apply-coupon">
                                                {{ get_phrase('Apply') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-20">
                                    <input type="submit" class="form-control eBtn gradient text-white" value="{{ get_phrase('Continue to payment') }}" @if ($count_items_price == 0) disabled @endif>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-------------- Cart list Item End  --------------->
@endsection

@push('js')
    <script>
        "use strict";
        $(document).ready(function() {
            $('#apply-coupon').on('click', function(e) {
                e.preventDefault();
                let code = $('input[name="coupon"]').val();
                window.location.href = "{{ route('library.cart') }}" + "?coupon=" + code;
            });
        });
    </script>
@endpush