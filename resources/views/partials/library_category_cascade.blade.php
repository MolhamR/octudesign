@php
    $selected_category_id = $selected ?? old('category_id');
    $category_tree = $categories->map(function ($category) {
        return [
            'id' => (int) $category->id,
            'title' => $category->title,
            'parent_id' => (int) $category->parent_id,
        ];
    })->values();
@endphp

<div class="fpb-7 mb-3">
    <label for="library_category_l1" class="form-label ol-form-label">{{ get_phrase('Category') }}<span class="text-danger ms-1">*</span></label>
    <select class="ol-select2 lib-cat-level" id="library_category_l1" required>
        <option value="">{{ get_phrase('Select a category') }}</option>
        @foreach ($categories->where('parent_id', 0) as $category)
            <option value="{{ $category->id }}">{{ $category->title }}</option>
        @endforeach
    </select>
</div>
<div class="fpb-7 mb-3 d-none" id="library_category_l2_wrap">
    <label for="library_category_l2" class="form-label ol-form-label">{{ get_phrase('Sub category') }}</label>
    <select class="ol-select2 lib-cat-level" id="library_category_l2"></select>
</div>
<div class="fpb-7 mb-3 d-none" id="library_category_l3_wrap">
    <label for="library_category_l3" class="form-label ol-form-label">{{ get_phrase('Second sub category') }}</label>
    <select class="ol-select2 lib-cat-level" id="library_category_l3"></select>
</div>
<input type="hidden" name="category_id" id="library_category_id" value="{{ $selected_category_id }}">

@push('js')
<script type="text/javascript">
    (function () {
        "use strict";
        var LIB_CATS = @json($category_tree);
        var LIB_SELECTED = @json($selected_category_id !== null && $selected_category_id !== '' ? (int) $selected_category_id : null);
        var PH_L2 = @json(get_phrase('Select a sub category'));
        var PH_L3 = @json(get_phrase('Select a second sub category'));

        function childrenOf(parentId) {
            return LIB_CATS.filter(function (c) { return String(c.parent_id) === String(parentId); });
        }
        function findById(id) {
            for (var i = 0; i < LIB_CATS.length; i++) {
                if (String(LIB_CATS[i].id) === String(id)) { return LIB_CATS[i]; }
            }
            return null;
        }
        function initSelect($el, items, placeholder, selected) {
            if ($el.hasClass('select2-hidden-accessible')) { $el.select2('destroy'); }
            $el.removeClass('inited');
            $el.empty();
            $el.append(new Option('', ''));
            items.forEach(function (c) { $el.append(new Option(c.title, c.id, false, false)); });
            $el.select2({ placeholder: placeholder, width: '100%' });
            $el.addClass('inited');
            if (selected !== null && selected !== undefined && String(selected) !== '') {
                $el.val(String(selected)).trigger('change.select2');
            }
        }

        var $l1 = $('#library_category_l1');
        var $l2 = $('#library_category_l2');
        var $l3 = $('#library_category_l3');
        var $wrap2 = $('#library_category_l2_wrap');
        var $wrap3 = $('#library_category_l3_wrap');
        var $hidden = $('#library_category_id');

        function syncHidden() {
            var value = '';
            if (!$wrap2.hasClass('d-none') && $l2.val()) { value = $l2.val(); }
            else if ($l1.val()) { value = $l1.val(); }
            if (!$wrap3.hasClass('d-none') && $l3.val()) { value = $l3.val(); }
            $hidden.val(value);
        }
        function fillLevel3(parentId, selected) {
            var items = parentId ? childrenOf(parentId) : [];
            if (items.length) {
                $wrap3.removeClass('d-none');
                initSelect($l3, items, PH_L3, selected);
            } else {
                $wrap3.addClass('d-none');
                initSelect($l3, [], PH_L3, null);
            }
        }
        function fillLevel2(parentId, selected) {
            var items = parentId ? childrenOf(parentId) : [];
            if (items.length) {
                $wrap2.removeClass('d-none');
                initSelect($l2, items, PH_L2, selected);
            } else {
                $wrap2.addClass('d-none');
                initSelect($l2, [], PH_L2, null);
            }
        }

        $l1.on('change', function () {
            var parentId = $(this).val();
            $wrap3.addClass('d-none');
            initSelect($l3, [], PH_L3, null);
            fillLevel2(parentId, null);
            syncHidden();
        });
        $l2.on('change', function () {
            var parentId = $(this).val();
            fillLevel3(parentId, null);
            syncHidden();
        });
        $l3.on('change', function () { syncHidden(); });

        if (LIB_SELECTED) {
            var chain = [];
            var node = findById(LIB_SELECTED);
            while (node) {
                chain.unshift(node);
                node = (node.parent_id && String(node.parent_id) !== '0') ? findById(node.parent_id) : null;
            }
            if (chain.length) {
                $l1.val(String(chain[0].id)).trigger('change.select2');
                if (chain[1]) {
                    fillLevel2(chain[0].id, chain[1].id);
                    if (chain[2]) {
                        fillLevel3(chain[1].id, chain[2].id);
                    }
                }
            }
        }
        syncHidden();
    })();
</script>
@endpush
