@extends('layouts.instructor')

@push('title', get_phrase('Edit book'))

@section('content')
    <div class="row mb-5">
        <div class="col-lg-12">
            <div class="ol-card radius-8px">
                <div class="ol-card-body my-3 py-4 px-20px">
                    <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap flex-md-nowrap">
                        <h4 class="title fs-16px">
                            <i class="fi-rr-settings-sliders me-2"></i>
                            {{ get_phrase('Edit book') }}
                        </h4>
                    </div>
                </div>
            </div>
            <div class="ol-card p-3">
                <div class="ol-card-body">
                    <form class="ajaxForm" action="{{ route('instructor.library.update', $library_item->id) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 pb-2">
                                <div class="eForm-layouts">
                                    <div class="fpb-7 mb-3">
                                        <label class="form-label ol-form-label" for="title">{{ get_phrase('Book title') }}<span class="text-danger ms-1">*</span></label>
                                        <input type="text" name="title" value="{{ $library_item->title }}" class="form-control ol-form-control" placeholder="{{ get_phrase('Enter book title') }}" required>
                                    </div>
                                    <div class="fpb-7 mb-3">
                                        <label class="form-label ol-form-label" for="description">{{ get_phrase('Description') }}</label>
                                        <textarea name="description" placeholder="{{ get_phrase('Enter description') }}" class="form-control ol-form-control">{{ $library_item->description }}</textarea>
                                    </div>
                                    @include('partials.library_category_cascade', ['categories' => $categories, 'selected' => $library_item->category_id])

                                    <div class="fpb-7 mb-3">
                                        <label class="form-label ol-form-label">{{ get_phrase('Pricing type') }}<span class="text-danger ms-1">*</span></label>
                                        <div class="eRadios">
                                            <div class="form-check">
                                                <input type="radio" name="is_paid" value="1" class="form-check-input eRadioSuccess" id="paid" @if ($library_item->is_paid == 1) checked @endif onchange="$('#paid-section').slideDown(200)">
                                                <label for="paid" class="form-check-label">{{ get_phrase('Paid') }}</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" name="is_paid" value="0" class="form-check-input eRadioSuccess" id="free" @if ($library_item->is_paid == 0) checked @endif onchange="$('#paid-section').slideUp(200)">
                                                <label for="free" class="form-check-label">{{ get_phrase('Free') }}</label>
                                            </div>
                                            <div class="paid-section" id="paid-section" @if ($library_item->is_paid == 0) style="display:none" @endif>
                                                <div class="fpb-7 mt-3">
                                                    <label for="price" class="form-label ol-form-label">{{ get_phrase('Price') }} <small>({{ currency() }})</small><span class="text-danger ms-1">*</span></label>
                                                    <input type="number" name="price" value="{{ $library_item->price }}" class="form-control ol-form-control" id="price" min="0" step=".01" placeholder="{{ get_phrase('Enter price') }}">
                                                </div>
                                                <div class="fpb-7 mt-3">
                                                    <div class="form-check">
                                                        <input type="checkbox" name="discount_flag" value="1" class="form-check-input eRadioSuccess" id="discount_flag" @if ($library_item->discount_flag == 1) checked @endif onchange="$('#discounted-section').slideToggle(200)">
                                                        <label for="discount_flag" class="form-check-label">{{ get_phrase('Check if this book has discount') }}</label>
                                                    </div>
                                                </div>
                                                <div class="fpb-7 mt-3 @if ($library_item->discount_flag != 1) d-none @endif" id="discounted-section">
                                                    <label for="discounted_price" class="form-label ol-form-label">{{ get_phrase('Discounted price') }}</label>
                                                    <input type="number" name="discounted_price" value="{{ $library_item->discounted_price }}" class="form-control ol-form-control" id="discounted_price" min="0" step=".01" placeholder="{{ get_phrase('Enter discount price') }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="fpb-7 mb-3">
                                        <label class="form-label ol-form-label">{{ get_phrase('Status') }}<span class="text-danger ms-1">*</span></label>
                                        <div class="eRadios">
                                            <div class="form-check">
                                                <input type="radio" value="active" name="status" class="form-check-input eRadioSuccess" id="status_active" @if ($library_item->status == 'active') checked @endif>
                                                <label for="status_active" class="form-check-label">{{ get_phrase('Active') }}</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" value="pending" name="status" class="form-check-input eRadioWarning" id="status_pending" @if ($library_item->status == 'pending') checked @endif>
                                                <label for="status_pending" class="form-check-label">{{ get_phrase('Pending') }}</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" value="inactive" name="status" class="form-check-input eRadioSecondary" id="status_inactive" @if ($library_item->status == 'inactive') checked @endif>
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

                                        @if ($library_item->cover_image)
                                            <div class="mt-3">
                                                <img src="{{ get_image($library_item->cover_image) }}" width="80" height="110" style="object-fit: cover" class="rounded" alt="">
                                            </div>
                                        @endif
                                    </div>
                                    <div class="fpb-7 mb-3">
                                        <label for="file" class="form-label ol-form-label">{{ get_phrase('PDF file') }}</label>
                                        <input type="file" name="file" class="form-control ol-form-control" id="file" accept="application/pdf" />
                                        <small class="form-text text-muted">{{ get_phrase('PDF file only. Max 100 MB.') }}</small>
                                        @if ($library_item->file)
                                            <small class="form-text">{{ get_phrase('Leave empty to keep the current PDF file.') }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="pt-2">
                                <button type="submit" class="btn ol-btn-primary float-end">{{ get_phrase('Update') }}</button>
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