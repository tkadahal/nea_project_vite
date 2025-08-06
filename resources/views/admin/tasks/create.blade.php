<x-layouts.app>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
            {{ trans('global.task.title') }}
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">
            {{ trans('global.create') }} {{ trans('global.task.title') }}
        </p>
    </div>

    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden p-6">
        <form class="w-full" action="{{ route('admin.task.store') }}" method="POST">
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
                            {{ trans('global.task.title_singular') }} {{ trans('global.information') }}
                        </h3>
                        <div class="grid grid-cols-1 gap-6">
                            <div class="col-span-full">
                                <x-forms.select label="{{ trans('global.task.fields.directorate_id') }}"
                                    name="directorate_id" id="directorate_id" :options="collect($directorates)
                                        ->map(fn($label, $value) => ['value' => (string) $value, 'label' => $label])
                                        ->values()
                                        ->all()" :selected="old('directorate_id', '')"
                                    placeholder="{{ trans('global.pleaseSelect') }}" :error="$errors->first('directorate_id')"
                                    class="js-single-select" />
                            </div>

                            <div class="col-span-full">
                                <x-forms.multi-select label="{{ trans('global.task.fields.project_id') }}"
                                    name="projects[]" id="projects" :options="[]" :selected="old('projects', [])"
                                    placeholder="{{ trans('global.pleaseSelect') }}" :error="$errors->first('projects')"
                                    class="js-multi-select" />
                            </div>

                            <div class="col-span-full">
                                <x-forms.multi-select label="{{ trans('global.task.fields.user_id') }}" name="users[]"
                                    id="users" :options="[]" :selected="old('users', [])"
                                    placeholder="{{ trans('global.pleaseSelect') }}" :error="$errors->first('users')"
                                    class="js-multi-select" />
                            </div>

                            <div class="col-span-full">
                                <x-forms.input label="{{ trans('global.task.fields.title') }}" name="title"
                                    type="text" :value="old('title', '')" placeholder="Enter task title"
                                    :error="$errors->first('title')" />
                            </div>

                            <div class="col-span-full">
                                <x-forms.text-area label="{{ trans('global.task.fields.description') }}"
                                    name="description" :value="old('description', '')" placeholder="Enter task description"
                                    :error="$errors->first('description')" rows="5" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="p-6 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                        <h3
                            class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-600">
                            {{ trans('global.task.headers.date_progress') }}
                        </h3>
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <x-forms.date-input label="{{ trans('global.task.fields.start_date') }}"
                                    name="start_date" :value="old('start_date', '')" :error="$errors->first('start_date')" />
                            </div>
                            <div>
                                <x-forms.date-input label="{{ trans('global.task.fields.due_date') }}" name="due_date"
                                    :value="old('due_date', '')" :error="$errors->first('due_date')" />
                            </div>
                            <div>
                                <x-forms.date-input label="{{ trans('global.task.fields.completion_date') }}"
                                    name="completion_date" :value="old('completion_date', '')" :error="$errors->first('completion_date')" />
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                        <h3
                            class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-600">
                            {{ trans('global.task.headers.status_priority') }}
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-forms.select label="{{ trans('global.task.fields.status_id') }}" name="status_id"
                                    id="status_id" :options="collect($statuses)
                                        ->map(fn($label, $value) => ['value' => (string) $value, 'label' => $label])
                                        ->values()
                                        ->all()" :selected="old('status_id', '')"
                                    placeholder="{{ trans('global.pleaseSelect') }}" :error="$errors->first('status_id')"
                                    class="js-single-select" />
                            </div>
                            <div>
                                <x-forms.select label="{{ trans('global.task.fields.priority_id') }}"
                                    name="priority_id" id="priority_id" :options="collect($priorities)
                                        ->map(fn($label, $value) => ['value' => (string) $value, 'label' => $label])
                                        ->values()
                                        ->all()" :selected="old('priority_id', '')"
                                    placeholder="{{ trans('global.pleaseSelect') }}" :error="$errors->first('priority_id')"
                                    class="js-single-select" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <x-buttons.primary>
                    {{ trans('global.save') }}
                </x-buttons.primary>
            </div>
        </form>
    </div>

    @push('scripts')
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
                    $("#error-message").removeClass("hidden").find("#error-text").text(
                        "Failed to initialize form due to missing jQuery or DOM.");
                }
            }

            waitForJQuery(function() {
                const $ = jQuery;

                // Debounce function
                function debounce(func, wait) {
                    let timeout;
                    return function executedFunction(...args) {
                        const later = () => {
                            clearTimeout(timeout);
                            func(...args);
                        };
                        clearTimeout(timeout);
                        timeout = setTimeout(later, wait);
                    };
                }

                // Initialize single-select dropdowns only (avoid conflicts with multi-select)
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
                        $optionsContainer.empty();
                        $container.find(".js-no-options").toggleClass("hidden", currentOptions.length > 0);
                        if (!currentOptions.length) {
                            $selectedLabel.text($container.data("placeholder") || "Select an option");
                            $hiddenInput.val("");
                            currentSelectedValue = "";
                            $container.data("selected", currentSelectedValue);
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
                            if (currentSelectedValue === String(option.value)) {
                                $option.addClass("bg-blue-100 dark:bg-blue-900");
                            }
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
                        console.log(`Updating options for #${componentId}:`, data.options);
                        currentOptions = data?.options || [];
                        currentSelectedValue = data?.selected && currentOptions.some((opt) => String(opt
                                .value) === String(data.selected)) ?
                            String(data.selected) :
                            "";
                        $container.data("selected", currentSelectedValue);
                        $container.attr("data-selected", currentSelectedValue);
                        renderOptions();
                        updateHiddenInput();
                    });

                    $optionsContainer.off("click", ".js-option").on("click", ".js-option", function(e) {
                        e.stopPropagation();
                        const $option = $(this);
                        const value = String($option.data("value"));
                        currentSelectedValue = value;
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
                                console.log(`Dropdown closed for #${componentId}`);
                            }
                        });

                    const $clearButton = $container.find(".js-clear-button");
                    if ($clearButton.length) {
                        $clearButton.off("click").on("click", function(e) {
                            e.stopPropagation();
                            currentSelectedValue = "";
                            $container.data("selected", currentSelectedValue);
                            $container.attr("data-selected", "");
                            $selectedLabel.text($container.data("placeholder") || "Select an option");
                            $dropdown.addClass("hidden");
                            updateHiddenInput();
                            renderOptions();
                        });
                    }

                    renderOptions();
                });

                // AJAX handlers
                const directorateContainer = $('.js-single-select[data-name="directorate_id"]');
                const directorateInput = directorateContainer.find(".js-hidden-input");
                const projectsContainer = $('.js-multi-select[data-name="projects"]');
                const usersContainer = $('.js-multi-select[data-name="users"]');

                function updateSelectOptions(container, options, selected = "") {
                    const containerId = container.attr("id") || "undefined";
                    console.log(`Updating select options for #${containerId}:`, options, "Selected:", selected);
                    container.data("options", options);
                    container.data("selected", selected);
                    container.attr("data-selected", JSON.stringify(selected));
                    container.trigger("options-updated", {
                        options,
                        selected
                    });
                }

                // Load projects when directorate changes
                directorateInput.on("change", function() {
                    const directorateId = $(this).val();
                    console.log("Directorate changed:", directorateId);

                    // Reset projects and users if no valid directorate_id
                    if (!directorateId || isNaN(directorateId) || directorateId <= 0) {
                        console.log("No valid directorate_id, resetting projects and users");
                        updateSelectOptions(projectsContainer, [], []);
                        updateSelectOptions(usersContainer, [], []);
                        return;
                    }

                    const $optionsContainer = projectsContainer.find(".js-options-container");
                    $optionsContainer.empty().append(
                        '<div class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">Loading...</div>');

                    const baseUrl = "{{ url('/admin/tasks/projects') }}";
                    const projectsUrl = `${baseUrl}/${encodeURIComponent(directorateId)}`;
                    console.log("Projects AJAX URL:", projectsUrl);

                    $.ajax({
                        url: projectsUrl,
                        method: "GET",
                        dataType: "json",
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                            "X-Requested-With": "XMLHttpRequest",
                        },
                        success: function(data) {
                            console.log("Projects AJAX success:", data);
                            const formattedData = Array.isArray(data) ?
                                data.map((project) => ({
                                    value: String(project.value),
                                    label: String(project.label),
                                })).filter((opt) => opt.value && opt.label) : [];
                            const validOldProjects = @json(old('projects', []))
                                .filter((projectId) => formattedData.some((opt) => String(opt
                                    .value) === String(projectId)));
                            updateSelectOptions(projectsContainer, formattedData, validOldProjects);
                            updateSelectOptions(usersContainer, [],
                                []); // Reset users when projects change
                            if (formattedData.length === 0) {
                                $("#error-message").removeClass("hidden").find("#error-text").text(
                                    "No projects available for the selected directorate."
                                );
                            }
                        },
                        error: function(xhr) {
                            console.error("Projects AJAX error:", xhr.status, xhr.statusText, xhr
                                .responseJSON);
                            updateSelectOptions(projectsContainer, [], []);
                            updateSelectOptions(usersContainer, [], []);
                            $("#error-message").removeClass("hidden").find("#error-text").text(
                                "Failed to load projects: " + (xhr.responseJSON?.message ||
                                    "Unknown error")
                            );
                        }
                    });
                });

                // Load users when projects change
                const debouncedFetchUsers = debounce(function(selectedProjects) {
                    console.log("Fetching users for projects:", selectedProjects);

                    if (!selectedProjects.length) {
                        console.log("No projects selected, resetting users");
                        updateSelectOptions(usersContainer, [], []);
                        return;
                    }

                    const $optionsContainer = usersContainer.find(".js-options-container");
                    $optionsContainer.empty().append(
                        '<div class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">Loading...</div>');

                    $.ajax({
                        url: "{{ route('admin.tasks.users_by_projects') }}",
                        method: "GET",
                        data: {
                            project_ids: selectedProjects
                        },
                        dataType: "json",
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                            "X-Requested-With": "XMLHttpRequest",
                        },
                        success: function(data) {
                            console.log("Users AJAX success:", data);
                            const formattedData = Array.isArray(data) ?
                                data.map((user) => ({
                                    value: String(user.value),
                                    label: String(user.label),
                                })).filter((opt) => opt.value && opt.label) : [];
                            const validOldUsers = @json(old('users', []))
                                .filter((userId) => formattedData.some((opt) => String(opt
                                    .value) === String(userId)));
                            updateSelectOptions(usersContainer, formattedData, validOldUsers);
                            if (formattedData.length === 0) {
                                $("#error-message").removeClass("hidden").find("#error-text").text(
                                    "No users available for the selected projects."
                                );
                            }
                        },
                        error: function(xhr) {
                            console.error("Users AJAX error:", xhr.status, xhr.statusText, xhr
                                .responseJSON);
                            updateSelectOptions(usersContainer, [], []);
                            $("#error-message").removeClass("hidden").find("#error-text").text(
                                "Failed to load users: " + (xhr.responseJSON?.message ||
                                    "Unknown error")
                            );
                        }
                    });
                }, 300);

                projectsContainer.on("change", function() {
                    const selectedProjects = $(this).data("selected") || [];
                    console.log("Projects selection changed:", selectedProjects);
                    debouncedFetchUsers(selectedProjects);
                });

                // Close error message
                $("#close-error").on("click", function() {
                    $("#error-message").addClass("hidden").find("#error-text").text("");
                });

                // Trigger initial change for pre-selected directorate
                const initialDirectorateId = directorateInput.val();
                if (initialDirectorateId && !isNaN(initialDirectorateId) && initialDirectorateId > 0) {
                    console.log("Initial directorate_id:", initialDirectorateId);
                    directorateInput.trigger("change");
                } else {
                    console.log("No valid initial directorate_id, skipping trigger");
                }
            });
        </script>
    @endpush
</x-layouts.app>
