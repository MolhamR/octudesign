@php $depth = $depth ?? 1; @endphp
@foreach ($categories as $category)
    <a href="{{ route('my.library', array_filter(['category' => $category->slug, 'search' => request('search')])) }}"
       class="ml-chip {{ ($activeSlug ?? null) === $category->slug ? 'active' : '' }} {{ $depth > 1 ? 'ml-chip-sub' : '' }}">
        {{ ucfirst($category->title) }}
    </a>
    @if (!empty($category->children))
        @include('partials.my_library_chips', [
            'categories' => $category->children,
            'activeSlug' => $activeSlug ?? null,
            'depth' => $depth + 1,
        ])
    @endif
@endforeach
