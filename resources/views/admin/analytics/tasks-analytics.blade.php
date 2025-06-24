<x-layouts.app>
    {{-- Page Title --}}
    <div class="mb-6 px-4 sm:px-6 lg:px-8">
        <h1 class="text-lg sm:text-xl lg:text-2xl xl:text-3xl font-bold text-gray-800 dark:text-gray-200">
            {{ __('Task Analytics') }}
        </h1>
        <p class="text-sm sm:text-base lg:text-lg text-gray-600 dark:text-gray-400 mt-1">
            {{ __('Analyze task statuses, progress, and distribution') }}
        </p>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col gap-4 mb-6 px-4 sm:px-6 lg:px-8">
        <div class="w-full" style="z-index: 1001;">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @if (auth()->user()->hasRole(\App\Models\Role::SUPERADMIN))
                    <div class="w-full">
                        <x-forms.select label="Directorate" name="directorate_id" :options="collect($directorates)
                            ->map(
                                fn($directorate) => [
                                    'value' => (string) $directorate->id,
                                    'label' => $directorate->title,
                                ],
                            )
                            ->values()
                            ->all()" :selected="request()->input('directorate_id')
                            ? (string) request()->input('directorate_id')
                            : null"
                            placeholder="Select a Directorate..." />
                    </div>
                @endif
                <div class="w-full">
                    <x-forms.select label="Project" name="project_id" :options="collect($projects)
                        ->map(fn($project) => ['value' => (string) $project->id, 'label' => $project->title])
                        ->values()
                        ->all()" :selected="request()->input('project_id') ? (string) request()->input('project_id') : null"
                        placeholder="Select a Project..." />
                </div>
                <div class="w-full">
                    <x-forms.select label="Status" name="status_id" :options="collect($statuses)
                        ->map(fn($status) => ['value' => (string) $status->id, 'label' => $status->title])
                        ->values()
                        ->all()" :selected="request()->input('status_id') ? (string) request()->input('status_id') : null"
                        placeholder="Select a Status..." />
                </div>
                <div class="w-full">
                    <x-forms.select label="Priority" name="priority_id" :options="collect($priorities)
                        ->map(fn($priority) => ['value' => (string) $priority->id, 'label' => $priority->title])
                        ->values()
                        ->all()" :selected="request()->input('priority_id') ? (string) request()->input('priority_id') : null"
                        placeholder="Select a Priority..." />
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
            <h3 class="text-sm sm:text-base lg:text-lg font-semibold text-gray-800 dark:text-gray-200">Total Tasks</h3>
            <p class="text-lg sm:text-xl lg:text-2xl text-gray-600 dark:text-gray-400">{{ $summary['total_tasks'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
            <h3 class="text-sm sm:text-base lg:text-lg font-semibold text-gray-800 dark:text-gray-200">Completed Tasks
            </h3>
            <p class="text-lg sm:text-xl lg:text-2xl text-gray-600 dark:text-gray-400">{{ $summary['completed_tasks'] }}
            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
            <h3 class="text-sm sm:text-base lg:text-lg font-semibold text-gray-800 dark:text-gray-200">Overdue Tasks
            </h3>
            <p class="text-lg sm:text-xl lg:text-2xl text-gray-600 dark:text-gray-400">{{ $summary['overdue_tasks'] }}
            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
            <h3 class="text-sm sm:text-base lg:text-lg font-semibold text-gray-800 dark:text-gray-200">Avg. Progress
            </h3>
            <p class="text-lg sm:text-xl lg:text-2xl text-gray-600 dark:text-gray-400">
                {{ $summary['average_progress'] }}%</p>
        </div>
    </div>

    {{-- Charts and Task Table Layout --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6 px-4 sm:px-6 lg:px-8">
        <!-- Charts (col-md-4) -->
        <div class="md:col-span-4 lg:col-span-1">
            <div class="space-y-6">
                <div class="bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-lg shadow">
                    <h3 class="text-sm sm:text-base lg:text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Task
                        Status Distribution</h3>
                    <div class="relative w-full max-w-[160px] sm:max-w-[200px] aspect-square mx-auto">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="text-center">
                                <p class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white"
                                    id="statusPercentage">
                                    {{ round(array_sum($charts['status']['data']) ? ($charts['status']['data'][0] / array_sum($charts['status']['data'])) * 100 : 0) }}%
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Main Status</p>
                            </div>
                        </div>
                        <canvas id="statusChart" class="w-full h-full"></canvas>
                    </div>
                    <div class="flex justify-around text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-2">
                        <div class="text-center">
                            <div class="w-2 h-2 sm:w-3 sm:h-3 bg-[#10b981] rounded-full inline-block mr-1 sm:mr-2">
                            </div>
                            <span>Not Started</span>
                        </div>
                        <div class="text-center">
                            <div class="w-2 h-2 sm:w-3 sm:h-3 bg-[#8b5cf6] rounded-full inline-block mr-1 sm:mr-2">
                            </div>
                            <span>In Progress</span>
                        </div>
                        <div class="text-center">
                            <div class="w-2 h-2 sm:w-3 sm:h-3 bg-[#ef4444] rounded-full inline-block mr-1 sm:mr-2">
                            </div>
                            <span>Completed</span>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-lg shadow">
                    <h3 class="text-sm sm:text-base lg:text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Task
                        Priority Distribution</h3>
                    <div class="relative w-full max-w-[160px] sm:max-w-[200px] aspect-square mx-auto">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="text-center">
                                <p class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white"
                                    id="priorityPercentage">
                                    {{ round(array_sum($charts['priority']['data']) ? ($charts['priority']['data'][0] / array_sum($charts['priority']['data'])) * 100 : 0) }}%
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Main Priority</p>
                            </div>
                        </div>
                        <canvas id="priorityChart" class="w-full h-full"></canvas>
                    </div>
                    <div class="flex justify-around text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-2">
                        <div class="text-center">
                            <div class="w-2 h-2 sm:w-3 sm:h-3 bg-[#10b981] rounded-full inline-block mr-1 sm:mr-2">
                            </div>
                            <span>Urgent</span>
                        </div>
                        <div class="text-center">
                            <div class="w-2 h-2 sm:w-3 sm:h-3 bg-[#8b5cf6] rounded-full inline-block mr-1 sm:mr-2">
                            </div>
                            <span>High</span>
                        </div>
                        <div class="text-center">
                            <div class="w-2 h-2 sm:w-3 sm:h-3 bg-[#ef4444] rounded-full inline-block mr-1 sm:mr-2">
                            </div>
                            <span>Medium</span>
                        </div>
                        <div class="text-center">
                            <div class="w-2 h-2 sm:w-3 sm:h-3 bg-[#10b981] rounded-full inline-block mr-1 sm:mr-2">
                            </div>
                            <span>Low</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Task Table (col-md-8) -->
        <div class="md:col-span-4 lg:col-span-3">
            <div class="bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-lg shadow mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm sm:text-base lg:text-lg font-semibold text-gray-800 dark:text-gray-200">Task
                        Details</h3>
                    <a href="{{ route('admin.tasks.analytics.export') }}"
                        class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-700 text-sm sm:text-base">
                        Export to CSV
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th
                                    class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Title
                                </th>
                                <th
                                    class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">
                                    Project
                                </th>
                                <th
                                    class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th
                                    class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">
                                    Priority
                                </th>
                                <th
                                    class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">
                                    Progress
                                </th>
                                <th
                                    class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">
                                    Due Date
                                </th>
                                <th
                                    class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden xl:table-cell">
                                    Assigned Users
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($tasks as $task)
                                <tr>
                                    <td class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                                        {{ $task->title }}</td>
                                    <td
                                        class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-gray-600 dark:text-gray-300 hidden sm:table-cell">
                                        @foreach ($task->projects as $project)
                                            {{ $project->title }}<br>
                                        @endforeach
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                                        {{ $task->status->title }}</td>
                                    <td
                                        class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-gray-600 dark:text-gray-300 hidden md:table-cell">
                                        {{ $task->priority->title }}
                                    </td>
                                    <td
                                        class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-gray-600 dark:text-gray-300 hidden md:table-cell">
                                        {{ $task->progress }}%
                                    </td>
                                    <td
                                        class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-gray-600 dark:text-gray-300 hidden lg:table-cell">
                                        {{ $task->due_date->format('Y-m-d') }}
                                    </td>
                                    <td
                                        class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-gray-600 dark:text-gray-300 hidden xl:table-cell">
                                        @foreach ($task->users as $user)
                                            <span
                                                class="inline-block bg-gray-200 dark:bg-gray-600 rounded-full px-2 py-1 text-xs mr-1">
                                                {{ $user->initials() }}
                                            </span>
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $tasks->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Status Chart (Donut)
                const statusCtx = document.getElementById('statusChart').getContext('2d');
                const statusData = {
                    datasets: [{
                        data: @json($charts['status']['data']),
                        backgroundColor: ['#10b981', '#8b5cf6', '#ef4444'],
                        borderWidth: 0,
                    }],
                    labels: @json($charts['status']['labels'])
                };
                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: statusData,
                    options: {
                        cutout: '70%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                enabled: true
                            }
                        },
                        maintainAspectRatio: true,
                        responsive: true
                    }
                });

                // Priority Chart (Donut)
                const priorityCtx = document.getElementById('priorityChart').getContext('2d');
                const priorityData = {
                    datasets: [{
                        data: @json($charts['priority']['data']),
                        backgroundColor: ['#10b981', '#8b5cf6', '#ef4444'],
                        borderWidth: 0,
                    }],
                    labels: @json($charts['priority']['labels'])
                };
                new Chart(priorityCtx, {
                    type: 'doughnut',
                    data: priorityData,
                    options: {
                        cutout: '70%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                enabled: true
                            }
                        },
                        maintainAspectRatio: true,
                        responsive: true
                    }
                });

                // Filter handling with AJAX
                document.addEventListener('change', function(event) {
                    const target = event.target;
                    if (target.matches(
                            'input[name="directorate_id"], input[name="project_id"], input[name="status_id"], input[name="priority_id"]'
                        )) {
                        const directorateId = document.querySelector('input[name="directorate_id"]')?.value ||
                            '';
                        const projectId = document.querySelector('input[name="project_id"]')?.value || '';
                        const statusId = document.querySelector('input[name="status_id"]')?.value || '';
                        const priorityId = document.querySelector('input[name="priority_id"]')?.value || '';

                        const url = new URL('{{ route('admin.tasks.analytics') }}', window.location.origin);
                        if (directorateId) url.searchParams.set('directorate_id', directorateId);
                        if (projectId) url.searchParams.set('project_id', projectId);
                        if (statusId) url.searchParams.set('status_id', statusId);
                        if (priorityId) url.searchParams.set('priority_id', priorityId);

                        window.history.pushState({}, '', url.toString());

                        fetch(url.toString(), {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                        .getAttribute('content')
                                }
                            })
                            .then(response => {
                                if (!response.ok) {
                                    return response.text().then(text => {
                                        throw new Error(
                                            `HTTP error! Status: ${response.status}, Body: ${text.substring(0, 200)}...`
                                        );
                                    });
                                }
                                return response.json();
                            })
                            .then(data => {
                                window.location.reload();
                            })
                            .catch(error => {
                                console.error('Filter request failed:', error);
                                alert('Failed to apply filters: ' + error.message);
                            });
                    }
                }, true);

                // jQuery fallback for select2 or custom select components
                if (window.jQuery) {
                    jQuery(document).on('change',
                        'input[name="directorate_id"], input[name="project_id"], input[name="status_id"], input[name="priority_id"]',
                        function() {
                            const directorateId = jQuery('input[name="directorate_id"]').val() || '';
                            const projectId = jQuery('input[name="project_id"]').val() || '';
                            const statusId = jQuery('input[name="status_id"]').val() || '';
                            const priorityId = jQuery('input[name="priority_id"]').val() || '';

                            const url = new URL('{{ route('admin.tasks.analytics') }}', window.location.origin);
                            if (directorateId) url.searchParams.set('directorate_id', directorateId);
                            if (projectId) url.searchParams.set('project_id', projectId);
                            if (statusId) url.searchParams.set('status_id', statusId);
                            if (priorityId) url.searchParams.set('priority_id', priorityId);

                            window.history.pushState({}, '', url.toString());

                            jQuery.ajax({
                                url: url.toString(),
                                method: 'GET',
                                headers: {
                                    'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                                },
                                dataType: 'json',
                                success: function(data) {
                                    window.location.reload();
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    console.error('jQuery filter request failed:', textStatus,
                                        errorThrown);
                                    alert('Failed to apply filters: ' + errorThrown);
                                }
                            });
                        });
                }

                // Initialize select values from URL parameters
                const urlParams = new URLSearchParams(window.location.search);
                const initialDirectorateId = urlParams.get('directorate_id') || '';
                const initialProjectId = urlParams.get('project_id') || '';
                const initialStatusId = urlParams.get('status_id') || '';
                const initialPriorityId = urlParams.get('priority_id') || '';

                if (initialDirectorateId || initialProjectId || initialStatusId || initialPriorityId) {
                    const directorateInput = document.querySelector('input[name="directorate_id"]');
                    const projectInput = document.querySelector('input[name="project_id"]');
                    const statusInput = document.querySelector('input[name="status_id"]');
                    const priorityInput = document.querySelector('input[name="priority_id"]');

                    if (directorateInput) directorateInput.value = initialDirectorateId;
                    if (projectInput) projectInput.value = initialProjectId;
                    if (statusInput) statusInput.value = initialStatusId;
                    if (priorityInput) priorityInput.value = initialPriorityId;

                    const $ = window.jQuery;
                    if ($) {
                        $('.js-single-select[data-name="directorate_id"]').trigger('options-updated', {
                            selected: initialDirectorateId
                        });
                        $('.js-single-select[data-name="project_id"]').trigger('options-updated', {
                            selected: initialProjectId
                        });
                        $('.js-single-select[data-name="status_id"]').trigger('options-updated', {
                            selected: initialStatusId
                        });
                        $('.js-single-select[data-name="priority_id"]').trigger('options-updated', {
                            selected: initialPriorityId
                        });
                    }
                }
            });
        </script>
    @endpush
</x-layouts.app>
