@extends('layouts.default')

@push('title', get_phrase('My Library'))

@section('content')
    <section class="my-course-content mt-50">
        <div class="profile-banner-area"></div>
        <div class="profile-banner-area-container container">
            <div class="row">
                @include('frontend.default.student.left_sidebar')

                <div class="col-lg-9">
                    <h4 class="g-title text-capitalize">{{ get_phrase('My Library') }}</h4>
                    <div class="my-panel mt-5">
                        <div class="row">
                            @if ($purchased_items->count() > 0)
                                <div class="row">
                                    @foreach ($purchased_items as $item)
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
                                    @endforeach
                                </div>

                                <div class="entry-pagination">
                                    <nav aria-label="Page navigation example">
                                        {{ $purchased_items->links() }}
                                    </nav>
                                </div>
                            @else
                                <div class="col-12">
                                    <div class="my-panel p-5">
                                        @include('frontend.default.empty')
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('js')
@endpush