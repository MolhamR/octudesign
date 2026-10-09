@extends('layouts.instructor')

@push('title', get_phrase('Library Manager'))

@section('content')
    <div class="ol-card radius-8px">
        <div class="ol-card-body my-3 py-12px px-20px">
            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap flex-md-nowrap">
                <h4 class="title fs-16px">
                    <i class="fi-rr-settings-sliders me-2"></i>
                    {{ get_phrase('Manage Library Books') }}
                </h4>

                <a href="{{ route('instructor.library.create') }}" class="btn ol-btn-outline-secondary d-flex align-items-center cg-10px">
                    <span class="fi-rr-plus"></span>
                    <span>{{ get_phrase('Add New Book') }}</span>
                </a>
            </div>
        </div>
    </div>

    <div class="row g-2 g-sm-3 mb-3 row-cols-1 row-cols-sm-2 row-cols-md-4 row-cols-lg-4">
        <div class="col">
            <a href="{{ route('instructor.library', ['status' => 'active']) }}" class="d-block">
                <div class="ol-card card-hover h-100">
                    <div class="ol-card-body px-3 py-12px">
                        <div class="d-flex align-items-center cg-12px">
                            <div>
                                <p class="sub-title fs-14px fw-semibold mb-2">{{ $active_books }}</p>
                                <h6 class="title fs-14px mb-1">{{ get_phrase('Active books') }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('instructor.library', ['status' => 'pending']) }}" class="d-block">
                <div class="ol-card card-hover h-100">
                    <div class="ol-card-body px-3 py-12px">
                        <div class="d-flex align-items-center cg-12px">
                            <div>
                                <p class="sub-title fs-14px fw-semibold mb-2">{{ $pending_books }}</p>
                                <h6 class="title fs-14px mb-1">{{ get_phrase('Pending books') }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('instructor.library', ['status' => 'inactive']) }}" class="d-block">
                <div class="ol-card card-hover h-100">
                    <div class="ol-card-body px-3 py-12px">
                        <div class="d-flex align-items-center cg-12px">
                            <div>
                                <p class="sub-title fs-14px fw-semibold mb-2">{{ $inactive_books }}</p>
                                <h6 class="title fs-14px mb-1">{{ get_phrase('Inactive books') }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('instructor.library', ['status' => '']) }}" class="d-block">
                <div class="ol-card card-hover h-100">
                    <div class="ol-card-body px-3 py-12px">
                        <div class="d-flex align-items-center cg-12px">
                            <div>
                                <p class="sub-title fs-14px fw-semibold mb-2">{{ $all_books }}</p>
                                <h6 class="title fs-14px mb-1">{{ get_phrase('Total books') }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="ol-card">
                <div class="ol-card-body px-3 py-3">
                    <div class="row mt-3 mb-4">
                        <div class="col-md-6 d-flex align-items-center gap-3">
                            <form action="{{ route('instructor.library') }}" method="get" class="d-flex align-items-center gap-2">
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ get_phrase('Search title') }}" class="ol-form-control form-control" />
                                <button type="submit" class="btn ol-btn-primary">{{ get_phrase('Search') }}</button>
                            </form>
                            @if (isset($_GET) && count($_GET) > 0)
                                <a href="{{ route('instructor.library') }}" class="me-2" data-bs-toggle="tooltip" title="{{ get_phrase('Clear') }}"><i class="fi-rr-cross-circle"></i></a>
                            @endif
                        </div>
                    </div>

                    @if ($library_items->count() > 0)
                        <div class="table-responsive">
                            <table class="table eTable eTable-2">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">{{ get_phrase('Cover') }}</th>
                                        <th scope="col">{{ get_phrase('Title') }}</th>
                                        <th scope="col">{{ get_phrase('Category') }}</th>
                                        <th scope="col">{{ get_phrase('Status') }}</th>
                                        <th scope="col">{{ get_phrase('Price') }}</th>
                                        <th scope="col">{{ get_phrase('Downloads') }}</th>
                                        <th scope="col">{{ get_phrase('Options') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($library_items as $key => $item)
                                        <tr>
                                            <th scope="row">
                                                <p class="row-number">{{ ++$key }}</p>
                                            </th>
                                            <td>
                                                <img src="{{ get_image($item->cover_image) }}" class="rounded" width="45" height="60" style="object-fit: cover" alt="">
                                            </td>
                                            <td>
                                                <div class="dAdmin_profile d-flex align-items-center min-w-200px">
                                                    <div class="dAdmin_profile_name">
                                                        <h4 class="title fs-14px">
                                                            <a href="{{ route('instructor.library.edit', $item->id) }}">{{ ucfirst($item->title) }}</a>
                                                        </h4>
                                                        <p class="sub-title2 text-12px">{{ get_phrase('Uploaded at') }} {{ date('d M Y', strtotime($item->created_at)) }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="sub-title2 text-12px">{{ $item->category_title }}</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('instructor.library.status', $item->id) }}" class="d-inline-block">
                                                    <span class="badge bg-{{ $item->status }}">{{ get_phrase(ucfirst($item->status)) }}</span>
                                                </a>
                                            </td>
                                            <td>
                                                @if ($item->is_paid == 0)
                                                    <p class="eBadge ebg-soft-success">{{ get_phrase('Free') }}</p>
                                                @else
                                                    @if ($item->discount_flag == 1)
                                                        <p>{{ currency($item->discounted_price) }} <del class="text-muted">{{ currency($item->price) }}</del></p>
                                                    @else
                                                        <p>{{ currency($item->price) }}</p>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>{{ $item->downloads }}</td>
                                            <td>
                                                <div class="dropdown ol-icon-dropdown ol-icon-dropdown-transparent">
                                                    <button class="btn ol-btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <span class="fi-rr-menu-dots-vertical"></span>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item" target="_blank" href="{{ route('library.details', $item->slug) }}">{{ get_phrase('View on frontend') }}</a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('instructor.library.edit', $item->id) }}">{{ get_phrase('Edit') }}</a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" onclick="confirmModal('{{ route('instructor.library.delete', $item->id) }}')" href="javascript:void(0)">{{ get_phrase('Delete') }}</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="admin-tInfo-pagi d-flex justify-content-between justify-content-center align-items-center flex-wrap gr-15">
                            <p class="admin-tInfo">
                                {{ get_phrase('Showing') . ' ' . count($library_items) . ' ' . get_phrase('of') . ' ' . $library_items->total() . ' ' . get_phrase('data') }}
                            </p>
                            {{ $library_items->links() }}
                        </div>
                    @else
                        @include('instructor.no_data')
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
@endpush