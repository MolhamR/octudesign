@php
    $all_categories = App\Models\LibraryCategory::orderBy('sort', 'asc')->orderBy('id', 'asc')->get();
    $selected_parent = $parent_id ?? 0;
@endphp

<form action="{{ route('admin.library.category.store') }}" method="post">@csrf
    <div class="fpb7 mb-2">
        <label class="form-label ol-form-label" for="parent_id">{{ get_phrase('Parent category') }}</label>
        <select class="form-control ol-form-control select2" name="parent_id" id="parent_id" required>
            <option value="0" @if ($selected_parent == 0) selected @endif>{{ get_phrase('None (Top level)') }}</option>
            @foreach ($all_categories->where('parent_id', 0) as $category)
                <option value="{{ $category->id }}" @if ($selected_parent == $category->id) selected @endif>{{ $category->title }}</option>
                @foreach ($category->childs->sortBy('sort') as $child_category)
                    <option value="{{ $child_category->id }}" @if ($selected_parent == $child_category->id) selected @endif>&mdash;&mdash; {{ $child_category->title }}</option>
                    @foreach ($child_category->childs->sortBy('sort') as $sub_category)
                        <option value="{{ $sub_category->id }}" @if ($selected_parent == $sub_category->id) selected @endif>&mdash;&mdash;&mdash;&mdash; {{ $sub_category->title }}</option>
                    @endforeach
                @endforeach
            @endforeach
        </select>
        <small class="form-text text-muted">{{ get_phrase('Select a parent to create a sub category.') }}</small>
    </div>

    <div class="fpb7 mb-2">
        <label class="form-label ol-form-label" for="title">{{ get_phrase('Title') }} <span class="text-danger">*</span></label>
        <input class="form-control ol-form-control" type="text" id="title" name="title" required>
    </div>

    <div class="fpb7 mb-2">
        <button type="submit" class="btn ol-btn-primary">{{ get_phrase('Add category') }}</button>
    </div>
</form>