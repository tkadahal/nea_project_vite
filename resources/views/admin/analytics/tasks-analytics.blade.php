<x-layouts.app>
    <div class="mb-6 px-4 sm:px-6 lg:px-8">
        <h1 class="text-lg sm:text-xl lg:text-2xl xl:text-3xl font-bold text-gray-800 dark:text-gray-200">
            {{ trans('global.analytics.task.title') }}
        </h1>
        <p class="text-sm sm:text-base lg:text-lg text-gray-600 dark:text-gray-400 mt-1">
            {{ trans('global.analytics.task.headerInfo') }}
        </p>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col gap-4 mb-6 px-4 sm:px-6 lg:px-8">
        <div class="w-full" style="z-index: 1001;">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @if (auth()->user()->hasRole(\App\Models\Role::SUPERADMIN))
                    <div class="w-full">
                        <label for="directorate_id"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ trans('global.directorate.title') }}
                        </label>
                        <select id="directorate_id" name="directorate_id"
                            class="block w-full p-2 text-base border border-gray-300 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                            <option value="">{{ trans('global.pleaseSelect') }}</option>
                            @foreach ($directorates as $directorate)
                                <option value="{{ $directorate->id }}"
                                    {{ request()->input('directorate_id') == $directorate->id ? 'selected' : '' }}>
                                    {{ $directorate->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="w-full">
                    <label for="project_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ trans('global.project.title') }}
                    </label>
                    <select id="project_id" name="project_id"
                        class="block w-full p-2 text-base border border-gray-300 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                        <option value="">{{ trans('global.pleaseSelect') }}</option>
                        @foreach ($projects as $project)
                            <option value="{{ $project->id }}"
                                {{ request()->input('project_id') == $project->id ? 'selected' : '' }}>
                                {{ $project->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full">
                    <label for="status_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ trans('global.status.title') }}
                    </label>
                    <select id="status_id" name="status_id"
                        class="block w-full p-2 text-base border border-gray-300 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                        <option value="">{{ trans('global.pleaseSelect') }}</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->id }}"
                                {{ request()->input('status_id') == $status->id ? 'selected' : '' }}>
                                {{ $status->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full">
                    <label for="priority_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ trans('global.priority.title') }}
                    </label>
                    <select id="priority_id" name="priority_id"
                        class="block w-full p-2 text-base border border-gray-300 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                        <option value="">{{ trans('global.pleaseSelect') }}</option>
                        @foreach ($priorities as $priority)
                            <option value="{{ $priority->id }}"
                                {{ request()->input('priority_id') == $priority->id ? 'selected' : '' }}>
                                {{ $priority->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
            <h3 class="text-sm sm:text-base lg:text-lg font-semibold text-gray-800 dark:text-gray-200">
                {{ trans('global.analytics.task.fields.total_task') }}
            </h3>
            <p class="text-lg sm:text-xl lg:text-2xl text-gray-600 dark:text-gray-400" id="total-tasks">
                {{ $summary['total_tasks'] ?? 0 }}
            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
            <h3 class="text-sm sm:text-base lg:text-lg font-semibold text-gray-800 dark:text-gray-200">
                {{ trans('global.analytics.task.fields.completed_task') }}
            </h3>
            <p class="text-lg sm:text-xl lg:text-2xl text-gray-600 dark:text-gray-400" id="completed-tasks">
                {{ $summary['completed_tasks'] ?? 0 }}
            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
            <h3 class="text-sm sm:text-base lg:text-lg font-semibold text-gray-800 dark:text-gray-200">
                {{ trans('global.analytics.task.fields.overdue_task') }}
            </h3>
            <p class="text-lg sm:text-xl lg:text-2xl text-gray-600 dark:text-gray-400" id="overdue-tasks">
                {{ $summary['overdue_tasks'] ?? 0 }}
            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
            <h3 class="text-sm sm:text-base lg:text-lg font-semibold text-gray-800 dark:text-gray-200">
                {{ trans('global.analytics.task.fields.avg_progress') }}
            </h3>
            <p class="text-lg sm:text-xl lg:text-2xl text-gray-600 dark:text-gray-400" id="average-progress">
                {{ $summary['average_progress'] ?? 0 }}%
            </p>
        </div>
    </div>

    {{-- Charts and Task Table Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6 px-4 sm:px-6 lg:px-8">
        <!-- Charts (col-lg-1) -->
        <div class="lg:col-span-1">
            <div class="space-y-6">
                <div class="bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-lg shadow">
                    <h3 class="text-sm sm:text-base lg:text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                        {{ trans('global.analytics.task.fields.task_status_distribution') }}
                    </h3>
                    <div class="relative w-full max-w-[160px] sm:max-w-[200px] aspect-square mx-auto">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="text-center">
                                <p class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white"
                                    id="statusPercentage">
                                    {{ round(array_sum($charts['status']['data'] ?? []) ? (($charts['status']['data'][0] ?? 0) / array_sum($charts['status']['data'] ?? [])) * 100 : 0) }}%
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400" id="statusMainLabel">
                                    {{ $charts['status']['labels'][0] ?? 'Main Status' }}
                                </p>
                            </div>
                        </div>
                        <canvas id="statusChart" class="w-full h-full"></canvas>
                    </div>
                    <div class="flex flex-wrap justify-around text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-2"
                        id="statusLegend">
                        @foreach ($charts['status']['labels'] ?? [] as $index => $label)
                            <div class="text-center mx-2">
                                <div class="w-2 h-2 sm:w-3 sm:h-3 rounded-full inline-block mr-1 sm:mr-2"
                                    style="background-color: {{ $charts['status']['colors'][$index] ?? '#6B7280' }}">
                                </div>
                                <span>{{ $label }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-lg shadow">
                    <h3 class="text-sm sm:text-base lg:text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                        {{ trans('global.analytics.task.fields.task_priority_distribution') }}
                    </h3>
                    <div class="relative w-full max-w-[160px] sm:max-w-[200px] aspect-square mx-auto">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="text-center">
                                <p class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white"
                                    id="priorityPercentage">
                                    {{ round(array_sum($charts['priority']['data'] ?? []) ? (($charts['priority']['data'][0] ?? 0) / array_sum($charts['priority']['data'] ?? [])) * 100 : 0) }}%
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400" id="priorityMainLabel">
                                    {{ $charts['priority']['labels'][0] ?? 'Main Priority' }}
                                </p>
                            </div>
                        </div>
                        <canvas id="priorityChart" class="w-full h-full"></canvas>
                    </div>
                    <div class="flex flex-wrap justify-around text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-2"
                        id="priorityLegend">
                        @foreach ($charts['priority']['labels'] ?? [] as $index => $label)
                            <div class="text-center mx-2">
                                <div class="w-2 h-2 sm:w-3 sm:h-3 rounded-full inline-block mr-1 sm:mr-2"
                                    style="background-color: {{ $charts['priority']['colors'][$index] ?? '#6B7280' }}">
                                </div>
                                <span>{{ $label }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Task Table (col-lg-3) -->
        <div class="lg:col-span-3">
            <div class="bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-lg shadow mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm sm:text-base lg:text-lg font-semibold text-gray-800 dark:text-gray-200">
                        {{ trans('global.analytics.task.fields.task_details') }}
                    </h3>
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
                                    {{ trans('global.task.fields.title') }}
                                </th>
                                <th
                                    class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider sm:table-cell">
                                    {{ trans('global.task.fields.project_id') }}
                                </th>
                                <th
                                    class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ trans('global.task.fields.status_id') }}
                                </th>
                                <th
                                    class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider md:table-cell">
                                    {{ trans('global.task.fields.priority_id') }}
                                </th>
                                <th
                                    class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider md:table-cell">
                                    {{ trans('global.task.fields.progress') }}
                                </th>
                                <th
                                    class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider lg:table-cell">
                                    {{ trans('global.task.fields.due_date') }}
                                </th>
                                <th
                                    class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider xl:table-cell">
                                    {{ trans('global.task.fields.user_id') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"
                            id="taskTableBody">
                            @if (isset($tableData) && is_array($tableData) && count($tableData) > 0)
                                @foreach ($tableData as $row)
                                    <tr>
                                        <td
                                            class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                                            {{ $row['title'] ?? 'N/A' }}
                                        </td>
                                        <td
                                            class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-gray-600 dark:text-gray-300 sm:table-cell">
                                            <span
                                                class="inline-flex items-center justify-center px-2 py-1 rounded-full bg-gray-200 text-black dark:bg-gray-700 dark:text-white text-xs">{{ $row['project'] ?? 'N/A' }}</span>
                                        </td>
                                        <td
                                            class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                                            <span
                                                class="inline-flex items-center justify-center px-2 py-1 rounded-full text-xs"
                                                style="background-color: {{ $row['status']['color'] ?? 'gray' }}">{{ $row['status']['title'] ?? 'N/A' }}</span>
                                        </td>
                                        <td
                                            class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-gray-600 dark:text-gray-300 md:table-cell">
                                            <span
                                                class="inline-flex items-center justify-center px-2 py-1 rounded-full text-xs"
                                                style="background-color: {{ $row['priority']['color'] ?? 'gray' }}">{{ $row['priority']['title'] ?? 'N/A' }}</span>
                                        </td>
                                        <td
                                            class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-gray-600 dark:text-gray-300 md:table-cell">
                                            {{ $row['progress'] ?? 'N/A' }}%
                                        </td>
                                        <td
                                            class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-gray-600 dark:text-gray-300 lg:table-cell">
                                            {{ $row['due_date'] ?? 'N/A' }}
                                        </td>
                                        <td
                                            class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-gray-600 dark:text-gray-300 xl:table-cell">
                                            <div class="flex flex-wrap gap-2">
                                                @if (isset($row['users']) && is_array($row['users']) && count($row['users']) > 0)
                                                    @foreach ($row['users'] as $user)
                                                        <span
                                                            class="inline-flex items-center justify-center px-2 py-1 rounded-full bg-gray-200 text-black dark:bg-gray-700 dark:text-white text-xs">
                                                            {{ is_object($user) ? $user->name ?? 'Unknown User' : $user ?? 'Unknown User' }}
                                                        </span>
                                                    @endforeach
                                                @else
                                                    <span class="text-gray-500 dark:text-gray-400 text-xs">No
                                                        Users</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="7"
                                        class="px-4 sm:px-6 py-4 text-center text-sm text-gray-600 dark:text-gray-300">
                                        No tasks available
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                    <div class="mt-4" id="pagination">
                        @if (isset($tasks))
                            {{ $tasks->links() }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
        <script>
            // Store initial data globally
            try {
                window.initialData = @json($data ?? null);
                console.log('Initial Data:', window.initialData);
                // Log users data specifically
                if (window.initialData?.tableData) {
                    console.log('Table Data Users:', window.initialData.tableData.map(row => row.users));
                }
            } catch (e) {
                console.error('Error parsing initial data:', e);
                window.initialData = null;
            }

            let statusChartInstance = null;
            let priorityChartInstance = null;

            function updateChartsAndTable(data) {
                try {
                    // Log data for debugging
                    console.log('updateChartsAndTable called with:', data);

                    // Update summary with fallbacks
                    document.getElementById('total-tasks').textContent = data?.summary?.total_tasks ?? 0;
                    document.getElementById('completed-tasks').textContent = data?.summary?.completed_tasks ?? 0;
                    document.getElementById('overdue-tasks').textContent = data?.summary?.overdue_tasks ?? 0;
                    document.getElementById('average-progress').textContent = (data?.summary?.average_progress ?? 0) + '%';

                    // Prepare chart data with fallbacks
                    const statusData = Array.isArray(data?.charts?.status?.data) ? data.charts.status.data : [0];
                    const statusLabels = Array.isArray(data?.charts?.status?.labels) ? data.charts.status.labels : ['No Data'];
                    const statusColors = Array.isArray(data?.charts?.status?.colors) ? data.charts.status.colors : ['#6B7280'];
                    const priorityData = Array.isArray(data?.charts?.priority?.data) ? data.charts.priority.data : [0];
                    const priorityLabels = Array.isArray(data?.charts?.priority?.labels) ? data.charts.priority.labels : [
                        'No Data'
                    ];
                    const priorityColors = Array.isArray(data?.charts?.priority?.colors) ? data.charts.priority.colors : [
                        '#6B7280'
                    ];

                    // Log chart data
                    console.log('Status Chart Data:', {
                        labels: statusLabels,
                        data: statusData,
                        colors: statusColors
                    });
                    console.log('Priority Chart Data:', {
                        labels: priorityLabels,
                        data: priorityData,
                        colors: priorityColors
                    });

                    // Update status chart
                    const statusCtx = document.getElementById('statusChart')?.getContext('2d');
                    if (!statusCtx) {
                        console.error('Status chart canvas not found');
                    } else {
                        if (statusChartInstance) statusChartInstance.destroy();
                        statusChartInstance = new Chart(statusCtx, {
                            type: 'doughnut',
                            data: {
                                labels: statusLabels.length ? statusLabels : ['No Data'],
                                datasets: [{
                                    data: statusData.length ? statusData : [1],
                                    backgroundColor: statusColors.length ? statusColors : ['#6B7280'],
                                    borderWidth: 0,
                                }],
                            },
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
                                responsive: true,
                            }
                        });
                    }

                    // Update status legend
                    const statusLegend = document.getElementById('statusLegend');
                    statusLegend.innerHTML = statusLabels.length ? statusLabels.map((label, index) => `
                        <div class="text-center mx-2">
                            <div class="w-2 h-2 sm:w-3 sm:h-3 rounded-full inline-block mr-1 sm:mr-2" style="background-color: ${statusColors[index] || '#6B7280'}"></div>
                            <span>${label || 'Unknown'}</span>
                        </div>
                    `).join('') : '<span class="text-gray-500 dark:text-gray-400 text-xs">No status data</span>';

                    // Update status percentage
                    const statusPercentage = document.getElementById('statusPercentage');
                    const statusMainLabel = document.getElementById('statusMainLabel');
                    const statusTotal = statusData.reduce((a, b) => a + b, 0);
                    statusPercentage.textContent = statusTotal ? Math.round((statusData[0] || 0) / statusTotal * 100) + '%' :
                        '0%';
                    statusMainLabel.textContent = statusLabels[0] || 'No Status';

                    // Update priority chart
                    const priorityCtx = document.getElementById('priorityChart')?.getContext('2d');
                    if (!priorityCtx) {
                        console.error('Priority chart canvas not found');
                    } else {
                        if (priorityChartInstance) priorityChartInstance.destroy();
                        priorityChartInstance = new Chart(priorityCtx, {
                            type: 'doughnut',
                            data: {
                                labels: priorityLabels.length ? priorityLabels : ['No Data'],
                                datasets: [{
                                    data: priorityData.length ? priorityData : [1],
                                    backgroundColor: priorityColors.length ? priorityColors : ['#6B7280'],
                                    borderWidth: 0,
                                }],
                            },
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
                                responsive: true,
                            }
                        });
                    }

                    // Update priority legend
                    const priorityLegend = document.getElementById('priorityLegend');
                    priorityLegend.innerHTML = priorityLabels.length ? priorityLabels.map((label, index) => `
                        <div class="text-center mx-2">
                            <div class="w-2 h-2 sm:w-3 sm:h-3 rounded-full inline-block mr-1 sm:mr-2" style="background-color: ${priorityColors[index] || '#6B7280'}"></div>
                            <span>${label || 'Unknown'}</span>
                        </div>
                    `).join('') : '<span class="text-gray-500 dark:text-gray-400 text-xs">No priority data</span>';

                    // Update priority percentage
                    const priorityPercentage = document.getElementById('priorityPercentage');
                    const priorityMainLabel = document.getElementById('priorityMainLabel');
                    const priorityTotal = priorityData.reduce((a, b) => a + b, 0);
                    priorityPercentage.textContent = priorityTotal ? Math.round((priorityData[0] || 0) / priorityTotal * 100) +
                        '%' : '0%';
                    priorityMainLabel.textContent = priorityLabels[0] || 'No Priority';

                    // Update table
                    const taskTableBody = document.getElementById('taskTableBody');
                    taskTableBody.innerHTML = (data?.tableData?.length ?? 0) ? data.tableData.map(row => {
                        // Ensure users is an array of strings
                        const users = Array.isArray(row.users) ? row.users.map(user =>
                            typeof user === 'object' && user ? (user.name || 'Unknown User') : (user ||
                                'Unknown User')
                        ) : [];
                        return `
                        <tr>
                            <td class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-gray-600 dark:text-gray-300">${row.title || 'N/A'}</td>
                            <td class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-gray-600 dark:text-gray-300 sm:table-cell">
                                <span class="inline-flex items-center justify-center px-2 py-1 rounded-full bg-gray-200 text-black dark:bg-gray-700 dark:text-white text-xs">${row.project || 'N/A'}</span>
                            </td>
                            <td class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                                <span class="inline-flex items-center justify-center px-2 py-1 rounded-full text-xs" style="background-color: ${row.status?.color || 'gray'}">${row.status?.title || 'N/A'}</span>
                            </td>
                            <td class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-gray-600 dark:text-gray-300 md:table-cell">
                                <span class="inline-flex items-center justify-center px-2 py-1 rounded-full text-xs" style="background-color: ${row.priority?.color || 'gray'}">${row.priority?.title || 'N/A'}</span>
                            </td>
                            <td class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-gray-600 dark:text-gray-300 md:table-cell">${row.progress || 'N/A'}%</td>
                            <td class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-gray-600 dark:text-gray-300 lg:table-cell">${row.due_date || 'N/A'}</td>
                            <td class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-gray-600 dark:text-gray-300 xl:table-cell">
                                <div class="flex flex-wrap gap-2">
                                    ${users.length ? users.map(user => `
                                                        <span class="inline-flex items-center justify-center px-2 py-1 rounded-full bg-gray-200 text-black dark:bg-gray-700 dark:text-white text-xs">${user}</span>
                                                    `).join('') : '<span class="text-gray-500 dark:text-gray-400 text-xs">No Users</span>'}
                                </div>
                            </td>
                        </tr>
                    `;
                    }).join('') : `
                        <tr>
                            <td colspan="7" class="px-4 sm:px-6 py-4 text-center text-sm text-gray-600 dark:text-gray-300">
                                No tasks available
                            </td>
                        </tr>
                    `;

                    // Update pagination
                    const pagination = document.getElementById('pagination');
                    pagination.innerHTML = data?.tasks?.links || '';
                } catch (error) {
                    console.error('Error in updateChartsAndTable:', error);
                    console.warn('Preserving existing table content due to error');
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                // Verify Chart.js is loaded
                if (typeof Chart === 'undefined') {
                    console.error('Chart.js not loaded');
                    document.getElementById('statusLegend').innerHTML =
                        '<span class="text-gray-500 dark:text-gray-400 text-xs">Chart.js failed to load</span>';
                    document.getElementById('priorityLegend').innerHTML =
                        '<span class="text-gray-500 dark:text-gray-400 text-xs">Chart.js failed to load</span>';
                    return;
                }

                // Initialize charts and table
                if (window.initialData && window.initialData.summary && window.initialData.charts && window.initialData
                    .tableData) {
                    console.log('Initializing with data:', window.initialData);
                    updateChartsAndTable(window.initialData);
                } else {
                    console.error('Initial data invalid or incomplete:', window.initialData);
                    // Preserve Blade-rendered table
                    document.getElementById('taskTableBody').innerHTML = document.getElementById('taskTableBody')
                        .innerHTML;
                }

                // Filter handling with AJAX
                document.addEventListener('change', function(event) {
                    const target = event.target;
                    if (target.matches(
                            'select[name="directorate_id"], select[name="project_id"], select[name="status_id"], select[name="priority_id"]'
                        )) {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                            'content');
                        if (!csrfToken) {
                            console.error('CSRF token not found');
                            alert('CSRF token missing. Please refresh the page.');
                            return;
                        }

                        const directorateId = document.querySelector('select[name="directorate_id"]')?.value ||
                            '';
                        const projectId = document.querySelector('select[name="project_id"]')?.value || '';
                        const statusId = document.querySelector('select[name="status_id"]')?.value || '';
                        const priorityId = document.querySelector('select[name="priority_id"]')?.value || '';

                        const url = new URL('{{ route('admin.analytics.task') }}', window.location.origin);
                        if (directorateId) url.searchParams.set('directorate_id', directorateId);
                        if (projectId) url.searchParams.set('project_id', projectId);
                        if (statusId) url.searchParams.set('status_id', statusId);
                        if (priorityId) url.searchParams.set('priority_id', priorityId);

                        console.log('Filter Request URL:', url.toString());
                        window.history.pushState({}, '', url.toString());

                        fetch(url.toString(), {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': csrfToken,
                                },
                            })
                            .then(response => {
                                console.log('Filter Response Status:', response.status);
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
                                console.log('Filter Data Loaded:', data);
                                updateChartsAndTable(data);
                            })
                            .catch(error => {
                                console.error('Filter request failed:', error);
                                alert('Failed to apply filters: ' + error.message);
                                console.warn('Preserving existing table content due to filter error');
                            });
                    }
                }, true);
            });
        </script>
    @endpush
</x-layouts.app>
