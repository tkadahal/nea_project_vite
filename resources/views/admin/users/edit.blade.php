<x-layouts.app>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('User') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Update User') }}</p>
        </div>

        <a href="{{ route('admin.user.index') }}"
            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300
                  focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2
                  dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-900">
            Back to Users
        </a>
    </div>

    <div class="flex flex-col md:flex-row gap-6">
        <div class="flex-1">
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6 p-6">
                <form class="max-w-3xl mx-auto" action="{{ route('admin.user.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Error messages unchanged -->
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 text-red-800 border border-red-300 rounded">
                            <ul class="list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div id="error-message"
                        class="mb-4 hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative">
                        <span id="error-text"></span>
                        <button type="button" id="close-error" class="absolute top-0 right-0 px-4 py-3">
                            <svg class="fill-current h-6 w-6 text-red-500" role="button"
                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path
                                    d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 010 1.698z" />
                            </svg>
                        </button>
                    </div>

                    <!-- User Information Section -->
                    <div
                        class="mb-8 p-6 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                        <h3
                            class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-600">
                            {{ __('User Information') }}
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-forms.input label="Employee Id" name="employee_id" type="number" :value="old('employee_id', $user->employee_id)"
                                    :error="$errors->first('employee_id')" />
                            </div>
                            <div>
                                <x-forms.input label="Name" name="name" type="text" :value="old('name', $user->name)"
                                    :error="$errors->first('name')" />
                            </div>
                            <div>
                                <x-forms.input label="Mobile Number" name="mobile_number" type="number"
                                    :value="old('mobile_number', $user->mobile_number)" :error="$errors->first('mobile_number')" />
                            </div>
                            <div>
                                <x-forms.input label="Email" name="email" type="email" :value="old('email', $user->email)"
                                    :error="$errors->first('email')" />
                            </div>
                            <div>
                                <x-forms.input label="Password" name="password" type="password" :error="$errors->first('password')" />
                            </div>
                            <div>
                                <x-forms.multi-select label="Role" name="roles[]" id="roles" :options="collect($roles)
                                    ->map(fn($label, $value) => ['value' => (string) $value, 'label' => $label])
                                    ->values()
                                    ->all()"
                                    :selected="old('roles', $user->roles->pluck('id')->toArray())" placeholder="Select role" :error="$errors->first('roles')" />
                            </div>
                        </div>
                    </div>

                    <!-- Assignment Section -->
                    <div
                        class="mb-8 p-6 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                        <h3
                            class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-600">
                            {{ __('Assignments') }}
                        </h3>
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <x-forms.select label="Directorate" name="directorate_id" id="directorate_id"
                                    :options="collect($directorates)
                                        ->map(fn($label, $value) => ['value' => (string) $value, 'label' => $label])
                                        ->values()
                                        ->all()" :selected="old('directorate_id', $user->directorate_id)" placeholder="Select directorate"
                                    :error="$errors->first('directorate_id')" />
                            </div>
                            <div>
                                <x-forms.multi-select label="Projects" name="projects[]" id="projects"
                                    :options="collect($projects)
                                        ->map(fn($label, $value) => ['value' => (string) $value, 'label' => $label])
                                        ->values()
                                        ->all()" :selected="old(
                                        'projects',
                                        $user->projects->pluck('id')->map(fn($id) => (string) $id)->toArray(),
                                    )" placeholder="Select projects"
                                    :error="$errors->first('projects')" />
                            </div>
                        </div>
                    </div>

                    <div class="flex space-x-2">
                        <x-buttons.primary>{{ __('Update') }}</x-buttons.primary>
                        <a href="{{ route('admin.user.index') }}"
                            class="px-4 py-2 text-sm text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-500">
                            {{ __('Cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function waitForjQuery() {
                if (window.jQuery) {
                    jQuery(document).ready(function($) {
                        // Store projects data for debugging
                        window.debugProjectsData = [];

                        // Find the Projects multi-select by data-name
                        const $projectsContainer = $('.js-multi-select[data-name="projects"]');
                        console.log('Projects container found:', $projectsContainer.length, 'ID:',
                            $projectsContainer.attr('id'));

                        // Get initial selected projects from user
                        const userProjects = @json($user->projects->pluck('id')->map(fn($id) => (string) $id)->toArray());
                        console.log('Initial user projects:', userProjects);

                        // Store initial selections for reset
                        let currentSelected = userProjects;

                        // AJAX handler for projects
                        $('input[name="directorate_id"].js-hidden-input').on('change', function() {
                            const directorateId = $(this).val();
                            console.log('Directorate changed:', directorateId);

                            if (directorateId) {
                                $.ajax({
                                    url: '{{ route('admin.users.projects', ':directorateId') }}'
                                        .replace(':directorateId', encodeURIComponent(
                                            directorateId)),
                                    type: 'GET',
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    },
                                    success: function(data) {
                                        console.log('AJAX success, raw response:', data);
                                        const formattedData = Array.isArray(data) ? data.filter(
                                            opt => opt.value !== undefined && opt.label !==
                                            undefined
                                        ) : [];
                                        console.log('Formatted projects:', formattedData);
                                        window.debugProjectsData =
                                        formattedData; // Store for inspection
                                        // Use old('projects') if available, otherwise currentSelected
                                        const validOldProjects = @json(old('projects', []))
                                            .length > 0 ?
                                            @json(old('projects', [])) :
                                            currentSelected;
                                        const validProjects = validOldProjects.filter(
                                            projectId => formattedData.some(opt => String(
                                                opt.value) === String(projectId))
                                        );
                                        console.log('Valid projects:', validProjects);
                                        currentSelected =
                                        validProjects; // Update current selections
                                        $projectsContainer
                                            .data('options', formattedData)
                                            .data('selected', validProjects);
                                        console.log('Data set on container:', $projectsContainer
                                            .data('options'), 'Selected:',
                                            $projectsContainer.data('selected'));
                                        $projectsContainer.trigger('options-updated');
                                        if (formattedData.length === 0) {
                                            $('#error-message').removeClass('hidden');
                                            $('#error-text').text(
                                                'No projects available for the selected directorate.'
                                                );
                                        } else {
                                            $('#error-message').addClass('hidden');
                                            $('#error-text').text('');
                                        }
                                    },
                                    error: function(xhr) {
                                        console.error('AJAX error:', xhr.status, xhr.statusText,
                                            xhr.responseJSON);
                                        $('#error-message').removeClass('hidden');
                                        $('#error-text').text('Failed to load projects: ' + (xhr
                                            .responseJSON?.message || 'Unknown error'));
                                        $projectsContainer
                                            .data('options', [])
                                            .data('selected', []);
                                        currentSelected = [];
                                        $projectsContainer.trigger('options-updated');
                                    }
                                });
                            } else {
                                console.log('Directorate cleared, resetting projects');
                                $projectsContainer
                                    .data('options', [])
                                    .data('selected', []);
                                currentSelected = [];
                                $projectsContainer.trigger('options-updated');
                                $('#error-message').addClass('hidden');
                                $('#error-text').text('');
                            }
                        });

                        // Close error message
                        $('#close-error').on('click', function() {
                            $('#error-message').addClass('hidden');
                            $('#error-text').text('');
                        });

                        // Trigger initial change for pre-selected directorate
                        const preSelected = $('input[name="directorate_id"].js-hidden-input').val();
                        if (preSelected) {
                            console.log('Initial directorate:', preSelected);
                            setTimeout(() => {
                                $('input[name="directorate_id"].js-hidden-input').trigger('change');
                            }, 200);
                        } else {
                            // Ensure initial selections are rendered if no directorate
                            console.log('No pre-selected directorate, rendering initial projects');
                            $projectsContainer.trigger('options-updated');
                        }
                    });
                } else {
                    setTimeout(waitForjQuery, 100);
                }
            })();
        </script>
    @endpush
</x-layouts.app>
