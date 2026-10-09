@extends('layouts.default')

@push('title', get_phrase('Library'))
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
                                @if ($category_details)
                                    <li class="breadcrumb-item active" aria-current="page">{{ $category_details->title }}</li>
                                @else
                                    <li class="breadcrumb-item active" aria-current="page">{{ get_phrase('All books') }}</li>
                                @endif
                            </ol>
                        </nav>
                        <div class="row row-gap-3">
                            <div class="col-auto col-md-4 col-lg-3">
                                <h3 class="g-title mt-2">{{ $category_details ? $category_details->title : get_phrase('All Books') }}</h3>
                            </div>
                            <div class="col-auto col-md-4 col-lg-4">
                                <span class="showing-text mt-4 d-inline-block">{{ get_phrase('Showing') . ' ' . $library_items->count() . ' ' . get_phrase('of') . ' ' . $library_items->total() . ' ' . get_phrase('data') }}</span>
                            </div>
                            <div class="col-auto me-auto ms-md-auto col-md-4 col-lg-5">
                                <div class="eNtry-search">
                                    <form action="{{ route('library') }}" method="get">
                                        <div class="search-input w-100">
                                            <input type="text" name="search" value="{{ request('search') }}" class="form-control ol-form-control" placeholder="{{ get_phrase('Search books') }}" />
                                            <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!------------------- Breadcum Area End  --------->

    <!-------------- List Item Start   --------------->
    <div class="eNtery-item">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-4">
                    <div class="sidebar">
                        <!------------------- categories start ------------------->
                        <div class="widget overlay-content overlay-content-max-h-400">
                            <h4 class="widget-title">{{ get_phrase('Categories') }}</h4>
                            <ul class="entry-widget overflow-hidden" id="parent-category">
                                <li class="category @if (!$category_details) active @endif">
                                    <a href="{{ route('library') }}" class="d-flex align-items-center justify-content-between">
                                        <span>{{ get_phrase('All books') }}</span>
                                    </a>
                                </li>
                                @foreach (App\Models\LibraryCategory::where('parent_id', 0)->where('status', 1)->orderBy('sort', 'asc')->get() as $category)
                                    <li class="category @if ($category_details && $category_details->id == $category->id) active @endif" id="{{ $category->slug }}">
                                        <a href="{{ route('library.category', $category->slug) }}" class="d-flex align-items-center justify-content-between">
                                            <span>{{ $category->title }}</span>
                                        </a>
                                    </li>

                                    <ul class="entry-widget ms-3" id="child-category">
                                        @foreach ($category->childs->where('status', 1)->sortBy('sort') as $child_category)
                                            <li class="category @if ($category_details && $category_details->id == $child_category->id) active @endif" id="{{ $child_category->slug }}">
                                                <a href="{{ route('library.category', $child_category->slug) }}" class="d-flex align-items-center justify-content-between">
                                                    <span>{{ $child_category->title }}</span>
                                                </a>
                                            </li>
                                            <ul class="entry-widget ms-3" id="sub-child-category">
                                                @foreach ($child_category->childs->where('status', 1)->sortBy('sort') as $sub_category)
                                                    <li class="category @if ($category_details && $category_details->id == $sub_category->id) active @endif" id="{{ $sub_category->slug }}">
                                                        <a href="{{ route('library.category', $sub_category->slug) }}" class="d-flex align-items-center justify-content-between">
                                                            <span>{{ $sub_category->title }}</span>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endforeach
                                    </ul>
                                @endforeach
                            </ul>
                        </div>
                        <!------------------- categories end ------------------->
                    </div>
                </div>
                <div class="col-lg-9 col-md-8">
                    <div class="row">
                        @foreach ($library_items as $item)
                            <div class="col-lg-4 col-md-6 col-sm-6 mb-30">
                                <a href="{{ route('library.details', $item->slug) }}" class="card Ecard eBar-card">
                                    <div class="courses-img">
                                        <img src="{{ get_image($item->cover_image) }}" alt="book-cover">
                                        <div class="cText d-flex">
                                            <h4>
                                                @if ($item->is_paid == 0)
                                                    {{ get_phrase('Free') }}
                                                @else
                                                    @if ($item->discount_flag == 1)
                                                        {{ currency(number_format($item->discounted_price, 2)) }}
                                                        <del>{{ currency(number_format($item->price, 2)) }}</del>
                                                    @else
                                                        {{ currency(number_format($item->price, 2)) }}
                                                    @endif
                                                @endif
                                            </h4>
                                        </div>
                                    </div>
                                    <div class="card-body entry-details mt-0">
                                        <div class="info-card mb-15">
                                            <div class="creator">
                                                <img src="{{ get_image($item->instructor_image) }}" alt="author-image">
                                                <h5>{{ $item->instructor_name }}</h5>
                                            </div>
                                        </div>
                                        <div class="entry-title">
                                            <h3 class="w-100 ellipsis-line-2">{{ ucfirst($item->title) }}</h3>
                                        </div>
                                        <ul>
                                            <li>
                                                <i class="fa-solid fa-book-open"></i>
                                                {{ $item->category_title }}
                                            </li>
                                            <li>
                                                <i class="fa-solid fa-download"></i>
                                                {{ $item->downloads }} {{ get_phrase('downloads') }}
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="learn-more">{{ get_phrase('Read more') }} <i class="fa-solid fa-arrow-right-long ms-2"></i></div>
                                </a>
                            </div>
                        @endforeach

                        @if ($library_items->count() == 0)
                            <div class="col-12 bg-white radius-10 py-5">
                                @include('frontend.default.empty')
                            </div>
                        @endif
                    </div>

                    @if (count($library_items) > 0)
                        <div class="entry-pagination">
                            <nav aria-label="Page navigation example">
                                {{ $library_items->links() }}
                            </nav>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-------------- List Item End  --------------->
@endsection

@push('js')
@endpush