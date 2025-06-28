@props([
    'label' => '',
    'name' => '',
    'id' => null,
    'options' => [],
    'selected' => [],
    'placeholder' => 'Select options...',
    'class' => '',
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

    $componentId = $id ?? str_replace(['[]', '[', ']'], '', $name);
    $uniqueId = $componentId . '-' . uniqid();
    $selected = collect($selected)
        ->flatten()
        ->map(function ($value) {
            return is_scalar($value) || is_null($value) ? (string) $value : '';
        })
        ->filter()
        ->values()
        ->unique()
        ->all();
@endphp

<div class="js-multi-select relative w-full {{ $class }}" id="{{ $uniqueId }}"
    data-name="{{ str_replace('[]', '', $name) }}" data-options="{{ json_encode($options) }}"
    data-selected="{{ json_encode($selected) }}" data-placeholder="{{ $placeholder }}">
    @if ($label)
        <label for="{{ $uniqueId }}"
            class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-300">{{ $label }}</label>
    @endif

    <div class="flex items-center mb-2 space-x-2">
        <button type="button"
            class="js-select-all px-3 py-1 text-sm text-white bg-blue-500 hover:bg-blue-600 rounded-md border border-blue-500">
            {{ __('Select All') }}
        </button>
        <button type="button"
            class="js-deselect-all px-3 py-1 text-sm text-white bg-red-500 hover:bg-red-600 rounded-md border border-red-500">
            {{ __('Deselect All') }}
        </button>
    </div>

    <div
        class="js-multi-select-container flex flex-wrap items-center gap-1 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg p-2 min-h-[42px] focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-transparent">
        <input type="text"
            class="js-search-input flex-1 bg-transparent border-none focus:outline-none text-gray-700 dark:text-gray-200 px-2 py-1"
            placeholder="{{ $selected ? '' : $placeholder }}">
    </div>

    <div
        class="js-dropdown absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-xl max-h-60 overflow-auto hidden">
        <div class="p-3">
            <input type="text"
                class="js-search-input w-full px-3 py-1.5 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500"
                placeholder="{{ __('Search...') }}">
        </div>
        <div class="js-options-container custom-scroll max-h-40 overflow-y-auto"></div>
        <div class="js-no-options px-4 py-2 text-sm text-gray-500 dark:text-gray-400 hidden">
            {{ __('No options available') }}
        </div>
    </div>

    @foreach ($selected as $value)
        <input type="hidden" name="{{ str_replace('[]', '', $name) }}[]" value="{{ e($value) }}"
            class="js-hidden-input">
    @endforeach

    @error(str_replace('[]', '', $name))
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

<style>
    .js-dropdown {
        z-index: 1000;
    }

    .custom-scroll::-webkit-scrollbar {
        width: 8px;
    }

    .custom-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .custom-scroll::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .custom-scroll::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    .dark .custom-scroll::-webkit-scrollbar-track {
        background: #1f2937;
    }

    .dark .custom-scroll::-webkit-scrollbar-thumb {
        background: #6b7280;
    }

    .dark .custom-scroll::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }

    .custom-scroll {
        scrollbar-width: thin;
        scrollbar-color: #888 #f1f1f1;
    }

    .dark .custom-scroll {
        scrollbar-color: #6b7280 #1f2937;
    }

    .js-option {
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem;
    }
</style>

<script>
    (function waitForJQuery() {
        if (window.jQuery) {
            initializeMultiSelect();
        } else {
            setTimeout(waitForJQuery, 50);
        }

        function initializeMultiSelect() {
            const $ = window.jQuery;
            const $container = $('#{{ $uniqueId }}');
            const $dropdown = $container.find(".js-dropdown");
            const $optionsContainer = $container.find(".js-options-container");
            const $searchInput = $container.find(".js-search-input");
            const $noOptions = $container.find(".js-no-options");
            const name = $container.attr("data-name");
            const placeholder = $container.attr("data-placeholder");

            let options = [];
            let selected = [];

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
                    selected = JSON.parse(rawSelected).map(String);
                    console.log('Selected parsed for #{{ $uniqueId }}:', selected);
                }
            } catch (e) {
                console.error('Error parsing data-selected for #{{ $uniqueId }}:', e.message);
                selected = [];
            }

            if (!Array.isArray(options) || !options.every(opt => opt.value !== undefined && opt.label !==
                undefined)) {
                console.warn('Invalid options format for #{{ $uniqueId }}:', options);
                options = [];
            }

            $container.data("options", options);
            $container.data("selected", selected);

            function renderOptions() {
                console.log('Rendering options for #{{ $uniqueId }}:', options);
                $optionsContainer.empty();
                const filteredOptions = options.filter(opt =>
                    opt.label.toLowerCase().includes($searchInput.val().toLowerCase())
                );

                if (filteredOptions.length === 0) {
                    $noOptions.removeClass("hidden");
                } else {
                    $noOptions.addClass("hidden");
                    filteredOptions.forEach(opt => {
                        const isSelected = selected.includes(String(opt.value));
                        const $option = $(`
                            <div class="js-option flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600 rounded-md cursor-pointer" data-value="${opt.value}">
                                <input type="checkbox" class="js-option-checkbox mr-2 form-checkbox h-4 w-4 text-indigo-600" ${isSelected ? "checked" : ""}>
                                <span>${opt.label}</span>
                            </div>
                        `);
                        $optionsContainer.append($option);
                    });
                }
            }

            function updateSelected() {
                console.log('Updating selected for #{{ $uniqueId }}:', selected);
                $container.find('input[type="hidden"]').remove();
                selected.forEach(value => {
                    $container.append(
                        `<input type="hidden" name="${name}[]" value="${value}" class="js-hidden-input">`
                    );
                });
                const $selectedContainer = $container.find('.js-multi-select-container');
                $selectedContainer.find('.js-selected-option').remove();
                selected.forEach(value => {
                    const option = options.find(opt => String(opt.value) === String(value));
                    const label = option ? option.label : `Unknown (ID: ${value})`;
                    const $optionSpan = $(`
                        <span class="js-selected-option inline-flex items-center px-2 py-1 bg-gray-200 text-gray-700 text-sm rounded-md dark:bg-gray-600 dark:text-gray-200 m-1" data-value="${value}">
                            <span>${label}</span>
                            <button type="button" class="js-remove-option ml-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </span>
                    `);
                    $selectedContainer.prepend($optionSpan);
                });
                $searchInput.attr('placeholder', selected.length ? '' : placeholder);
                $searchInput.trigger('change'); // Support Livewire or other frameworks
                if (window.Livewire) {
                    window.Livewire.dispatch('input', {
                        name: name,
                        value: selected
                    });
                }
            }

            renderOptions();
            updateSelected();

            $searchInput.on('focus', function() {
                $dropdown.removeClass('hidden');
                renderOptions();
            });

            $searchInput.on('input', function() {
                renderOptions();
            });

            $searchInput.on('keydown', function(e) {
                if (e.key === 'Escape') {
                    $dropdown.addClass("hidden");
                }
            });

            $optionsContainer.on('click', '.js-option', function(e) {
                e.preventDefault();
                const value = String($(this).data("value"));
                const $checkbox = $(this).find(".js-option-checkbox");
                if (selected.includes(value)) {
                    selected = selected.filter(v => v !== value);
                    $checkbox.prop("checked", false);
                } else {
                    selected.push(value);
                    $checkbox.prop("checked", true);
                }
                $container.data("selected", selected);
                updateSelected();
                renderOptions();
            });

            $optionsContainer.on('click', '.js-option-checkbox', function(e) {
                e.stopPropagation();
                const value = String($(this).closest(".js-option").data("value"));
                if (selected.includes(value)) {
                    selected = selected.filter(v => v !== value);
                    $(this).prop("checked", false);
                } else {
                    selected.push(value);
                    $(this).prop("checked", true);
                }
                $container.data("selected", selected);
                updateSelected();
                renderOptions();
            });

            $container.find('.js-select-all').on('click', function() {
                selected = options.map(opt => String(opt.value));
                $container.data("selected", selected);
                updateSelected();
                renderOptions();
            });

            $container.find('.js-deselect-all').on('click', function() {
                selected = [];
                $container.data("selected", selected);
                updateSelected();
                renderOptions();
            });

            $container.on('click', '.js-remove-option', function() {
                const value = String($(this).closest(".js-selected-option").data("value"));
                selected = selected.filter(v => v !== value);
                $container.data("selected", selected);
                updateSelected();
                renderOptions();
            });

            $container.on("options-updated", function(event, data) {
                try {
                    options = data.options || JSON.parse($container.attr("data-options") || "[]");
                    selected = (data.selected || []).filter(val =>
                        options.some(opt => String(opt.value) === String(val))
                    );
                } catch (e) {
                    console.error('Error updating options for #{{ $uniqueId }}:', e.message);
                    options = [];
                    selected = [];
                }
                console.log('Options updated for #{{ $uniqueId }}:', options, 'Selected:', selected);
                renderOptions();
                updateSelected();
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
