<x-layouts.app>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Task') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Create New Task') }}</p>
    </div>

    {{-- Removed outer flex containers, the form will now handle the grid layout --}}
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden p-6">
        <form class="w-full" action="{{ route('admin.task.store') }}" method="POST">
            @csrf

            @if ($errors->any())
                <div
                    class="mb-4 p-4 bg-red-100 text-red-800 border border-red-300 rounded dark:bg-red-900 dark:text-red-200 dark:border-red-700">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div id="error-message"
                class="mb-4 hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative dark:bg-red-900 dark:border-red-700 dark:text-red-200">
                <span id="error-text"></span>
                <button type="button" id="close-error" class="absolute top-0 right-0 px-4 py-3">
                    <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20">
                        <path
                            d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
                    </svg>
                </button>
            </div>

            {{-- New: Main Grid for Two Columns --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Left Column: Task Information --}}
                <div>
                    <div
                        class="p-6 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 h-full">
                        {{-- Added h-full to make it fill the height --}}
                        <h3
                            class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-600">
                            {{ __('Task Information') }}
                        </h3>

                        <div class="grid grid-cols-1 gap-6"> {{-- Changed to grid-cols-1 to make inner items stack --}}
                            <div class="col-span-full">
                                <x-forms.select label="Directorate" name="directorate_id" id="directorate_id"
                                    :options="collect($directorates)
                                        ->map(fn($label, $value) => ['value' => (string) $value, 'label' => $label])
                                        ->values()
                                        ->all()" :selected="old('directorate_id', '')" placeholder="Select directorate"
                                    :error="$errors->first('directorate_id')" class="js-single-select" />
                            </div>

                            <div class="col-span-full">
                                <x-forms.multi-select label="Projects" name="projects[]" id="projects"
                                    :options="[]" :selected="old('projects', [])" placeholder="Select projects" :error="$errors->first('projects')"
                                    class="js-multi-select" />
                            </div>

                            <div class="col-span-full">
                                <x-forms.multi-select label="Assignees" name="users[]" id="users" :options="[]"
                                    :selected="old('users', [])" placeholder="Select assignees" :error="$errors->first('users')"
                                    class="js-multi-select" />
                            </div>

                            <div class="col-span-full">
                                <x-forms.input label="Title" name="title" type="text" :value="old('title', '')"
                                    placeholder="Enter task title" :error="$errors->first('title')" />
                            </div>

                            <div class="col-span-full">
                                <x-forms.text-area label="Description" name="description" :value="old('description', '')"
                                    placeholder="Enter task description" :error="$errors->first('description')" rows="5" />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Dates and Status & Priority --}}
                <div class="space-y-6"> {{-- Use space-y to add vertical gap between stacked sections --}}
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
                                    :selected="old('status_id', '')" placeholder="Select status" :error="$errors->first('status_id')"
                                    class="js-single-select" />
                            </div>

                            <div>
                                <x-forms.select label="Priority" name="priority_id" id="priority_id" :options="collect($priorities)
                                    ->map(fn($label, $value) => ['value' => (string) $value, 'label' => $label])
                                    ->values()
                                    ->all()"
                                    :selected="old('priority_id', '')" placeholder="Select priority" :error="$errors->first('priority_id')"
                                    class="js-single-select" />
                            </div>
                        </div>
                    </div>
                    <div class="p-6 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                        <h3
                            class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-600">
                            {{ __('Dates') }}
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <x-forms.date-input label="Start Date" name="start_date" :value="old('start_date', '')"
                                    :error="$errors->first('start_date')" />
                            </div>

                            <div>
                                <x-forms.date-input label="Due Date" name="due_date" :value="old('due_date', '')"
                                    :error="$errors->first('due_date')" />
                            </div>

                            <div>
                                <x-forms.date-input label="Completion Date" name="completion_date" :value="old('completion_date', '')"
                                    :error="$errors->first('completion_date')" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <x-buttons.primary>{{ __('Save') }}</x-buttons.primary>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            (function waitForjQuery() {
                if (window.jQuery) {
                    jQuery(document).ready(function($) {
                        // Store projects and users data for debugging
                        window.debugProjectsData = [];
                        window.debugUsersData = [];

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

                        // Function to find containers with multiple selectors
                        function findContainers() {
                            let $projectsContainer = $(
                                '.js-multi-select[data-name="projects[]"], .js-multi-select[data-name="projects"], #projects'
                            ).filter(':visible');
                            let $usersContainer = $(
                                '.js-multi-select[data-name="users[]"], .js-multi-select[data-name="users"], #users'
                            ).filter(':visible');
                            return {
                                $projectsContainer,
                                $usersContainer
                            };
                        }

                        // Initial container check
                        let {
                            $projectsContainer,
                            $usersContainer
                        } = findContainers();
                        console.log('Initial - Projects container found:', $projectsContainer.length, 'ID:',
                            $projectsContainer.attr('id'), 'Data-name:', $projectsContainer.attr('data-name'));
                        console.log('Initial - Users container found:', $usersContainer.length, 'ID:',
                            $usersContainer.attr('id'), 'Data-name:', $usersContainer.attr('data-name'));

                        // Log DOM for debugging
                        setTimeout(() => {
                            console.log('Task Form HTML:', $('form[action="' +
                                    '{{ route('admin.task.store') }}' + '"]').prop('outerHTML')
                                ?.substring(0, 1000) + '...');
                            console.log('Projects DOM:', $projectsContainer[0]?.outerHTML || '<not found>');
                            console.log('Users DOM:', $('.js-multi-select[data-name="users"], #users')[0]
                                ?.outerHTML || '<not found>');
                            console.log('Projects inputs:', $projectsContainer.find('input').map(
                                function() {
                                    return $(this).attr('name') + ':' + $(this).val();
                                }).get());
                            console.log('Users inputs:', $usersContainer.find('input').map(function() {
                                return $(this).attr('name') + ':' + $(this).val();
                            }).get());
                            console.log('All multi-selects:', $('.js-multi-select').map(function() {
                                return $(this).attr('id') + ':' + $(this).attr('data-name');
                            }).get());
                        }, 1000);

                        // Retry container check
                        setTimeout(() => {
                            if ($projectsContainer.length === 0 || $usersContainer.length === 0) {
                                console.warn('Retrying container detection...');
                                ({
                                    $projectsContainer,
                                    $usersContainer
                                } = findContainers());
                                console.log('Retry - Projects:', $projectsContainer.length, 'ID:',
                                    $projectsContainer.attr('id'), 'Data-name:', $projectsContainer
                                    .attr('data-name'));
                                console.log('Retry - Users:', $usersContainer.length, 'ID:', $usersContainer
                                    .attr('id'), 'Data-name:', $usersContainer.attr('data-name'));
                            }
                        }, 2000);

                        // Mutation observer for projects container with debouncing
                        let lastSelectedProjects = [];
                        const debouncedFetchUsers = debounce((selectedProjects) => {
                            if (JSON.stringify(selectedProjects) !== JSON.stringify(lastSelectedProjects)) {
                                console.log('Debounced fetch users for projects:', selectedProjects);
                                lastSelectedProjects = selectedProjects;
                                fetchUsers(selectedProjects);
                            }
                        }, 300);

                        const observer = new MutationObserver((mutations) => {
                            mutations.forEach((mutation) => {
                                if (mutation.type === 'attributes' || mutation.type ===
                                    'childList') {
                                    const selectedProjects = $projectsContainer.data('selected') ||
                                        [];
                                    console.log('Mutation detected - Projects selected:',
                                        selectedProjects);
                                    debouncedFetchUsers(selectedProjects);
                                }
                            });
                        });
                        if ($projectsContainer[0]) {
                            observer.observe($projectsContainer[0], {
                                attributes: true,
                                childList: true,
                                subtree: true
                            });
                        }

                        // AJAX handler for projects
                        $('input[name="directorate_id"].js-hidden-input').on('change', function() {
                            const directorateId = $(this).val();
                            console.log('Directorate changed:', directorateId);

                            // Re-check containers
                            ({
                                $projectsContainer,
                                $usersContainer
                            } = findContainers());

                            if (directorateId) {
                                if ($projectsContainer.length === 0) {
                                    console.error('Projects multi-select container not found during AJAX.');
                                    $('#error-message').removeClass('hidden');
                                    $('#error-text').text(
                                        'Projects multi-select component failed to load.');
                                    return;
                                }

                                $.ajax({
                                    url: '{{ route('admin.tasks.projects', ':directorateId') }}'
                                        .replace(':directorateId', encodeURIComponent(
                                            directorateId)),
                                    type: 'GET',
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    },
                                    success: function(data) {
                                        console.log('Projects AJAX success, raw response:',
                                            data);
                                        const formattedData = Array.isArray(data) ? data.filter(
                                            opt => opt.value !== undefined && opt.label !==
                                            undefined
                                        ) : [];
                                        console.log('Formatted projects:', formattedData);
                                        window.debugProjectsData = formattedData;
                                        const validOldProjects = @json(old('projects', []))
                                            .filter(projectId => formattedData.some(opt =>
                                                String(opt.value) === String(projectId)));
                                        console.log('Valid old projects:', validOldProjects);
                                        $projectsContainer
                                            .data('options', formattedData)
                                            .data('selected', validOldProjects)
                                            .trigger('options-updated');
                                        // Reset users when directorate changes
                                        if ($usersContainer.length > 0) {
                                            $usersContainer
                                                .data('options', [])
                                                .data('selected', [])
                                                .trigger('options-updated');
                                        }
                                        if (formattedData.length === 0) {
                                            $('#error-message').removeClass('hidden');
                                            $('#error-text').text(
                                                'No projects available for the selected directorate.'
                                            );
                                        }
                                    },
                                    error: function(xhr) {
                                        console.error('Projects AJAX error:', xhr.status, xhr
                                            .statusText, xhr.responseJSON);
                                        $('#error-message').removeClass('hidden');
                                        $('#error-text').text('Failed to load projects: ' + (xhr
                                            .responseJSON?.message || 'Unknown error'));
                                        if ($projectsContainer.length > 0) {
                                            $projectsContainer
                                                .data('options', [])
                                                .data('selected', [])
                                                .trigger('options-updated');
                                        }
                                        if ($usersContainer.length > 0) {
                                            $usersContainer
                                                .data('options', [])
                                                .data('selected', [])
                                                .trigger('options-updated');
                                        }
                                    }
                                });
                            } else {
                                console.log('Directorate cleared, resetting projects and users');
                                if ($projectsContainer.length > 0) {
                                    $projectsContainer
                                        .data('options', [])
                                        .data('selected', [])
                                        .trigger('options-updated');
                                }
                                if ($usersContainer.length > 0) {
                                    $usersContainer
                                        .data('options', [])
                                        .data('selected', [])
                                        .trigger('options-updated');
                                }
                            }
                        });

                        // AJAX handler for users based on selected projects
                        function fetchUsers(selectedProjects) {
                            console.log('Fetching users for projects:', selectedProjects);
                            if ($usersContainer.length === 0) {
                                console.error('Users multi-select container not found.');
                                $('#error-message').removeClass('hidden');
                                $('#error-text').text('Users multi-select component failed to load.');
                                return;
                            }

                            // Store current selections
                            const currentSelectedUsers = $usersContainer.data('selected') || [];
                            console.log('Current selected users:', currentSelectedUsers);

                            if (selectedProjects.length > 0) {
                                $.ajax({
                                    url: '{{ route('admin.tasks.users_by_projects') }}',
                                    type: 'GET',
                                    data: {
                                        project_ids: selectedProjects
                                    },
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    },
                                    success: function(data) {
                                        console.log('Users AJAX success, raw response:', data);
                                        const formattedData = Array.isArray(data) ? data.filter(
                                            opt => opt.value !== undefined && opt.label !==
                                            undefined
                                        ) : [];
                                        console.log('Formatted users:', formattedData);
                                        window.debugUsersData = formattedData;
                                        // Preserve valid selections
                                        const validSelectedUsers = currentSelectedUsers.filter(
                                            userId => formattedData.some(opt => String(opt
                                                .value) === String(userId))
                                        );
                                        console.log('Valid selected users:', validSelectedUsers);
                                        $usersContainer
                                            .data('options', formattedData)
                                            .data('selected', validSelectedUsers)
                                            .trigger('options-updated');
                                        // Log hidden inputs
                                        console.log('Users hidden inputs after update:', $usersContainer
                                            .find('input[name="users[]"]').map(function() {
                                                return $(this).val();
                                            }).get());
                                        if (formattedData.length === 0) {
                                            $('#error-message').removeClass('hidden');
                                            $('#error-text').text(
                                                'No users available for the selected projects.');
                                        }
                                    },
                                    error: function(xhr) {
                                        console.error('Users AJAX error:', xhr.status, xhr.statusText,
                                            xhr.responseJSON);
                                        $('#error-message').removeClass('hidden');
                                        $('#error-text').text('Failed to load users: ' + (xhr
                                            .responseJSON?.message || 'Unknown error'));
                                        $usersContainer
                                            .data('options', [])
                                            .data('selected', [])
                                            .trigger('options-updated');
                                    }
                                });
                            } else {
                                console.log('Projects cleared, resetting users');
                                $usersContainer
                                    .data('options', [])
                                    .data('selected', [])
                                    .trigger('options-updated');
                            }
                        }

                        // Handle project selection changes
                        $projectsContainer.on('options-updated change', function() {
                            const selectedProjects = $projectsContainer.data('selected') || [];
                            console.log('Projects event triggered, selected:', selectedProjects);
                            debouncedFetchUsers(selectedProjects);
                        });

                        // Handle user selection changes
                        $usersContainer.on('change', function() {
                            const selectedUsers = $usersContainer.data('selected') || [];
                            console.log('Users selection changed:', selectedUsers);
                            console.log('Users hidden inputs:', $usersContainer.find(
                                'input[name="users[]"]').map(function() {
                                return $(this).val();
                            }).get());
                        });

                        // Close error message
                        $('#close-error').on('click', function() {
                            $('#error-message').addClass('hidden');
                            $('#error-text').text('');
                        });

                        // Trigger initial change for pre-selected directorate
                        const $preSelected = $('input[name="directorate_id"].js-hidden-input').first();
                        if ($preSelected.length > 0 && $preSelected.val()) {
                            console.log('Initial directorate:', $preSelected.val());
                            setTimeout(() => {
                                $preSelected.trigger('change');
                            }, 1000);
                        }
                    });
                } else {
                    setTimeout(waitForjQuery, 100);
                }
            })();
        </script>
    @endpush
</x-layouts.app>
