@extends('layouts.default')

@push('title', get_phrase('My Library'))

@section('content')
    <section class="my-course-content">
        <div class="profile-banner-area"></div>
        <div class="profile-banner-area-container container">
            <div class="row">
                @include('frontend.default.student.left_sidebar')

                <div class="col-lg-9">
                    <h4 class="g-title text-capitalize mb-1">{{ get_phrase('My Library') }}</h4>
                    <p class="text-muted mb-0 text-14px">
                        @if (!empty($category_details))
                            {{ get_phrase('Category') }}: <strong>{{ ucfirst($category_details->title) }}</strong>
                        @else
                            {{ get_phrase('All Books') }}
                        @endif
                        — {{ $purchased_items->total() }} {{ get_phrase('books') }}
                    </p>

                    <!-- Search -->
                    <form action="{{ route('my.library') }}" method="get" class="ml-search mt-4">
                        @if (!empty($category_details))
                            <input type="hidden" name="category" value="{{ $category_details->slug }}">
                        @endif
                        <div class="ml-search-box">
                            <i class="fi fi-rr-search"></i>
                            <input type="text" name="search" value="{{ request('search') }}" class="ml-search-input" placeholder="{{ get_phrase('Search in my books') }}">
                            @if (request('search'))
                                <a href="{{ route('my.library', array_filter(['category' => $category_details->slug ?? null])) }}" class="ml-search-clear" title="{{ get_phrase('Clear') }}">
                                    <i class="fi fi-rr-cross-small"></i>
                                </a>
                            @endif
                            <button type="submit" class="ml-search-btn">{{ get_phrase('Search') }}</button>
                        </div>
                    </form>

                    <!-- Category chips -->
                    @if (count($my_library_tree) > 0)
                        <div class="ml-chips-wrap mt-3">
                            <a href="{{ route('my.library', array_filter(['search' => request('search')])) }}"
                               class="ml-chip {{ empty($category_details) ? 'active' : '' }}">
                                {{ get_phrase('All Books') }}
                            </a>
                            @include('partials.my_library_chips', ['categories' => $my_library_tree, 'activeSlug' => $category_details->slug ?? null, 'depth' => 1])
                        </div>
                    @endif

                    <!-- Books -->
                    <div class="row mt-4">
                        @forelse ($purchased_items as $item)
                            <div class="col-lg-4 col-md-6 col-sm-6 mb-30">
                                <a href="{{ route('library.details', $item->slug) }}" class="card Ecard eBar-card">
                                    <div class="courses-img">
                                        <img src="{{ get_image($item->cover_image) }}" alt="book-cover">
                                        <div class="cText d-flex">
                                            <h4>
                                                <i class="fa-solid fa-check-circle me-1"></i>
                                                {{ get_phrase('Purchased') }}
                                            </h4>
                                        </div>
                                    </div>
                                    <div class="card-body entry-details mt-0">
                                        <div class="info-card mb-15">
                                            <div class="creator">
                                                <img src="{{ get_image($item->instructor_photo) }}" alt="author-image">
                                                <h5>{{ $item->instructor_name }}</h5>
                                            </div>
                                        </div>
                                        <div class="entry-title">
                                            <h3 class="w-100 ellipsis-line-2">{{ ucfirst($item->title) }}</h3>
                                        </div>
                                    </div>
                                    <div class="learn-more">
                                        <a href="{{ route('library.read', $item->slug) }}">
                                            <i class="fa-solid fa-book-open-reader me-1"></i>
                                            {{ get_phrase('Read now') }}
                                        </a>
                                    </div>
                                </a>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="my-panel p-5">
                                    @include('frontend.default.empty')
                                </div>
                            </div>
                        @endforelse
                    </div>

                    @if ($purchased_items->hasPages())
                        <div class="entry-pagination">
                            <nav aria-label="Page navigation example">
                                {{ $purchased_items->links() }}
                            </nav>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@push('js')
@endpush
