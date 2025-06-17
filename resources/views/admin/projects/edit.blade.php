<x-layouts.app>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Project') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Edit project') }}</p>
    </div>

    <div class="flex flex-col md:flex-row gap-6">
        <div class="flex-1">
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6 p-6">
                <form class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8"
                    action="{{ route('admin.project.update', $project) }}" method="POST">
                    @csrf
                    @method('PUT')

                    @if ($errors->any())
                        <div class="col-span-full mb-6 p-4 bg-red-100 text-red-800 border border-red-300 rounded-lg">
                            <ul class="list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div id="error-message"
                        class="col-span-full mb-6 hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative">
                        <span id="error-text"></span>
                        <button type="button" id="close-error" class="absolute top-0 right-0 px-4 py-3">
                            <svg class="fill-current h-6 w-6 text-red-500" role="button"
                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path
                                    d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
                            </svg>
                        </button>
                    </div>

                    <div
                        class="lg:col-span-1 p-6 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                        <h3
                            class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-600">
                            {{ __('Project Information') }}
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="col-span-full">
                                <x-forms.select label="Directorate" name="directorate_id" id="directorate_id"
                                    :options="collect($directorates)
                                        ->map(fn($label, $value) => ['value' => (string) $value, 'label' => $label])
                                        ->values()
                                        ->all()" :selected="old('directorate_id', $project->directorate_id)" placeholder="Select directorate"
                                    :error="$errors->first('directorate_id')" class="js-single-select" />
                            </div>

                            <div class="col-span-full">
                                <x-forms.select label="Department" name="department_id" id="department_select"
                                    :options="collect($departments)
                                        ->map(fn($label, $value) => ['value' => (string) $value, 'label' => $label])
                                        ->values()
                                        ->all()" :selected="old('department_id', $project->department_id)" placeholder="Select department"
                                    allow-clear="true"
                                    data-selected="{{ old('department_id', $project->department_id) }}"
                                    :error="$errors->first('department_id')" class="js-single-select" />
                            </div>

                            <div class="col-span-full">
                                <x-forms.select label="Project Manager" name="project_manager"
                                    id="project_manager_select" :options="collect($users)
                                        ->map(fn($label, $value) => ['value' => (string) $value, 'label' => $label])
                                        ->values()
                                        ->all()" :selected="old('project_manager', $project->project_manager)"
                                    placeholder="Select project manager" allow-clear="true"
                                    data-selected="{{ old('project_manager', $project->project_manager) }}"
                                    :error="$errors->first('project_manager')" class="js-single-select" />
                            </div>

                            <div class="col-span-full">
                                <x-forms.input label="Title" name="title" type="text" :value="old('title', $project->title)"
                                    :error="$errors->first('title')" />
                            </div>

                            <div class="col-span-full">
                                <x-forms.text-area label="Description" name="description" :value="old('description', $project->description)"
                                    :error="$errors->first('description')" />
                            </div>

                            <div>
                                <x-forms.input label="Start Date" name="start_date" type="date" :value="old(
                                    'start_date',
                                    $project->start_date
                                        ? \Carbon\Carbon::parse($project->start_date)->format('Y-m-d')
                                        : '',
                                )"
                                    :error="$errors->first('start_date')" />
                            </div>

                            <div>
                                <x-forms.input label="End Date" name="end_date" type="date" :value="old(
                                    'end_date',
                                    $project->end_date
                                        ? \Carbon\Carbon::parse($project->end_date)->format('Y-m-d')
                                        : '',
                                )"
                                    :error="$errors->first('end_date')" />
                            </div>

                            <div>
                                <x-forms.select label="Status" name="status_id" id="status_id" :options="collect($statuses)
                                    ->map(fn($label, $value) => ['value' => (string) $value, 'label' => $label])
                                    ->values()
                                    ->all()"
                                    :selected="old('status_id', $project->status_id)" placeholder="Select status" :error="$errors->first('status_id')"
                                    class="js-single-select" />
                            </div>

                            <div>
                                <x-forms.select label="Priority" name="priority_id" id="priority_id" :options="collect($priorities)
                                    ->map(fn($label, $value) => ['value' => (string) $value, 'label' => $label])
                                    ->values()
                                    ->all()"
                                    :selected="old('priority_id', $project->priority_id)" placeholder="Select priority" :error="$errors->first('priority_id')"
                                    class="js-single-select" />
                            </div>
                        </div>
                    </div>

                    <div
                        class="lg:col-span-2 p-6 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                        <div class="flex items-center gap-2 mb-4 pb-2 border-b border-gray-200 dark:border-gray-600">
                            <div class="text-indigo-600 dark:text-indigo-400">
                                <i class="fa fa-dollar-sign text-xl"></i>
                            </div>
                            <h5 class="text-xl font-semibold text-gray-800 dark:text-gray-100">
                                {{ trans('global.project.headers.budget_details') }}
                            </h5>
                        </div>

                        <div class="overflow-x-auto">
                            <table id="budget-entries-table"
                                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-100 dark:bg-gray-600">
                                    <tr>
                                        <th scope="col"
                                            class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            {{ trans('global.project.fields.fiscal_year_id') }}
                                        </th>
                                        <th scope="col"
                                            class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            {{ trans('global.project.fields.total_budget') }}
                                        </th>
                                        <th scope="col"
                                            class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            {{ trans('global.project.fields.internal_budget') }}
                                        </th>
                                        <th scope="col"
                                            class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            {{ trans('global.project.fields.foreign_loan_budget') }}
                                        </th>
                                        <th scope="col"
                                            class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            {{ trans('global.project.fields.foreign_subsidy_budget') }}
                                        </th>
                                        <th scope="col"
                                            class="px-3 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-12">
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                    @php
                                        $initialBudgets = old('budgets', $project->budgets->toArray());
                                        if (empty($initialBudgets)) {
                                            $initialBudgets = [[]];
                                        }
                                    @endphp

                                    @foreach ($initialBudgets as $index => $budget)
                                        <tr class="budget-entry-row">
                                            <td class="px-3 py-3 whitespace-nowrap">
                                                <x-forms.select name="budgets[{{ $index }}][fiscal_year_id]"
                                                    id="fiscal_year_id_{{ $index }}" :options="collect($fiscalYears)
                                                        ->map(
                                                            fn($label, $value) => [
                                                                'value' => (string) $value,
                                                                'label' => $label,
                                                            ],
                                                        )
                                                        ->values()
                                                        ->all()"
                                                    :selected="old(
                                                        'budgets.' . $index . '.fiscal_year_id',
                                                        $budget['fiscal_year_id'] ?? null,
                                                    )" placeholder="{{ trans('global.pleaseSelect') }}"
                                                    :error="$errors->first('budgets.' . $index . '.fiscal_year_id')" class="w-full js-single-select" />
                                            </td>
                                            <td class="px-3 py-3 whitespace-nowrap">
                                                <x-forms.input name="budgets[{{ $index }}][total_budget]"
                                                    type="number" step="0.01" min="0" :value="old(
                                                        'budgets.' . $index . '.total_budget',
                                                        $budget['total_budget'] ?? '',
                                                    )"
                                                    :error="$errors->first('budgets.' . $index . '.total_budget')" class="w-full" />
                                            </td>
                                            <td class="px-3 py-3 whitespace-nowrap">
                                                <x-forms.input name="budgets[{{ $index }}][internal_budget]"
                                                    type="number" step="0.01" min="0" :value="old(
                                                        'budgets.' . $index . '.internal_budget',
                                                        $budget['internal_budget'] ?? '',
                                                    )"
                                                    :error="$errors->first('budgets.' . $index . '.internal_budget')" class="w-full" />
                                            </td>
                                            <td class="px-3 py-3 whitespace-nowrap">
                                                <x-forms.input
                                                    name="budgets[{{ $index }}][foreign_loan_budget]"
                                                    type="number" step="0.01" min="0" :value="old(
                                                        'budgets.' . $index . '.foreign_loan_budget',
                                                        $budget['foreign_loan_budget'] ?? '',
                                                    )"
                                                    :error="$errors->first(
                                                        'budgets.' . $index . '.foreign_loan_budget',
                                                    )" class="w-full" />
                                            </td>
                                            <td class="px-3 py-3 whitespace-nowrap">
                                                <x-forms.input
                                                    name="budgets[{{ $index }}][foreign_subsidy_budget]"
                                                    type="number" step="0.01" min="0" :value="old(
                                                        'budgets.' . $index . '.foreign_subsidy_budget',
                                                        $budget['foreign_subsidy_budget'] ?? '',
                                                    )"
                                                    :error="$errors->first(
                                                        'budgets.' . $index . '.foreign_subsidy_budget',
                                                    )" class="w-full" />
                                            </td>
                                            <td class="px-3 py-3 whitespace-nowrap text-right text-sm font-medium">
                                                <button type="button"
                                                    class="remove-budget-btn inline-flex items-center justify-center p-2 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:bg-red-700 dark:hover:bg-red-800 dark:focus:ring-red-600 {{ count($initialBudgets) === 1 ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                    {{ count($initialBudgets) === 1 ? 'disabled' : '' }}>
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <button type="button" id="add-budget-row"
                            class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-indigo-700 dark:hover:bg-indigo-800 dark:focus:ring-indigo-600">
                            <i class="fa fa-plus mr-2"></i> {{ trans('global.project.fields.add_budget') }}
                        </button>
                    </div>

                    <div class="col-span-full mt-8">
                        <x-buttons.primary>{{ __('Save') }}</x-buttons.primary>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Wait for jQuery and DOM to be ready
        function waitForJQuery(callback, retries = 50, interval = 100) {
            if (typeof jQuery !== "undefined" && jQuery.fn.jquery && document.readyState !== "loading") {
                console.log("jQuery and DOM ready. jQuery version:", jQuery.fn.jquery, "DOM state:", document.readyState);
                callback();
            } else if (retries > 0) {
                console.warn("jQuery or DOM not ready, retrying... jQuery:", typeof jQuery !== "undefined" ? jQuery.fn
                    .jquery : "undefined", "DOM:", document.readyState, "Retries left:", retries);
                setTimeout(function() {
                    waitForJQuery(callback, retries - 1, interval);
                }, interval);
            } else {
                console.error("Failed to load jQuery or DOM after maximum retries.");
                callback(); // Proceed to avoid complete failure
            }
        }

        waitForJQuery(function() {
            const $ = jQuery;

            // Custom Dropdown Component Logic
            $(".js-single-select").each(function() {
                const $container = $(this);
                const componentId = $container.attr("id");
                const dataName = $container.data("name");
                let currentOptions = $container.data("options") || [];
                let currentSelectedValue = $container.data("selected") || "";
                const $optionsContainer = $container.find(".js-options-container");
                const $selectedLabel = $container.find(".js-selected-label");
                const $hiddenInput = $container.find(".js-hidden-input");
                const $dropdown = $container.find(".js-dropdown");
                const $searchInput = $container.find(".js-search-input");

                if (!$optionsContainer.length || !$hiddenInput.length) {
                    console.error(`Required elements not found in container #${componentId}. HTML:`,
                        $container[0].outerHTML);
                    return;
                }

                function renderOptions(searchTerm = "") {
                    console.log(`Rendering options for #${componentId}:`, {
                        options: currentOptions,
                        selected: currentSelectedValue,
                        searchTerm
                    });
                    $optionsContainer.empty();
                    $container.find(".js-no-options").toggleClass("hidden", currentOptions.length > 0);
                    if (!currentOptions.length) {
                        $selectedLabel.text($container.data("placeholder") || "Select an option");
                        $hiddenInput.val("");
                        currentSelectedValue = "";
                        $container.data("selected", "");
                        $container.attr("data-selected", "");
                        return;
                    }
                    const filteredOptions = searchTerm ?
                        currentOptions.filter((opt) => opt.label.toLowerCase().includes(searchTerm
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
                    const selectedOption = currentOptions.find((opt) => String(opt.value) === String(
                        currentSelectedValue));
                    $selectedLabel.text(selectedOption ? selectedOption.label : $container.data(
                        "placeholder") || "Select an option");
                    $hiddenInput.val(selectedOption ? currentSelectedValue : "");
                    if (!selectedOption) {
                        currentSelectedValue = "";
                        $container.data("selected", "");
                        $container.attr("data-selected", "");
                    }
                }

                function updateHiddenInput() {
                    $hiddenInput.val(currentSelectedValue || "").trigger("change");
                    console.log(`Updated hidden input for #${componentId}:`, {
                        name: dataName,
                        value: currentSelectedValue
                    });
                }

                $container.off("options-updated").on("options-updated", function(event, data) {
                    console.log(`Options-updated event for #${componentId}:`, data);
                    currentOptions = data?.options || [];
                    currentSelectedValue = data?.selected && currentOptions.some((opt) => String(opt
                        .value) === String(data.selected)) ? String(data.selected) : "";
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
                    console.log(`Option selected for #${componentId}:`, currentSelectedValue);
                });

                $searchInput.off("input").on("input", function() {
                    renderOptions($(this).val());
                });

                $container.find(".js-toggle-dropdown").off("click").on("click", function(e) {
                    e.stopPropagation();
                    $dropdown.toggleClass("hidden");
                    if (!$dropdown.hasClass("hidden")) {
                        $searchInput.focus();
                    }
                    console.log(`Toggled dropdown for #${componentId}:`, !$dropdown.hasClass(
                        "hidden"));
                });

                $(document).off("click.dropdown-" + componentId).on("click.dropdown-" + componentId,
                    function(e) {
                        if (!$container.is(e.target) && $container.has(e.target).length === 0) {
                            $dropdown.addClass("hidden");
                        }
                    });

                console.log(`Initializing dropdown #${componentId}:`, {
                    options: currentOptions,
                    selected: currentSelectedValue
                });
                renderOptions();
            });

            // Dependent Dropdown Logic
            const directorateContainer = $('.js-single-select[data-name="directorate_id"]');
            const directorateInput = directorateContainer.find(".js-hidden-input");
            const departmentSelectContainer = $('.js-single-select[data-name="department_id"]');
            const userSelectContainer = $('.js-single-select[data-name="project_manager"]');

            if (!directorateContainer.length || !directorateInput.length || !departmentSelectContainer.length || !
                userSelectContainer.length) {
                console.error("Required dropdown containers not found.", {
                    directorate: directorateContainer.length,
                    directorateInput: directorateInput.length,
                    department: departmentSelectContainer.length,
                    user: userSelectContainer.length
                });
                return;
            }

            function updateSelectOptions(container, options, selected = "") {
                console.log(`Updating select options for #${container.attr("id")}:`, {
                    options,
                    selected
                });
                container.data("options", options);
                container.data("selected", selected);
                container.attr("data-selected", selected);
                container.find(".js-hidden-input").val(selected).trigger("change");
                container.find(".js-selected-label").text(
                    options.find((opt) => String(opt.value) === String(selected))?.label ||
                    container.data("placeholder") || "Select an option"
                );
                container.find(".js-options-container").empty();
                container.find(".js-no-options").toggleClass("hidden", options.length > 0);
                if (options.length > 0) {
                    $.each(options, function(index, option) {
                        const $option = $(`
                            <div class="js-option cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600" data-value="${option.value}">
                                ${option.label}
                            </div>
                        `);
                        container.find(".js-options-container").append($option);
                    });
                }
                container.trigger("options-updated", {
                    options,
                    selected
                });
            }

            function loadDepartments(directorateId, selectedDepartmentId = "") {
                if (!directorateId) {
                    console.log("No directorate ID, clearing department options");
                    updateSelectOptions(departmentSelectContainer, [], "");
                    return;
                }
                const $optionsContainer = departmentSelectContainer.find(".js-options-container");
                $optionsContainer.empty().append(
                    '<div class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">Loading...</div>');

                $.ajax({
                    url: `/admin/projects/departments/${encodeURIComponent(directorateId)}`,
                    method: "GET",
                    dataType: "json",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    beforeSend: function() {
                        console.log(
                            `AJAX request started for departments: /admin/projects/departments/${directorateId}`
                            );
                    },
                    success: function(data) {
                        console.log("Departments response:", data);
                        const formattedData = Array.isArray(data) ?
                            data.map((dept) => ({
                                value: String(dept.value),
                                label: String(dept.label)
                            })).filter((opt) => opt.value && opt.label) : [];
                        updateSelectOptions(departmentSelectContainer, formattedData,
                            selectedDepartmentId);
                    },
                    error: function(xhr) {
                        console.error("AJAX error for departments:", xhr.status, xhr.statusText, xhr
                            .responseJSON);
                        updateSelectOptions(departmentSelectContainer, [], "");
                        $optionsContainer.empty().append(
                            '<div class="px-4 py-2 text-sm text-red-500 dark:text-red-400">Failed to load departments</div>'
                            );
                        $("#error-message").removeClass("hidden").find("#error-text").text(
                            "Failed to load departments: " + (xhr.responseJSON?.message ||
                                "Unknown error"));
                    },
                    complete: function() {
                        console.log(`AJAX request for departments completed: ${directorateId}`);
                    },
                });
            }

            function loadUsers(directorateId, selectedUserId = "") {
                if (!directorateId) {
                    console.log("No directorate ID, clearing user options");
                    updateSelectOptions(userSelectContainer, [], "");
                    return;
                }
                const $optionsContainer = userSelectContainer.find(".js-options-container");
                $optionsContainer.empty().append(
                    '<div class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">Loading...</div>');

                $.ajax({
                    url: `/admin/projects/users/${encodeURIComponent(directorateId)}`,
                    method: "GET",
                    dataType: "json",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    beforeSend: function() {
                        console.log(
                            `AJAX request started for users: /admin/projects/users/${directorateId}`
                            );
                    },
                    success: function(data) {
                        console.log("Users response:", data);
                        const formattedData = Array.isArray(data) ?
                            data.map((user) => ({
                                value: String(user.value),
                                label: String(user.label)
                            })).filter((opt) => opt.value && opt.label) : [];
                        updateSelectOptions(userSelectContainer, formattedData, selectedUserId);
                    },
                    error: function(xhr) {
                        console.error("AJAX error for users:", xhr.status, xhr.statusText, xhr
                            .responseJSON);
                        updateSelectOptions(userSelectContainer, [], "");
                        $optionsContainer.empty().append(
                            '<div class="px-4 py-2 text-sm text-red-500 dark:text-red-400">Failed to load users</div>'
                            );
                        $("#error-message").removeClass("hidden").find("#error-text").text(
                            "Failed to load users: " + (xhr.responseJSON?.message ||
                                "Unknown error"));
                    },
                    complete: function() {
                        console.log(`AJAX request for users completed: ${directorateId}`);
                    },
                });
            }

            directorateInput.off("change").on("change", function() {
                const directorateId = $(this).val();
                console.log("Directorate changed:", directorateId);
                loadDepartments(directorateId, "");
                loadUsers(directorateId, "");
            });

            if (directorateInput.val()) {
                console.log("Initial directorate:", directorateInput.val());
                const initialDepartmentId = "{{ old('department_id', $project->department_id) }}";
                const initialUserId = "{{ old('project_manager', $project->project_manager) }}";
                loadDepartments(directorateInput.val(), initialDepartmentId);
                loadUsers(directorateInput.val(), initialUserId);
            } else {
                console.log("No initial directorate, clearing department and user options");
                updateSelectOptions(departmentSelectContainer, [], "");
                updateSelectOptions(userSelectContainer, [], "");
            }

            $("#close-error").off("click").on("click", function() {
                $("#error-message").addClass("hidden").find("#error-text").text("");
            });

            // Budget Details Logic
            const budgetTableBody = $("#budget-entries-table tbody");
            const addBudgetButton = $("#add-budget-row");

            function updateRowIndices() {
                budgetTableBody.find(".budget-entry-row").each(function(index) {
                    const $row = $(this);

                    // Update fiscal year select
                    $row.find(".js-single-select").each(function() {
                        const $container = $(this);
                        const name = $container.data("name");
                        if (name && name.includes("fiscal_year_id")) {
                            const newId = `fiscal_year_id_${index}`;
                            $container.attr("id", newId);
                            $container.data("name", `budgets[${index}][fiscal_year_id]`);
                            $container.attr("data-name", `budgets[${index}][fiscal_year_id]`);

                            // Preserve existing selected value
                            const existingSelectedValue = $container.attr("data-selected");
                            const $hiddenInput = $container.find(".js-hidden-input");
                            const $selectedLabel = $container.find(".js-selected-label");
                            const options = JSON.parse($container.attr("data-options") || "[]");

                            if (!existingSelectedValue) {
                                $container.data("selected", "");
                                $container.attr("data-selected", "");
                                $selectedLabel.text($container.data("placeholder") ||
                                    "Select an option");
                                $hiddenInput.val("");
                            } else {
                                const selectedOption = options.find(
                                    (opt) => String(opt.value) === String(existingSelectedValue)
                                );
                                if (selectedOption) {
                                    $selectedLabel.text(selectedOption.label);
                                    $hiddenInput.val(existingSelectedValue);
                                } else {
                                    $selectedLabel.text($container.data("placeholder") ||
                                        "Select an option");
                                    $hiddenInput.val("");
                                }
                            }

                            $hiddenInput.attr("name", `budgets[${index}][fiscal_year_id]`);
                            console.log(
                                `Updated select container ID: ${newId}, data-name: budgets[${index}][fiscal_year_id]`
                                );
                        }
                    });

                    // Update other inputs
                    $row.find("input:not(.js-hidden-input)").each(function() {
                        const $input = $(this);
                        const currentName = $input.attr("name");
                        const currentId = $input.attr("id");

                        if (currentName) {
                            const newName = currentName.replace(/budgets\[\d+\]/,
                                `budgets[${index}]`);
                            $input.attr("name", newName);
                            console.log(`Updated input name: ${newName}`);
                        } else {
                            console.warn(`Input missing name attribute:`, $input[0].outerHTML);
                        }

                        if (currentId) {
                            const newId = currentId.replace(
                                /^(total_budget|internal_budget|foreign_loan_budget|foreign_subsidy_budget)_\d+/,
                                `$1_${index}`);
                            $input.attr("id", newId);
                            console.log(`Updated input ID: ${newId}`);
                        } else {
                            console.warn(`Input missing ID attribute:`, $input[0].outerHTML);
                        }
                    });

                    // Reinitialize dropdown
                    $row.find(".js-single-select").each(function() {
                        const $container = $(this);
                        const name = $container.data("name");
                        if (name && name.includes("fiscal_year_id")) {
                            const componentId = $container.attr("id");
                            const options = JSON.parse($container.attr("data-options") || "[]");
                            const $optionsContainer = $container.find(".js-options-container");
                            const $selectedLabel = $container.find(".js-selected-label");
                            const $hiddenInput = $container.find(".js-hidden-input");
                            const $dropdown = $container.find(".js-dropdown");
                            const $searchInput = $container.find(".js-search-input");

                            $optionsContainer.empty();
                            if (options.length > 0) {
                                $.each(options, function(i, option) {
                                    const $option = $(`
                                        <div class="js-option cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600" data-value="${option.value}">
                                            ${option.label}
                                        </div>
                                    `);
                                    $optionsContainer.append($option);
                                });
                            } else {
                                $container.find(".js-no-options").removeClass("hidden");
                            }

                            $optionsContainer.off("click", ".js-option").on("click", ".js-option",
                                function(e) {
                                    e.stopPropagation();
                                    const $option = $(this);
                                    $container.data("selected", $option.data("value"));
                                    $container.attr("data-selected", $option.data("value"));
                                    $selectedLabel.text($option.text().trim());
                                    $hiddenInput.val($option.data("value")).trigger("change");
                                    $dropdown.addClass("hidden");
                                    console.log(`Option selected for #${componentId}:`, $option
                                        .data("value"));
                                });

                            $searchInput.off("input").on("input", function() {
                                const searchTerm = $(this).val().toLowerCase();
                                $optionsContainer.empty();
                                const filteredOptions = options.filter((opt) =>
                                    opt.label.toLowerCase().includes(searchTerm)
                                );
                                $.each(filteredOptions, function(i, option) {
                                    const $option = $(`
                                        <div class="js-option cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600" data-value="${option.value}">
                                            ${option.label}
                                        </div>
                                    `);
                                    $optionsContainer.append($option);
                                });
                            });

                            $container.find(".js-toggle-dropdown").off("click").on("click",
                                function(e) {
                                    e.stopPropagation();
                                    $dropdown.toggleClass("hidden");
                                    if (!$dropdown.hasClass("hidden")) {
                                        $searchInput.focus();
                                    }
                                    console.log(`Toggled dropdown for #${componentId}:`, !
                                        $dropdown.hasClass("hidden"));
                                });

                            $(document).off("click.dropdown-" + componentId).on("click.dropdown-" +
                                componentId,
                                function(e) {
                                    if (!$container.is(e.target) && $container.has(e.target)
                                        .length === 0) {
                                        $dropdown.addClass("hidden");
                                    }
                                });

                            console.log(`Reinitialized dropdown: #${componentId}`);
                        }
                    });

                    // Update remove button state
                    const removeButton = $row.find(".remove-budget-btn");
                    if (budgetTableBody.find(".budget-entry-row").length === 1) {
                        removeButton.prop("disabled", true).addClass("opacity-50 cursor-not-allowed");
                    } else {
                        removeButton.prop("disabled", false).removeClass("opacity-50 cursor-not-allowed");
                    }
                });
            }

            addBudgetButton.on("click", function() {
                console.log("Add budget row clicked");
                const lastRow = budgetTableBody.find(".budget-entry-row").last();
                const newRow = lastRow.clone(true, true);
                const index = budgetTableBody.find(".budget-entry-row").length;

                // Reset inputs and dropdown
                newRow.find("input:not(.js-hidden-input)").val("");
                newRow.find(".js-hidden-input").val("");
                newRow.find(".js-selected-label").text(newRow.find(".js-single-select").data(
                    "placeholder") || "Select an option");
                newRow.find(".js-single-select").attr("data-selected", "").data("selected", "");
                newRow.find(".text-red-600, .border-red-500").removeClass("text-red-600 border-red-500");

                // Update names and IDs for select
                newRow.find(".js-single-select").each(function() {
                    const $container = $(this);
                    const currentName = $container.data("name");
                    if (currentName && currentName.includes("fiscal_year_id")) {
                        const newName = currentName.replace(/budgets\[\d+\]/, `budgets[${index}]`);
                        const newId = `fiscal_year_id_${index}`;
                        $container.attr("id", newId).data("name", newName).attr("data-name",
                            newName);
                        $container.find(".js-hidden-input").attr("name", newName);
                        console.log(`New row select: #${newId}, name: ${newName}`);
                    } else {
                        console.warn(`Select container missing valid data-name:`, $container[0]
                            .outerHTML);
                    }
                });

                // Update names and IDs for other inputs
                newRow.find("input:not(.js-hidden-input)").each(function() {
                    const $input = $(this);
                    const currentName = $input.attr("name");
                    const currentId = $input.attr("id");

                    if (currentName) {
                        const newName = currentName.replace(/budgets\[\d+\]/, `budgets[${index}]`);
                        $input.attr("name", newName);
                        console.log(`New row input name: ${newName}`);
                    } else {
                        console.warn(`New row input missing name attribute:`, $input[0].outerHTML);
                    }

                    if (currentId) {
                        const newId = currentId.replace(
                            /^(total_budget|internal_budget|foreign_loan_budget|foreign_subsidy_budget)_\d+/,
                            `$1_${index}`);
                        $input.attr("id", newId);
                        console.log(`New row input ID: ${newId}`);
                    } else {
                        console.warn(`New row input missing ID attribute:`, $input[0].outerHTML);
                    }
                });

                budgetTableBody.append(newRow);
                updateRowIndices();
                console.log(`Appended new budget row with index: ${index}`);
            });

            budgetTableBody.on("click", ".remove-budget-btn", function() {
                console.log("Remove budget row clicked");
                if (budgetTableBody.find(".budget-entry-row").length > 1) {
                    $(this).closest(".budget-entry-row").remove();
                    updateRowIndices();
                    console.log("Removed budget row, updated indices");
                }
            });

            // Initial validation and logging
            console.log("Budget table rows:", budgetTableBody.find(".budget-entry-row").length);
            budgetTableBody.find(".budget-entry-row").each(function(index) {
                console.log(`Row ${index} inputs:`, $(this).find("input:not(.js-hidden-input)").map((i,
                    el) => ({
                        name: $(el).attr("name"),
                        id: $(el).attr("id")
                    })).get());
            });

            updateRowIndices();
        });
    </script>
</x-layouts.app>
