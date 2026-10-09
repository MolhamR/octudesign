@extends('layouts.admin')

@push('title', get_phrase('Add new book'))

@section('content')
    <div class="row mb-5">
        <div class="col-lg-12">
            <div class="ol-card radius-8px">
                <div class="ol-card-body my-3 py-4 px-20px">
                    <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap flex-md-nowrap">
                        <h4 class="title fs-16px">
                            <i class="fi-rr-settings-sliders me-2"></i>
                            {{ get_phrase('Add new book') }}
                        </h4>
                        <a href="{{ route('admin.library.items') }}" class="btn ol-btn-outline-secondary d-flex align-items-center cg-10px">
                            <i class="fi-rr-arrow-left"></i>
                            <span>{{ get_phrase('Back to books') }}</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="ol-card p-3">
                <div class="ol-card-body">
                    <form action="{{ route('admin.library.item.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 pb-2">
                                <div class="eForm-layouts">
                                    <div class="fpb-7 mb-3">
                                        <label class="form-label ol-form-label" for="title">{{ get_phrase('Book title') }}<span class="text-danger ms-1">*</span></label>
                                        <input type="text" name="title" value="{{ old('title') }}" class="form-control ol-form-control" placeholder="{{ get_phrase('Enter book title') }}" required>
                                    </div>
                                    <div class="fpb-7 mb-3">
                                        <label class="form-label ol-form-label" for="description">{{ get_phrase('Description') }}</label>
                                        <textarea name="description" placeholder="{{ get_phrase('Enter description') }}" class="form-control ol-form-control">{{ old('description') }}</textarea>
                                    </div>
                                    @include('partials.library_category_cascade', ['categories' => $categories])

                                    <div class="fpb-7 mb-3">
                                        <label class="form-label ol-form-label">{{ get_phrase('Pricing type') }}<span class="text-danger ms-1">*</span></label>
                                        <div class="eRadios">
                                            <div class="form-check">
                                                <input type="radio" name="is_paid" value="1" class="form-check-input eRadioSuccess" id="paid" onchange="$('#paid-section').slideDown(200)" checked>
                                                <label for="paid" class="form-check-label">{{ get_phrase('Paid') }}</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" name="is_paid" value="0" class="form-check-input eRadioSuccess" id="free" onchange="$('#paid-section').slideUp(200)">
                                                <label for="free" class="form-check-label">{{ get_phrase('Free') }}</label>
                                            </div>
                                            <div class="paid-section" id="paid-section">
                                                <div class="fpb-7 mt-3">
                                                    <label for="price" class="form-label ol-form-label">{{ get_phrase('Price') }} <small>({{ currency() }})</small><span class="text-danger ms-1">*</span></label>
                                                    <input type="number" name="price" class="form-control ol-form-control" id="price" min="0" step=".01" placeholder="{{ get_phrase('Enter price') }}">
                                                </div>
                                                <div class="fpb-7 mt-3">
                                                    <div class="form-check">
                                                        <input type="checkbox" name="discount_flag" value="1" class="form-check-input eRadioSuccess" id="discount_flag" onchange="$('#discounted-section').slideToggle(200)">
                                                        <label for="discount_flag" class="form-check-label">{{ get_phrase('Check if this book has discount') }}</label>
                                                    </div>
                                                </div>
                                                <div class="fpb-7 mt-3 d-none" id="discounted-section">
                                                    <label for="discounted_price" class="form-label ol-form-label">{{ get_phrase('Discounted price') }}</label>
                                                    <input type="number" name="discounted_price" class="form-control ol-form-control" id="discounted_price" min="0" step=".01" placeholder="{{ get_phrase('Enter discount price') }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="fpb-7 mb-3">
                                        <label class="form-label ol-form-label">{{ get_phrase('Status') }}<span class="text-danger ms-1">*</span></label>
                                        <div class="eRadios">
                                            <div class="form-check">
                                                <input type="radio" value="active" name="status" class="form-check-input eRadioSuccess" id="status_active" checked>
                                                <label for="status_active" class="form-check-label">{{ get_phrase('Active') }}</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" value="pending" name="status" class="form-check-input eRadioWarning" id="status_pending">
                                                <label for="status_pending" class="form-check-label">{{ get_phrase('Pending') }}</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" value="inactive" name="status" class="form-check-input eRadioSecondary" id="status_inactive">
                                                <label for="status_inactive" class="form-check-label">{{ get_phrase('Inactive') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="eForm-layouts">
                                    <div class="fpb-7 mb-3">
                                        <label for="cover_image" class="form-label ol-form-label">{{ get_phrase('Cover image') }}</label>
                                        <input type="file" name="cover_image" class="form-control ol-form-control" id="cover_image" accept="image/*" />
                                        <small class="form-text text-muted">{{ get_phrase('Jpg, jpeg, png, webp. Max 4 MB.') }}</small>
                                    </div>
                                    <div class="fpb-7 mb-3">
                                        <label for="file" class="form-label ol-form-label">{{ get_phrase('PDF file') }}<span class="text-danger ms-1">*</span></label>
                                        <input type="file" name="file" class="form-control ol-form-control" id="file" accept="application/pdf" required />
                                        <small class="form-text text-muted">{{ get_phrase('PDF file only. Max 100 MB. It is stored privately and served in a protected, non-downloadable viewer.') }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-2">
                                <button type="submit" class="btn ol-btn-primary float-end">{{ get_phrase('Submit') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
@endpush
