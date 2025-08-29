<x-layouts.app>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
            {{ trans('global.budget.title') }}
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">
            {{ trans('global.add') }} {{ trans('global.budget.title_singular') }}
        </p>
    </div>

    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden p-6">
        <form id="budget-form" class="w-full" action="{{ route('admin.budget.store') }}" method="POST"
            enctype="multipart/form-data">
            @csrf

            @if ($errors->any())
                <div
                    class="col-span-full mb-6 p-4 bg-red-100 text-red-800 border border-red-300 rounded-lg dark:bg-red-900 dark:text-red-200 dark:border-red-700">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div id="error-message"
                class="col-span-full mb-6 hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative dark:bg-red-900 dark:border-red-700 dark:text-red-200">
                <span id="error-text"></span>
                <button type="button" id="close-error" class="absolute top-0 right-0 px-4 py-3">
                    <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20">
                        <path
                            d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
                    </svg>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="p-6 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                        <h3
                            class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-600">
                            {{ trans('global.budget.title') }} {{ trans('global.information') }}
                        </h3>
                        <div class="space-y-6">
                            <x-forms.select label="{{ trans('global.budget.fields.project_id') }}" name="project_id"
                                id="project_id" :options="collect($projects)
                                    ->map(fn($label, $value) => ['value' => (string) $value, 'label' => $label])
                                    ->values()
                                    ->all()" :selected="old('project_id', $projectId ?? '')"
                                placeholder="{{ trans('global.pleaseSelect') }}" :error="$errors->first('project_id')"
                                class="js-single-select" />

                            <x-forms.select label="{{ trans('global.budget.fields.fiscal_year_id') }}"
                                name="fiscal_year_id" id="fiscal_year_id" :options="collect($fiscalYears)
                                    ->map(fn($label, $value) => ['value' => (string) $value, 'label' => $label])
                                    ->values()
                                    ->all()" :selected="old('fiscal_year_id')"
                                placeholder="{{ trans('global.pleaseSelect') }}" :error="$errors->first('fiscal_year_id')"
                                class="js-single-select" />

                            <!-- Government Budget Section -->
                            <div>
                                <h4 class="text-lg font-medium text-gray-700 dark:text-gray-200 mb-4">
                                    {{ trans('global.budget.headers.governmentBudget') }}
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <x-forms.input label="{{ trans('global.budget.fields.government_loan') }}"
                                        name="government_loan" type="number" step="0.01" min="0"
                                        :value="old('government_loan', '')" :error="$errors->first('government_loan')" class="budget-component"
                                        placeholder="0.00" />
                                    <x-forms.input label="{{ trans('global.budget.fields.government_share') }}"
                                        name="government_share" type="number" step="0.01" min="0"
                                        :value="old('government_share', '')" :error="$errors->first('government_share')" class="budget-component"
                                        placeholder="0.00" />
                                </div>
                            </div>

                            <!-- Foreign Budget Section -->
                            <div>
                                <h4 class="text-lg font-medium text-gray-700 dark:text-gray-200 mb-4">
                                    {{ trans('global.budget.headers.foreignBudget') }}
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <x-forms.input label="{{ trans('global.budget.fields.foreign_loan_budget') }}"
                                        name="foreign_loan_budget" type="number" step="0.01" min="0"
                                        :value="old('foreign_loan_budget', '')" :error="$errors->first('foreign_loan_budget')" class="budget-component"
                                        placeholder="0.00" />
                                    <x-forms.input label="{{ trans('global.budget.fields.foreign_subsidy_budget') }}"
                                        name="foreign_subsidy_budget" type="number" step="0.01" min="0"
                                        :value="old('foreign_subsidy_budget', '')" :error="$errors->first('foreign_subsidy_budget')" class="budget-component"
                                        placeholder="0.00" />
                                </div>
                            </div>

                            <!-- Internal Budget Section -->
                            <div>
                                <h4 class="text-lg font-medium text-gray-700 dark:text-gray-200 mb-4">
                                    {{ trans('global.budget.fields.internal_budget') }}
                                </h4>
                                <x-forms.input label="{{ trans('global.budget.fields.internal_budget') }}"
                                    name="internal_budget" type="number" step="0.01" min="0"
                                    :value="old('internal_budget', '')" :error="$errors->first('internal_budget')" class="budget-component" placeholder="0.00" />
                            </div>

                            <!-- Total Budget -->
                            <x-forms.input label="{{ trans('global.budget.fields.total_budget') }}" name="total_budget"
                                type="number" step="0.01" min="0" :value="old('total_budget', '')" :error="$errors->first('total_budget')"
                                readonly="true"
                                class="bg-gray-100 dark:bg-gray-600 cursor-not-allowed text-gray-700 dark:text-gray-300" />
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="p-6 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                        <h3
                            class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-600">
                            {{ trans('global.details') }}
                        </h3>
                        <div class="space-y-6">
                            <x-forms.date-input label="{{ trans('global.budget.fields.decision_date') }}"
                                name="decision_date" type="text" :value="old('decision_date', '')" :error="$errors->first('decision_date')" />
                            <x-forms.text-area label="{{ trans('global.budget.fields.remarks') }}" name="remarks"
                                :value="old('remarks')" :error="$errors->first('remarks')" />
                            <x-forms.file-input label="{{ trans('global.budget.fields.upload_documents') }}"
                                name="files" multiple accept=".pdf,.png,.jpg" maxSize="2MB" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-span-full mt-8 flex">
                <x-buttons.primary id="submit-button" type="submit" :disabled="false">
                    {{ trans('global.save') }}
                </x-buttons.primary>
                <a href="{{ route('admin.budget.index') }}"
                    class="ml-4 inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </form>
    </div>

    <style>
        .js-options-container::-webkit-scrollbar {
            width: 8px;
        }

        .js-options-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .js-options-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .js-options-container::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .dark .js-options-container::-webkit-scrollbar-track {
            background: #1f2937;
        }

        .dark .js-options-container::-webkit-scrollbar-thumb {
            background: #6b7280;
        }

        .dark .js-options-container::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        .js-options-container {
            scrollbar-width: thin;
            scrollbar-color: #888 #f1f1f1;
        }

        .dark .js-options-container {
            scrollbar-color: #6b7280 #1f2937;
        }

        input[readonly] {
            opacity: 1 !important;
            color: inherit !important;
        }
    </style>

    @push('scripts')
        <script>
            function waitForJQuery(callback, retries = 50) {
                if (
                    typeof jQuery !== "undefined" &&
                    jQuery.fn.jquery &&
                    document.readyState === "complete"
                ) {
                    console.log('jQuery loaded and DOM ready:', jQuery.fn.jquery);
                    callback();
                } else if (retries > 0) {
                    console.log('Waiting for jQuery, retries left:', retries);
                    setTimeout(function() {
                        waitForJQuery(callback, retries - 1);
                    }, 100);
                } else {
                    console.error('jQuery failed to load after retries');
                }
            }

            waitForJQuery(function() {
                const $ = jQuery.noConflict();

                // Budget calculation logic
                function updateTotalBudget() {
                    const $governmentLoan = $('input[name="government_loan"]');
                    const $governmentShare = $('input[name="government_share"]');
                    const $foreignLoan = $('input[name="foreign_loan_budget"]');
                    const $foreignSubsidy = $('input[name="foreign_subsidy_budget"]');
                    const $internal = $('input[name="internal_budget"]');
                    const $total = $('input[name="total_budget"]');
                    const $errorMessage = $('#error-message');
                    const $errorText = $('#error-text');

                    if (!$governmentLoan.length || !$governmentShare.length || !$foreignLoan.length || !$foreignSubsidy
                        .length || !$internal.length || !$total.length) {
                        $errorText.text('Error: One or more budget inputs not found in the DOM.');
                        $errorMessage.removeClass('hidden');
                        console.error('Budget inputs missing:', {
                            government_loan: $governmentLoan.length,
                            government_share: $governmentShare.length,
                            foreign_loan: $foreignLoan.length,
                            foreign_subsidy: $foreignSubsidy.length,
                            internal: $internal.length,
                            total: $total.length
                        });
                        return;
                    }

                    const getValidNumber = (value) => {
                        const num = parseFloat(value);
                        return isNaN(num) || num < 0 ? 0 : num;
                    };

                    const governmentLoan = getValidNumber($governmentLoan.val());
                    const governmentShare = getValidNumber($governmentShare.val());
                    const foreignLoan = getValidNumber($foreignLoan.val());
                    const foreignSubsidy = getValidNumber($foreignSubsidy.val());
                    const internal = getValidNumber($internal.val());

                    const total = (governmentLoan + governmentShare + foreignLoan + foreignSubsidy + internal).toFixed(
                        2);
                    $total.val(total).trigger('change').trigger('input');
                    $total[0].dispatchEvent(new Event('input', {
                        bubbles: true
                    }));
                    if (window.Livewire) {
                        window.Livewire.dispatch('input', {
                            name: 'total_budget',
                            value: total
                        });
                    }
                    $errorMessage.addClass('hidden');
                    console.log('Total budget updated:', total);
                }

                // Debounce to prevent excessive updates
                function debounce(func, wait) {
                    let timeout;
                    return function() {
                        clearTimeout(timeout);
                        timeout = setTimeout(func, wait);
                    };
                }

                // Bind events by class with delegation
                $(document).on('input change keyup', '.budget-component', function(e) {
                    debounce(updateTotalBudget, 300).call(this);
                });

                // Bind events by name as fallback
                const inputNames = ['government_loan', 'government_share', 'foreign_loan_budget',
                    'foreign_subsidy_budget', 'internal_budget'
                ];
                inputNames.forEach(function(name) {
                    const $input = $(`input[name="${name}"]`);
                    if ($input.length) {
                        $input.on('input change keyup', function(e) {
                            debounce(updateTotalBudget, 300).call(this);
                        });
                    }
                });

                // Form submission handling
                const $form = $('#budget-form');
                const $submitButton = $('#submit-button');

                $form.on('submit', function(e) {
                    console.log('Form submit attempted');
                    if ($submitButton.prop('disabled')) {
                        e.preventDefault();
                        console.log('Form submission prevented: button is disabled');
                        return;
                    }

                    $submitButton
                        .prop('disabled', true)
                        .addClass('opacity-50 cursor-not-allowed')
                        .text('{{ trans('global.saving') }}...');
                    console.log('Submit button disabled');

                    // Perform AJAX form submission
                    e.preventDefault();
                    $.ajax({
                        url: $form.attr('action'),
                        method: 'POST',
                        data: new FormData($form[0]),
                        processData: false,
                        contentType: false,
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                            "X-Requested-With": "XMLHttpRequest",
                        },
                        success: function(response) {
                            console.log('Form submission success:', response);
                            $("#error-message").addClass("hidden");
                            $("#error-text").text("");
                            window.location.href = '{{ route('admin.budget.index') }}';
                        },
                        error: function(xhr) {
                            console.error('Form submission error:', xhr.status, xhr.responseJSON);
                            $submitButton
                                .prop('disabled', false)
                                .removeClass('opacity-50 cursor-not-allowed')
                                .text('{{ trans('global.save') }}');
                            $("#error-message").removeClass("hidden");
                            $("#error-text").text(
                                xhr.responseJSON?.message ||
                                "{{ trans('global.budget.errors.create_failed') }}"
                            );
                        }
                    });
                });

                // Initialize total budget on page load
                updateTotalBudget();

                // Close error message
                $("#close-error").on("click", function() {
                    $("#error-message").addClass("hidden");
                    $("#error-text").text("");
                });

                // Select dropdown logic
                $(".js-single-select").each(function() {
                    const $container = $(this);
                    const componentId = $container.attr("id");
                    const dataName = $container.data("name");
                    let currentOptions = $container.data("options") || [];
                    let currentSelectedValue = $container.data("selected") || "";
                    const $optionsContainer = $container.find(".js-options-container");
                    const $selectedLabel = $container.find(".js-selected-label");
                    const $hiddenInput = $container.find("input.js-hidden-input");
                    const $dropdown = $container.find(".js-dropdown");
                    const $searchInput = $container.find(".js-search-input");

                    function renderOptions(searchTerm = "") {
                        $optionsContainer.empty();
                        if (!currentOptions || currentOptions.length === 0) {
                            $container.find(".js-no-options").removeClass("hidden");
                            $selectedLabel.text($container.data("placeholder") ||
                                "{{ trans('global.pleaseSelect') }}");
                            $hiddenInput.val("");
                            currentSelectedValue = "";
                            $container.data("selected", "");
                            $container.attr("data-selected", "");
                            return;
                        }
                        $container.find(".js-no-options").addClass("hidden");
                        const filteredOptions = searchTerm ?
                            currentOptions.filter((option) => option.label.toLowerCase().includes(searchTerm
                                .toLowerCase())) :
                            currentOptions;
                        $.each(filteredOptions, function(index, option) {
                            const $option = $(`
                                <div class="js-option cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600" data-value="${option.value}">
                                    ${option.label}
                                </div>
                            `);
                            $optionsContainer.append($option);
                        });
                        const selectedOption = currentOptions.find(
                            (opt) => String(opt.value) === String(currentSelectedValue)
                        );
                        $selectedLabel.text(
                            selectedOption ?
                            selectedOption.label :
                            $container.data("placeholder") || "{{ trans('global.pleaseSelect') }}"
                        );
                        $hiddenInput.val(selectedOption ? currentSelectedValue : "");
                        if (!selectedOption) {
                            currentSelectedValue = "";
                            $container.data("selected", "");
                            $container.attr("data-selected", "");
                        }
                    }

                    function updateHiddenInput() {
                        $hiddenInput.val(currentSelectedValue || "");
                        $hiddenInput.trigger("change");
                        console.log(`${dataName} selected:`, currentSelectedValue);
                    }

                    $container.off("options-updated").on("options-updated", function(event, data) {
                        currentOptions = data?.options || $container.data("options") || [];
                        currentSelectedValue =
                            data?.selected &&
                            currentOptions.some((opt) => String(opt.value) === String(data.selected)) ?
                            String(data.selected) :
                            "";
                        renderOptions();
                        updateHiddenInput();
                    });

                    $optionsContainer.off("click", ".js-option").on("click", ".js-option", function(e) {
                        e.stopPropagation();
                        const $option = $(this);
                        currentSelectedValue = $option.data("value");
                        $container.data("selected", currentSelectedValue);
                        $container.attr("data-selected", currentSelectedValue);
                        $selectedLabel.text($option.text().trim());
                        $dropdown.addClass("hidden");
                        updateHiddenInput();
                    });

                    $searchInput.off("input").on("input", function() {
                        renderOptions($(this).val());
                    });

                    $container.find(".js-toggle-dropdown").off("click").on("click", function(e) {
                        e.stopPropagation();
                        $dropdown.toggleClass("hidden");
                        console.log(`Dropdown toggled for #${componentId}:`, !$dropdown.hasClass(
                            "hidden"));
                        if (!$dropdown.hasClass("hidden")) {
                            $searchInput.focus();
                        }
                    });

                    $(document).off("click.dropdown-" + componentId).on("click.dropdown-" + componentId,
                        function(e) {
                            if (!$container.is(e.target) && $container.has(e.target).length === 0) {
                                $dropdown.addClass("hidden");
                                console.log(`Dropdown closed for #${componentId}`);
                            }
                        });

                    renderOptions();
                    console.log(`Options parsed for #${componentId}:`, currentOptions);
                    console.log(`Selected parsed for #${componentId}:`, currentSelectedValue);
                });

                // Pre-select project if project_id is provided
                const projectId = "{{ $projectId ?? '' }}";
                if (projectId) {
                    const projectSelectContainer = $('.js-single-select[data-name="project_id"]');
                    projectSelectContainer.data('selected', projectId);
                    projectSelectContainer.attr('data-selected', projectId);
                    projectSelectContainer.trigger('options-updated', {
                        options: projectSelectContainer.data('options'),
                        selected: projectId,
                    });
                } else {
                    const projectSelectContainer = $('.js-single-select[data-name="project_id"]');
                    projectSelectContainer.data('selected', '');
                    projectSelectContainer.attr('data-selected', '');
                    projectSelectContainer.trigger('options-updated', {
                        options: projectSelectContainer.data('options'),
                        selected: '',
                    });
                }
            });
        </script>
    @endpush
</x-layouts.app>
