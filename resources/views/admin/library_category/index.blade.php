@extends('layouts.admin')

@push('title', get_phrase('Library Categories'))

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
                    {{ get_phrase('Library Categories') }} <span class="text-muted">({{ $categories->count() }})</span>
                </h4>

                <a onclick="ajaxModal('{{ route('modal', ['admin.library_category.create', 'parent_id' => 0]) }}', '{{ get_phrase('Add new category') }}')" href="#" class="btn ol-btn-outline-secondary d-flex align-items-center cg-10px">
                    <span class="fi-rr-plus"></span>
                    <span>{{ get_phrase('Add new category') }}</span>
                </a>
            </div>
        </div>
    </div>

    <div class="ol-card radius-8px">
        <div class="ol-card-body py-3 px-20px">
            <p class="sub-title text-12px mb-3">
                {{ get_phrase('You can add a category, a sub category and a sub sub category. Example') }}:
                {{ get_phrase('University') }} &rarr; {{ get_phrase('Faculty') }} &rarr; {{ get_phrase('First year') }}
            </p>

            @if ($categories->count() > 0)
                <div class="table-responsive">
                    <table class="table eTable align-middle">
                        <thead>
                            <tr>
                                <th>{{ get_phrase('Title') }}</th>
                                <th>{{ get_phrase('Level') }}</th>
                                <th>{{ get_phrase('Books') }}</th>
                                <th>{{ get_phrase('Status') }}</th>
                                <th class="text-end">{{ get_phrase('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories->where('parent_id', 0) as $parent_category)
                                <tr>
                                    <td>
                                        <a href="javascript:void(0);" onclick="ajaxModal('{{ route('modal', ['admin.library_category.edit', 'id' => $parent_category->id]) }}', '{{ get_phrase('Edit category') }}')" class="fw-600">
                                            {{ $parent_category->title }}
                                        </a>
                                    </td>
                                    <td><span class="badge bg-primary">{{ get_phrase('Category') }}</span></td>
                                    <td>{{ App\Models\LibraryItem::whereIn('category_id', App\Models\LibraryCategory::descendant_ids($parent_category->id))->count() }}</td>
                                    <td>
                                        <a href="{{ route('admin.library.category.status', $parent_category->id) }}" class="d-inline-block">
                                            @if ($parent_category->status == 1)
                                                <span class="badge bg-success">{{ get_phrase('Active') }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ get_phrase('Inactive') }}</span>
                                            @endif
                                        </a>
                                    </td>
                                    <td class="text-end">
                                        <a onclick="ajaxModal('{{ route('modal', ['admin.library_category.create', 'parent_id' => $parent_category->id]) }}', '{{ get_phrase('Add new category') }}')" class="mx-1" data-bs-toggle="tooltip" title="{{ get_phrase('Add sub category') }}" href="#"><i class="fi-rr-plus"></i></a>
                                        <a onclick="ajaxModal('{{ route('modal', ['admin.library_category.edit', 'id' => $parent_category->id]) }}', '{{ get_phrase('Edit category') }}')" class="mx-1" data-bs-toggle="tooltip" title="{{ get_phrase('Edit') }}" href="#"><i class="fi fi-rr-pen-clip"></i></a>
                                        <a onclick="confirmModal('{{ route('admin.library.category.delete', $parent_category->id) }}')" class="mx-1" data-bs-toggle="tooltip" title="{{ get_phrase('Delete') }}" href="#"><i class="fi fi-rr-trash"></i></a>
                                    </td>
                                </tr>

                                @foreach ($parent_category->childs->sortBy('sort') as $child_category)
                                    <tr>
                                        <td class="ps-4">
                                            <a href="javascript:void(0);" onclick="ajaxModal('{{ route('modal', ['admin.library_category.edit', 'id' => $child_category->id]) }}', '{{ get_phrase('Edit category') }}')">
                                                &mdash;&mdash; {{ $child_category->title }}
                                            </a>
                                        </td>
                                        <td><span class="badge bg-secondary">{{ get_phrase('Sub category') }}</span></td>
                                        <td>{{ App\Models\LibraryItem::whereIn('category_id', App\Models\LibraryCategory::descendant_ids($child_category->id))->count() }}</td>
                                        <td>
                                            <a href="{{ route('admin.library.category.status', $child_category->id) }}" class="d-inline-block">
                                                @if ($child_category->status == 1)
                                                    <span class="badge bg-success">{{ get_phrase('Active') }}</span>
                                                @else
                                                    <span class="badge bg-danger">{{ get_phrase('Inactive') }}</span>
                                                @endif
                                            </a>
                                        </td>
                                        <td class="text-end">
                                            <a onclick="ajaxModal('{{ route('modal', ['admin.library_category.create', 'parent_id' => $child_category->id]) }}', '{{ get_phrase('Add new category') }}')" class="mx-1" data-bs-toggle="tooltip" title="{{ get_phrase('Add sub category') }}" href="#"><i class="fi-rr-plus"></i></a>
                                            <a onclick="ajaxModal('{{ route('modal', ['admin.library_category.edit', 'id' => $child_category->id]) }}', '{{ get_phrase('Edit category') }}')" class="mx-1" data-bs-toggle="tooltip" title="{{ get_phrase('Edit') }}" href="#"><i class="fi fi-rr-pen-clip"></i></a>
                                            <a onclick="confirmModal('{{ route('admin.library.category.delete', $child_category->id) }}')" class="mx-1" data-bs-toggle="tooltip" title="{{ get_phrase('Delete') }}" href="#"><i class="fi fi-rr-trash"></i></a>
                                        </td>
                                    </tr>

                                    @foreach ($child_category->childs->sortBy('sort') as $sub_child_category)
                                        <tr>
                                            <td class="ps-5">
                                                <a href="javascript:void(0);" onclick="ajaxModal('{{ route('modal', ['admin.library_category.edit', 'id' => $sub_child_category->id]) }}', '{{ get_phrase('Edit category') }}')">
                                                    &mdash;&mdash;&mdash; {{ $sub_child_category->title }}
                                                </a>
                                            </td>
                                            <td><span class="badge bg-secondary">{{ get_phrase('Sub sub category') }}</span></td>
                                            <td>{{ App\Models\LibraryItem::where('category_id', $sub_child_category->id)->count() }}</td>
                                            <td>
                                                <a href="{{ route('admin.library.category.status', $sub_child_category->id) }}" class="d-inline-block">
                                                    @if ($sub_child_category->status == 1)
                                                        <span class="badge bg-success">{{ get_phrase('Active') }}</span>
                                                    @else
                                                        <span class="badge bg-danger">{{ get_phrase('Inactive') }}</span>
                                                    @endif
                                                </a>
                                            </td>
                                            <td class="text-end">
                                                <a onclick="ajaxModal('{{ route('modal', ['admin.library_category.edit', 'id' => $sub_child_category->id]) }}', '{{ get_phrase('Edit category') }}')" class="mx-1" data-bs-toggle="tooltip" title="{{ get_phrase('Edit') }}" href="#"><i class="fi fi-rr-pen-clip"></i></a>
                                                <a onclick="confirmModal('{{ route('admin.library.category.delete', $sub_child_category->id) }}')" class="mx-1" data-bs-toggle="tooltip" title="{{ get_phrase('Delete') }}" href="#"><i class="fi fi-rr-trash"></i></a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
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
@endpush
