@extends('layouts.default')

@push('title', get_phrase('Book Details'))
@push('meta')@endpush
@push('css')@endpush

@section('content')
    @php
        $item_category = App\Models\LibraryCategory::find($library_item->category_id);
    @endphp
    <!------------------- Breadcum Area Start  ------>
    <section class="breadcum-area page-content-pb-100 bg-white">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 px-4">
                    <div class="eNtry-breadcum mt-4">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb d-flex flex-nowrap">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ get_phrase('Home') }}</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('library') }}">{{ get_phrase('Library') }}</a></li>
                                @foreach ($category_breadcrumb as $crumb)
                                    <li class="breadcrumb-item ellipsis-line-1 active" aria-current="page">{{ $crumb->title }}</li>
                                @endforeach
                            </ol>
                        </nav>
                    </div>

                    <div class="course-details pe-auto pe-lg-5">
                        <h2 class="g-title ellipsis-line-4">{{ $library_item->title }}</h2>
                        <p class="g-text text-dark ellipsis-line-2">
                            {{ ellipsis($library_item->description ?? '', 200) }}
                        </p>

                        <div class="row row-gap-4">
                            <div class="col-6 col-sm-6 col-md-4 d-flex align-items-center text-dark">
                                <img class="pro-32 me-2 rounded-circle" src="{{ get_image($library_item->instructor_image) }}" alt="instructor-image">
                                {{ $library_item->instructor_name }}
                            </div>
                            <div class="col-6 col-sm-6 col-md-4 d-flex align-items-center text-dark">
                                <i class="fa-solid fa-book-open me-2"></i>
                                {{ $item_category->title ?? get_phrase('Uncategorized') }}
                            </div>
                            <div class="col-6 col-sm-6 col-md-4 d-flex align-items-center text-dark">
                                <i class="fa-solid fa-download me-2"></i>
                                {{ $library_item->downloads }} {{ get_phrase('Downloads') }}
                            </div>
                            <div class="col-6 col-sm-6 col-md-4 d-flex align-items-center text-dark">
                                <img class="pro-20 me-2" src="{{ asset('assets/frontend/default/image/g3.png') }}" alt="...">
                                {{ get_phrase('PDF Book') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!------------------- Breadcum Area End  --------->

    <section>
        <div class="container">
            <div class="row">
                <div class="col-lg-8 order-2 order-lg-1">
                    <div class="details-page-content">
                        <div class="ps-box static-menu mt-5 w-100">
                            <div class="content-area p-3">
                                <div class="details__description">
                                    <h4 class="g-title mb-3">{{ get_phrase('Description') }}</h4>
                                    <div class="description">
                                        {!! $library_item->description !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 order-1 order-lg-2">
                    <div class="gradient-border radius-22 page-static-sidebar">
                        <div class="ps-box ps-sidebar">
                            <div class="hero-details position-relative pt-3 pb-4 mt-0">
                                <img class="radius-10" src="{{ get_image($library_item->cover_image) }}" alt="book-cover" style="width:100%; max-height:380px; object-fit: cover;">
                            </div>

                            <div class="ps-price d-flex">
                                @if ($library_item->is_paid == 0)
                                    <h4 class="g-title">{{ get_phrase('Free') }}</h4>
                                @elseif ($library_item->discount_flag == 1)
                                    <h4 class="g-title">{{ currency(number_format($library_item->discounted_price, 2)) }}</h4>
                                    <del>{{ currency(number_format($library_item->price, 2)) }}</del>
                                @else
                                    <h4 class="g-title">{{ currency(number_format($library_item->price, 2)) }}</h4>
                                @endif
                            </div>

                            @if (auth()->check())
                                @if ($is_purchased || $library_item->uploader_user_id == auth()->user()->id)
                                    <a href="{{ route('library.read', $library_item->slug) }}" class="eBtn gradient w-100 mb-3">
                                        <i class="fa-solid fa-book-open-reader me-2"></i>
                                        {{ get_phrase('Read now') }}</a>
                                    <p class="text-12px text-muted mb-3">
                                        <i class="fa-solid fa-lock me-1"></i>
                                        {{ get_phrase('Protected reading. Downloading and copying are disabled.') }}
                                    </p>
                                @else
                                    <a href="{{ route('purchase.library', $library_item->id) }}" class="eBtn gradient w-100">
                                        <i class="fa-solid fa-cart-arrow-down me-2"></i>
                                        {{ get_phrase($library_item->is_paid ? 'Buy Now' : 'Get Book') }}
                                    </a>

                                    @if ($library_item->is_paid == 1)
                                        @if ($in_cart)
                                            <a href="{{ route('library.cart.delete', $library_item->id) }}" class="eBtn mt-3 gradient w-100">
                                                {{ get_phrase('Remove from cart') }}</a>
                                        @else
                                            <a href="{{ route('library.cart.store', $library_item->id) }}" class="eBtn learn-btn w-100 mb-3 mt-3">
                                                {{ get_phrase('Add to cart') }}</a>
                                        @endif
                                    @endif
                                @endif
                            @else
                                <a href="{{ route('purchase.library', $library_item->id) }}" class="eBtn gradient mt-3 w-100">
                                    <i class="fa-solid fa-cart-arrow-down me-2"></i>
                                    {{ get_phrase($library_item->is_paid ? 'Buy Now' : 'Get Book') }}</a>
                            @endif

                            <ul class="ps-side-feature mt-2">
                                <li class="d-flex justify-content-between align-items-center py-3 mb-0">
                                    <span>
                                        <i class="fa-solid fa-user"></i>
                                        <p>{{ get_phrase('Instructor') }}</p>
                                    </span>
                                    {{ $library_item->instructor_name }}
                                </li>
                                <li class="d-flex justify-content-between align-items-center py-3 mb-0">
                                    <span>
                                        <i class="fa-solid fa-book-open"></i>
                                        <p>{{ get_phrase('Category') }}</p>
                                    </span>
                                    {{ $item_category->title ?? '' }}
                                </li>
                                <li class="d-flex justify-content-between align-items-center py-3 mb-0">
                                    <span>
                                        <i class="fa-solid fa-file-pdf"></i>
                                        <p>{{ get_phrase('Format') }}</p>
                                    </span>
                                    {{ get_phrase('PDF') }}
                                </li>
                                <li class="d-flex justify-content-between align-items-center py-3 mb-0">
                                    <span>
                                        <i class="fa-solid fa-download"></i>
                                        <p>{{ get_phrase('Downloads') }}</p>
                                    </span>
                                    {{ $library_item->downloads }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('js')
@endpush