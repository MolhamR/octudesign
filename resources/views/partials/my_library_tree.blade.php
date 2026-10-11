@foreach ($categories as $category)
    @php
        $has_children = !empty($category->children);
        $submenu_class = $submenuClass ?? 'my-library-submenu';
        $is_active = ($activeSlug ?? null) === $category->slug;
    @endphp
    <li class="{{ $has_children ? 'has-children' : '' }}">
        <a href="{{ route('my.library', ['category' => $category->slug]) }}" class="{{ trim(($linkClass ?? '') . ' ' . ($is_active ? 'active' : '')) }}">
            <span class="me-auto">{{ ucfirst($category->title) }}</span>
            @if ($has_children)
                <span class="menu-arrow"><i class="fi fi-sr-angle-small-left"></i></span>
            @endif
        </a>
        @if ($has_children)
            <ul class="{{ $submenu_class }}">
                @include('partials.my_library_tree', [
                    'categories' => $category->children,
                    'linkClass' => $linkClass ?? '',
                    'submenuClass' => $submenu_class,
                    'activeSlug' => $activeSlug ?? null,
                ])
            </ul>
        @endif
    </li>
@endforeach
