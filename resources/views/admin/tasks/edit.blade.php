<x-layouts.app>
    <!-- Page Title -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Directorate') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Edit Directorate') }}</p>
        </div>

        <a href="{{ route('admin.directorate.index') }}"
            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300
                  focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2
                  dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-900">
            Back to Directorates
        </a>
    </div>

    <div class="flex flex-col md:flex-row gap-6">
        <div class="flex-1">
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
                <div class="p-6">
                    <form class="max-w-md mb-10" action="{{ route('admin.directorate.update', $directorate) }}"
                        method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <x-forms.input label="Title" name="title" type="text" :value="old('title', $directorate->title)" />
                        </div>

                        <div class="mb-4">
                            <x-forms.text-area label="Description" name="description" :value="$directorate->description ?? ''" />
                        </div>

                        <div class="mb-4">
                            <x-forms.multi-select label="Department" name="departments[]" :options="collect($departments)
                                ->map(fn($label, $value) => ['value' => (string) $value, 'label' => $label])
                                ->values()
                                ->all()"
                                :selected="old('departments', $directorate->departments->pluck('id')->toArray())" multiple placeholder="Select department" :error="$errors->first('departments')" />
                        </div>

                        <div class="flex space-x-2">
                            <x-buttons.primary>{{ __('Update') }}</x-buttons.primary>
                            <a href="{{ route('admin.directorate.index') }}"
                                class="px-4 py-2 text-sm text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-500">
                                {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Get all multi-select components
                const multiSelects = document.querySelectorAll('.js-multi-select');

                multiSelects.forEach((multiSelect) => {
                    const selectAllBtn = multiSelect.querySelector('.js-select-all');
                    const deselectAllBtn = multiSelect.querySelector('.js-deselect-all');
                    const container = multiSelect.querySelector('.js-multi-select-container');
                    const searchInput = multiSelect.querySelector('.js-search-input');
                    const dropdown = multiSelect.querySelector('.js-dropdown');
                    const optionsContainer = multiSelect.querySelector('.js-options-container');
                    const noOptions = multiSelect.querySelector('.js-no-options');
                    const inputContainer = multiSelect;
                    const options = JSON.parse(multiSelect.dataset.options || '[]');
                    const name = multiSelect.dataset.name;
                    const multiple = multiSelect.dataset.multiple === 'true';
                    const placeholder = multiSelect.dataset.placeholder || 'Select options...';

                    // Helper function to create a selected option element
                    const createSelectedOption = (option) => {
                        const span = document.createElement('span');
                        span.className =
                            'js-selected-option inline-flex items-center px-2 py-1 bg-gray-200 text-gray-700 text-sm rounded-md dark:bg-gray-600 dark:text-gray-300 m-1';
                        span.dataset.value = option.value;
                        span.innerHTML = `
                <span>${option.label}</span>
                <button type="button" class="js-remove-option ml-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            `;
                        return span;
                    };

                    // Helper function to create a dropdown option element
                    const createDropdownOption = (option) => {
                        const div = document.createElement('div');
                        div.className =
                            'js-option px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600 cursor-pointer';
                        div.dataset.value = option.value;
                        div.textContent = option.label;
                        return div;
                    };

                    // Helper function to update hidden inputs
                    const updateHiddenInputs = (selectedValues) => {
                        const existingInputs = inputContainer.querySelectorAll(`input[name="${name}[]"]`);
                        existingInputs.forEach(input => input.remove());

                        selectedValues.forEach(value => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = `${name}[]`;
                            input.value = value;
                            inputContainer.appendChild(input);
                        });
                    };

                    // Helper function to update dropdown options
                    const updateDropdown = (query = '') => {
                        optionsContainer.innerHTML = '';
                        const selectedValues = Array.from(container.querySelectorAll('.js-selected-option'))
                            .map(opt => opt.dataset.value);
                        const filteredOptions = options.filter(option =>
                            option.label.toLowerCase().includes(query.toLowerCase()) &&
                            !selectedValues.includes(option.value)
                        );

                        if (filteredOptions.length === 0) {
                            noOptions.classList.remove('hidden');
                            dropdown.classList.add('hidden');
                        } else {
                            noOptions.classList.add('hidden');
                            dropdown.classList.remove('hidden');
                            filteredOptions.forEach(option => {
                                const optionElement = createDropdownOption(option);
                                optionsContainer.appendChild(optionElement);
                            });
                        }
                    };

                    // Select All functionality
                    selectAllBtn.addEventListener('click', () => {
                        if (!multiple) return;

                        container.querySelectorAll('.js-selected-option').forEach(option => option
                            .remove());
                        const selectedValues = [];
                        options.forEach(option => {
                            const selectedOption = createSelectedOption(option);
                            container.insertBefore(selectedOption, searchInput);
                            selectedValues.push(option.value);
                        });

                        updateHiddenInputs(selectedValues);
                        searchInput.placeholder = '';
                        dropdown.classList.add('hidden');
                    });

                    // Deselect All functionality
                    deselectAllBtn.addEventListener('click', () => {
                        if (!multiple) return;

                        container.querySelectorAll('.js-selected-option').forEach(option => option
                            .remove());
                        updateHiddenInputs([]);
                        searchInput.placeholder = placeholder;
                        dropdown.classList.add('hidden');
                    });

                    // Search input functionality
                    searchInput.addEventListener('input', () => {
                        const query = searchInput.value.trim();
                        updateDropdown(query);
                    });

                    // Show dropdown when search input is focused
                    searchInput.addEventListener('focus', () => {
                        updateDropdown(searchInput.value.trim());
                    });

                    // Hide dropdown when clicking outside
                    document.addEventListener('click', (e) => {
                        if (!multiSelect.contains(e.target)) {
                            dropdown.classList.add('hidden');
                        }
                    });

                    // Handle option selection from dropdown
                    optionsContainer.addEventListener('click', (e) => {
                        const optionElement = e.target.closest('.js-option');
                        if (optionElement) {
                            const value = optionElement.dataset.value;
                            const option = options.find(opt => opt.value === value);
                            if (option && multiple) {
                                const selectedOption = createSelectedOption(option);
                                container.insertBefore(selectedOption, searchInput);
                                const selectedValues = Array.from(container.querySelectorAll(
                                    '.js-selected-option')).map(opt => opt.dataset.value);
                                updateHiddenInputs(selectedValues);
                                searchInput.value = '';
                                searchInput.placeholder = '';
                                updateDropdown();
                            }
                        }
                    });

                    // Handle remove option
                    container.addEventListener('click', (e) => {
                        const removeBtn = e.target.closest('.js-remove-option');
                        if (removeBtn) {
                            const option = removeBtn.closest('.js-selected-option');
                            option.remove();
                            const selectedValues = Array.from(container.querySelectorAll(
                                '.js-selected-option')).map(opt => opt.dataset.value);
                            updateHiddenInputs(selectedValues);
                            searchInput.placeholder = selectedValues.length === 0 ? placeholder : '';
                            updateDropdown(searchInput.value.trim());
                        }
                    });
                });
            });
        </script>
    @endpush
</x-layouts.app>
