<x-layouts.app>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Edit Project') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Edit project details for') }} <span
                class="font-semibold">{{ $project->title }}</span></p>
    </div>

    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden p-6">
        <form class="w-full" action="{{ route('admin.project.update', $project) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')

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
                            {{ __('Project Information') }}
                        </h3>
                        <div class="grid grid-cols-1 gap-6">
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
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="p-6 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                        <h3
                            class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-600">
                            {{ __('Dates & Progress') }}
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-forms.date-input label="Start Date" name="start_date" :value="old(
                                    'start_date',
                                    $project->start_date
                                        ? \Carbon\Carbon::parse($project->start_date)->format('Y-m-d')
                                        : '',
                                )"
                                    :error="$errors->first('start_date')" />
                            </div>
                            <div>
                                <x-forms.date-input label="End Date" name="end_date" :value="old(
                                    'end_date',
                                    $project->end_date
                                        ? \Carbon\Carbon::parse($project->end_date)->format('Y-m-d')
                                        : '',
                                )"
                                    :error="$errors->first('end_date')" />
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                        <h3
                            class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-600">
                            {{ __('Status & Priority') }}
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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

                    <div class="p-6 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                        <h3
                            class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-600">
                            {{ __('Attachments') }}
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="col-span-full">
                                <div class="space-y-4">
                                    <div>
                                        <input type="file" name="files[]" multiple
                                            accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.zip"
                                            class="w-full p-2 border rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            aria-describedby="files-error" onchange="updateFileNameList(event)">
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            {{ __('Supported formats: PDF, DOC, XLS, PNG, JPG, ZIP. Max size: 10MB each.') }}
                                        </p>
                                        @error('files.*')
                                            <p class="text-red-500 text-sm mt-1" id="files-error">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div id="selected-files-preview" class="space-y-2">
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 hidden"
                                            id="selected-files-title">
                                            {{ __('Selected Files:') }}
                                        </p>
                                        <ul class="list-disc list-inside text-sm text-gray-700 dark:text-gray-300"
                                            id="file-list">
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            @if ($project->files->isNotEmpty())
                                <div class="col-span-full">
                                    <div
                                        class="flex items-center gap-2 mb-4 pb-2 border-b border-gray-200 dark:border-gray-600">
                                        <div class="text-indigo-600 dark:text-indigo-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <h5 class="text-xl font-semibold text-gray-800 dark:text-gray-100">
                                            {{ __('Uploaded Files') }}
                                        </h5>
                                    </div>
                                    <div class="space-y-2">
                                        <ul class="list-disc list-inside text-sm text-gray-700 dark:text-gray-300">
                                            @foreach ($project->files as $file)
                                                <li>
                                                    <a href="{{ route('admin.files.download', $file->id) }}"
                                                        class="text-blue-600 hover:underline dark:text-blue-400">
                                                        {{ $file->filename }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <x-buttons.primary>{{ __('Save') }}</x-buttons.primary>
            </div>
        </form>
    </div>

    <script>
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
                callback();
            }
        }

        waitForJQuery(function() {
            const $ = jQuery;

            window.updateFileNameList = function(event) {
                const files = event.target.files;
                const fileList = $("#file-list");
                const filesTitle = $("#selected-files-title");
                fileList.empty();
                if (files.length > 0) {
                    filesTitle.removeClass("hidden");
                    for (let i = 0; i < files.length; i++) {
                        fileList.append(`<li>${files[i].name}</li>`);
                    }
                } else {
                    filesTitle.addClass("hidden");
                }
            };

            $(".js-single-select").each(function() {
                const $container = $(this);
                const componentId = $container.attr("id");
                const dataName = $container.data("name");
                let currentOptions = $container.data("options") || [];
                let currentSelectedValue = String($container.data("selected") || "");
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
                }

                $container.off("options-updated").on("options-updated", function(event, data) {
                    currentOptions = data?.options || [];
                    currentSelectedValue = data?.selected && currentOptions.some((opt) => String(opt
                            .value) === String(data.selected)) ?
                        String(data.selected) :
                        "";
                    renderOptions();
                    updateHiddenInput();
                });

                $optionsContainer.off("click", ".js-option").on("click", ".js-option", function(e) {
                    e.stopPropagation();
                    const $option = $(this);
                    currentSelectedValue = String($option.data("value"));
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
                    $(".js-dropdown").not($dropdown).addClass("hidden");
                    $dropdown.toggleClass("hidden");
                    if (!$dropdown.hasClass("hidden")) {
                        $searchInput.val("");
                        renderOptions();
                        $searchInput.focus();
                    }
                });

                $(document).off("click.dropdown-" + componentId).on("click.dropdown-" + componentId,
                    function(e) {
                        if (!$container.is(e.target) && $container.has(e.target).length === 0) {
                            $dropdown.addClass("hidden");
                        }
                    });

                const $clearButton = $container.find(".js-clear-button");
                if ($clearButton.length) {
                    $clearButton.off("click").on("click", function(e) {
                        e.stopPropagation();
                        currentSelectedValue = "";
                        $container.data("selected", "");
                        $container.attr("data-selected", "");
                        $selectedLabel.text($container.data("placeholder") || "Select an option");
                        $dropdown.addClass("hidden");
                        updateHiddenInput();
                        renderOptions();
                    });
                }

                renderOptions();
            });

            const directorateContainer = $('.js-single-select[data-name="directorate_id"]');
            const directorateInput = directorateContainer.find(".js-hidden-input");
            const departmentSelectContainer = $('.js-single-select[data-name="department_id"]');
            const userSelectContainer = $('.js-single-select[data-name="project_manager"]');

            function updateSelectOptions(container, options, selected = "") {
                container.data("options", options);
                container.data("selected", selected);
                container.attr("data-selected", selected);
                container.trigger("options-updated", {
                    options,
                    selected
                });
            }

            function loadDepartments(directorateId, selectedDepartmentId = "") {
                if (!directorateId) {
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
                    success: function(data) {
                        const formattedData = Array.isArray(data) ?
                            data.map((dept) => ({
                                value: String(dept.value),
                                label: String(dept.label),
                            })).filter((opt) => opt.value && opt.label) :
                            [];
                        updateSelectOptions(departmentSelectContainer, formattedData,
                            selectedDepartmentId);
                    },
                    error: function(xhr) {
                        updateSelectOptions(departmentSelectContainer, [], "");
                        $optionsContainer.empty().append(
                            '<div class="px-4 py-2 text-sm text-red-500 dark:text-red-400">Failed to load departments</div>'
                            );
                        $("#error-message").removeClass("hidden").find("#error-text").text(
                            "Failed to load departments: " + (xhr.responseJSON?.message ||
                                "Unknown error")
                        );
                    }
                });
            }

            function loadUsers(directorateId, selectedUserId = "") {
                if (!directorateId) {
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
                    success: function(data) {
                        const formattedData = Array.isArray(data) ?
                            data.map((user) => ({
                                value: String(user.value),
                                label: String(user.label),
                            })).filter((opt) => opt.value && opt.label) :
                            [];
                        updateSelectOptions(userSelectContainer, formattedData, selectedUserId);
                    },
                    error: function(xhr) {
                        updateSelectOptions(userSelectContainer, [], "");
                        $optionsContainer.empty().append(
                            '<div class="px-4 py-2 text-sm text-red-500 dark:text-red-400">Failed to load users</div>'
                            );
                        $("#error-message").removeClass("hidden").find("#error-text").text(
                            "Failed to load users: " + (xhr.responseJSON?.message ||
                                "Unknown error")
                        );
                    }
                });
            }

            directorateInput.off("change").on("change", function() {
                const directorateId = $(this).val();
                loadDepartments(directorateId, "{{ old('department_id', $project->department_id) }}");
                loadUsers(directorateId, "{{ old('project_manager', $project->project_manager) }}");
            });

            if (directorateInput.val()) {
                loadDepartments(directorateInput.val(), "{{ old('department_id', $project->department_id) }}");
                loadUsers(directorateInput.val(), "{{ old('project_manager', $project->project_manager) }}");
            } else {
                updateSelectOptions(departmentSelectContainer, [], "");
                updateSelectOptions(userSelectContainer, [], "");
            }

            $("#close-error").off("click").on("click", function() {
                $("#error-message").addClass("hidden").find("#error-text").text("");
            });
        });
    </script>
</x-layouts.app>
