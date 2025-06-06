@props([
    'label' => '',
    'name' => '',
    'id' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => 'Select an option...',
])

@php
    if (!is_string($id) && !is_null($id)) {
        throw new \Exception('$id must be a string or null, got: ' . gettype($id));
    }
    if (!is_string($name)) {
        throw new \Exception('$name must be a string, got: ' . gettype($name));
    }
    if (!is_string($label)) {
        throw new \Exception('$label must be a string, got: ' . gettype($label));
    }
    if (!is_string($placeholder)) {
        throw new \Exception('$placeholder must be a string, got: ' . gettype($placeholder));
    }

    // Use provided id or sanitize name (remove []) for id
    $componentId = $id ?? str_replace(['[]', '[', ']'], '', $name);
    // Generate a unique ID to avoid conflicts
    $uniqueId = $componentId . '-' . uniqid();
@endphp

<div class="js-single-select relative w-full" id="{{ $uniqueId }}" data-name="{{ $name }}"
    data-options="{{ json_encode($options) }}" data-selected="{{ json_encode($selected) }}"
    data-placeholder="{{ $placeholder }}">
    @if ($label)
        <label for="{{ $uniqueId }}"
            class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-300">{{ $label }}</label>
    @endif

    <div
        class="js-toggle-dropdown flex items-center bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg p-2 focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-transparent w-full min-h-[42px]">
        <span class="js-selected-label flex-1 px-2 py-1 text-gray-700 dark:text-gray-300">
            @php
                $selectedOption = collect($options)->firstWhere('value', $selected);
                $selectedLabel = $selectedOption ? $selectedOption['label'] : $placeholder;
            @endphp
            {{ $selectedLabel }}
        </span>
        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </div>

    <div
        class="js-dropdown absolute z-10 mt-1 w-full bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-auto hidden">
        <div class="p-2">
            <input type="text"
                class="js-search-input w-full px-2 py-1 bg-transparent border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700 dark:text-gray-300"
                placeholder="Search...">
        </div>
        <div class="js-options-container"></div>
        <div class="js-no-options px-4 py-2 text-sm text-gray-500 dark:text-gray-400 hidden">
            No options available
        </div>
    </div>

    <input type="hidden" name="{{ $name }}" value="{{ $selected ?? '' }}" class="js-hidden-input">

    @error($name)
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

<script>
    (function waitForJQuery() {
        if (window.jQuery) {
            initializeSingleSelect();
        } else {
            setTimeout(waitForJQuery, 50);
        }

        function initializeSingleSelect() {
            const $ = window.jQuery;
            const $container = $('#{{ $uniqueId }}');
            const $dropdown = $container.find(".js-dropdown");
            const $optionsContainer = $container.find(".js-options-container");
            const $searchInput = $container.find(".js-search-input");
            const $noOptions = $container.find(".js-no-options");
            const $selectedLabel = $container.find(".js-selected-label");
            const $hiddenInput = $container.find('input[type="hidden"]');
            const name = $container.attr("data-name");
            const placeholder = $container.attr("data-placeholder");

            let options = [];
            let selected = null;

            try {
                const rawOptions = $container.attr("data-options");
                if (rawOptions) {
                    options = JSON.parse(rawOptions);
                    console.log('Options parsed for #{{ $uniqueId }}:', options);
                }
            } catch (e) {
                console.error('Error parsing data-options for #{{ $uniqueId }}:', e.message);
                options = [];
            }

            try {
                const rawSelected = $container.attr("data-selected");
                if (rawSelected) {
                    selected = JSON.parse(rawSelected);
                    console.log('Selected parsed for #{{ $uniqueId }}:', selected);
                }
            } catch (e) {
                console.error('Error parsing data-selected for #{{ $uniqueId }}:', e.message);
                selected = null;
            }

            if (!Array.isArray(options) || !options.every((opt) => opt.value !== undefined && opt.label !==
                    undefined)) {
                console.warn('Invalid options format for #{{ $uniqueId }}:', options);
                options = [];
            }

            $container.data("selected", selected);

            function renderOptions() {
                console.log('Rendering options for #{{ $uniqueId }}:', options);
                $optionsContainer.empty();
                const filteredOptions = options.filter((opt) =>
                    opt.label.toLowerCase().includes($searchInput.val().toLowerCase())
                );

                if (filteredOptions.length === 0) {
                    $noOptions.removeClass("hidden");
                } else {
                    $noOptions.addClass("hidden");
                    filteredOptions.forEach((opt) => {
                        const isSelected = String(opt.value) === String(selected);
                        const $option = $(`
                            <div class="js-option cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600" data-value="${opt.value}">
                                ${opt.label}
                            </div>
                        `).toggleClass("bg-gray-100 dark:bg-gray-600", isSelected);
                        $optionsContainer.append($option);
                    });
                }
            }

            function updateSelectedLabel() {
                const option = options.find((opt) => String(opt.value) === String(selected));
                $selectedLabel.text(option ? option.label : placeholder);
                $hiddenInput.val(selected || "");
            }

            renderOptions();
            updateSelectedLabel();

            $container.find(".js-toggle-dropdown").on("click", function() {
                $dropdown.toggleClass("hidden");
                if (!$dropdown.hasClass("hidden")) {
                    $searchInput.focus();
                }
            });

            $searchInput.on("input", function() {
                renderOptions();
            });

            $searchInput.on("keydown", function(e) {
                if (e.key === "Escape") {
                    $dropdown.addClass("hidden");
                }
            });

            $optionsContainer.on("click", ".js-option", function(e) {
                e.preventDefault();
                selected = String($(this).data("value"));
                $container.data("selected", selected);
                $container.attr('data-selected', JSON.stringify(selected));
                updateSelectedLabel();
                $dropdown.addClass("hidden");
                $searchInput.val("");
                renderOptions();
                $hiddenInput.trigger("change");
            });

            $(document).on("options-updated", '.js-single-select[data-name="' + name + '"]', function(event, data) {
                console.log('Options-updated triggered for #{{ $uniqueId }}:', data);
                try {
                    options = data.options || JSON.parse($container.attr("data-options") || "[]");
                    selected = data.selected !== undefined ? data.selected : JSON.parse($container.attr(
                        "data-selected") || "null");
                } catch (e) {
                    console.error('Error updating options for #{{ $uniqueId }}:', e.message);
                    options = [];
                    selected = null;
                }
                renderOptions();
                updateSelectedLabel();
            });

            $(document).on("click.select-{{ $uniqueId }}", function(e) {
                const $target = $(e.target);
                if (!$container.is($target) && $container.find($target).length === 0) {
                    $dropdown.addClass("hidden");
                }
            });
        }
    })();
</script>
