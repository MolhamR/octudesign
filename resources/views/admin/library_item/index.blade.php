@extends('layouts.admin')

@push('title', get_phrase('Library Books'))

@push('meta')
@endpush

@push('css')
@endpush

@section('content')
    <div class="ol-card radius-8px">
        <div class="ol-card-body my-3 py-12px px-20px">
            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap flex-md-nowrap">
                <h4 class="title fs-16px">
                    <i class="fi-rr-settings-sliders me-2"></i>
                    {{ get_phrase('All Library Books') }} <span class="text-muted">({{ $library_items->total() }})</span>
                </h4>
                <a href="{{ route('admin.library.item.create') }}" class="btn ol-btn-primary d-flex align-items-center cg-10px">
                    <i class="fi fi-rr-plus"></i>
                    {{ get_phrase('Add new book') }}
                </a>
            </div>
        </div>
    </div>

    <div class="ol-card">
        <div class="ol-card-body p-3">
            <div class="row row-cols-lg-3 mb-30px">
                <div class="col">
                    <form action="{{ route('admin.library.items') }}" method="get" class="form-inline">
                        <input type="text" name="search" class="form-control ol-form-control" placeholder="{{ get_phrase('Search books') }}" value="{{ request()->input('search') }}">
                    </form>
                </div>
                <div class="col">
                    <select class="form-control ol-form-control filter-select" data-filter="category">
                        <option value="">{{ get_phrase('All categories') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->slug }}" @if (request('category') == $category->slug) selected @endif>{{ $category->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col">
                    <select class="form-control ol-form-control filter-select" data-filter="status">
                        <option value="">{{ get_phrase('All status') }}</option>
                        <option value="active" @if (request('status') == 'active') selected @endif>{{ get_phrase('Active') }}</option>
                        <option value="pending" @if (request('status') == 'pending') selected @endif>{{ get_phrase('Pending') }}</option>
                        <option value="inactive" @if (request('status') == 'inactive') selected @endif>{{ get_phrase('Inactive') }}</option>
                    </select>
                </div>
                <div class="col mt-2">
                    <select class="form-control ol-form-control filter-select" data-filter="instructor">
                        <option value="">{{ get_phrase('All instructors') }}</option>
                        @foreach ($instructors as $instructor)
                            <option value="{{ $instructor->id }}" @if (request('instructor') == $instructor->id) selected @endif>{{ $instructor->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if ($library_items->count() > 0)
                <div class="table-responsive">
                    <table class="table eTable">
                        <thead>
                            <tr>
                                <th>{{ get_phrase('Cover') }}</th>
                                <th>{{ get_phrase('Title') }}</th>
                                <th>{{ get_phrase('Category') }}</th>
                                <th>{{ get_phrase('Instructor') }}</th>
                                <th>{{ get_phrase('Price') }}</th>
                                <th>{{ get_phrase('Downloads') }}</th>
                                <th>{{ get_phrase('Status') }}</th>
                                <th class="text-end">{{ get_phrase('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($library_items as $item)
                                <tr>
                                    <td class="pe-3">
                                        <img src="{{ get_image($item->cover_image) }}" class="rounded" width="45" height="60" style="object-fit: cover" alt="">
                                    </td>
                                    <td>
                                        <a href="{{ route('library.details', $item->slug) }}" target="_blank" class="fw-600 text-decoration-underline">{{ ucfirst($item->title) }}</a>
                                        <p class="sub-title text-12px mt-1 mb-0">{{ get_phrase('Uploaded at') }} {{ date('d M Y', strtotime($item->created_at)) }}</p>
                                    </td>
                                    <td>{{ $item->category_title ?? get_phrase('Uncategorized') }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-10px">
                                            <img src="{{ get_image($item->instructor_image) }}" class="rounded-circle" width="30" height="30" alt="">
                                            <span>{{ $item->instructor_name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($item->is_paid == 0)
                                            {{ get_phrase('Free') }}
                                        @else
                                            @if ($item->discount_flag == 1)
                                                {{ currency(number_format($item->discounted_price, 2)) }}
                                                <del class="text-muted">{{ currency(number_format($item->price, 2)) }}</del>
                                            @else
                                                {{ currency(number_format($item->price, 2)) }}
                                            @endif
                                        @endif
                                    </td>
                                    <td>{{ $item->downloads }}</td>
                                    <td>
                                        <a href="{{ route('admin.library.item.status', $item->id) }}" class="d-inline-block">
                                            @if ($item->status == 'active')
                                                <span class="badge bg-success">{{ get_phrase('Active') }}</span>
                                            @elseif ($item->status == 'pending')
                                                <span class="badge bg-warning">{{ get_phrase('Pending') }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ get_phrase('Inactive') }}</span>
                                            @endif
                                        </a>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.library.item.edit', $item->id) }}" class="mx-1" data-bs-toggle="tooltip" title="{{ get_phrase('Edit') }}"><i class="fi fi-rr-pen-clip"></i></a>
                                        <a onclick="confirmModal('{{ route('admin.library.item.delete', $item->id) }}')" class="mx-1" data-bs-toggle="tooltip" title="{{ get_phrase('Delete') }}" href="#"><i class="fi fi-rr-trash"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 d-flex justify-content-end">
                    {{ $library_items->appends(request()->query())->links() }}
                </div>
            @else
                <div class="ol-card">
                    <div class="ol-card-body">
                        @include('admin.no_data')
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('js')
    <script>
        "use strict";
        $(document).ready(function() {
            $('.filter-select').on('change', function() {
                let selected_category = $('[data-filter="category"]').val();
                let selected_status = $('[data-filter="status"]').val();
                let selected_instructor = $('[data-filter="instructor"]').val();
                let search = "{{ request()->input('search') }}";
                let url = "{{ route('admin.library.items') }}";

                let params = [];
                if (search) params.push('search=' + search);
                if (selected_category) params.push('category=' + selected_category);
                if (selected_status) params.push('status=' + selected_status);
                if (selected_instructor) params.push('instructor=' + selected_instructor);

                if (params.length) {
                    window.location.href = url + '?' + params.join('&');
                } else {
                    window.location.href = url;
                }
            });
        });
    </script>
@endpush