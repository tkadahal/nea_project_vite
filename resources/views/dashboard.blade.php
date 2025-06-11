<x-layouts.app>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Dashboard</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Welcome to the dashboard</p>
    </div>

    <!-- Number Blocks Section -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        @foreach ($number_blocks as $block)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $block['title'] }}</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-1">{{ $block['number'] }}</p>
                        <a href="{{ $block['url'] }}"
                            class="text-xs text-blue-500 hover:text-blue-600 dark:text-blue-400 dark:hover:text-blue-300 flex items-center mt-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                            View Details
                        </a>
                    </div>
                    <div
                        class="p-3 rounded-full {{ $block['title'] == trans('global.user.title')
                            ? 'bg-blue-100 dark:bg-blue-900'
                            : ($block['title'] == trans('global.project.title')
                                ? 'bg-green-100 dark:bg-green-900'
                                : ($block['title'] == trans('global.contract.title')
                                    ? 'bg-yellow-100 dark:bg-yellow-900'
                                    : 'bg-purple-100 dark:bg-purple-900')) }}">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-6 w-6 {{ $block['title'] == trans('global.user.title')
                                ? 'text-blue-500 dark:text-blue-300'
                                : ($block['title'] == trans('global.project.title')
                                    ? 'text-green-500 dark:text-green-300'
                                    : ($block['title'] == trans('global.contract.title')
                                        ? 'text-yellow-500 dark:text-yellow-300'
                                        : 'text-purple-500 dark:text-purple-300')) }}"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            @if ($block['title'] == trans('global.user.title'))
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            @elseif ($block['title'] == trans('global.project.title'))
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2-5h12M5 7h12m-12 4h12" />
                            @elseif ($block['title'] == trans('global.contract.title'))
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            @elseif ($block['title'] == trans('global.task.title'))
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01" />
                            @endif
                        </svg>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Project Status and Tasks Section -->
    <div class="grid grid-cols-12 gap-6 mb-6">
        <!-- Project Status -->
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700 col-span-4">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Project Status</h2>
                <button class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                    </svg>
                </button>
            </div>
            <div class="flex justify-center mb-4">
                <div class="relative w-40 h-40">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                                {{ $project_status['completed'] }}%
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Completed</p>
                        </div>
                    </div>
                    <canvas id="projectStatusChart" width="160" height="160" style="min-height: 160px;"></canvas>
                </div>
            </div>
            <div class="flex justify-around text-sm text-gray-600 dark:text-gray-400">
                <div class="text-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full inline-block mr-2"></div>
                    <span>Completed</span>
                    <p class="text-gray-800 dark:text-gray-100 font-medium">{{ $project_status['completed'] }}%</p>
                </div>
                <div class="text-center">
                    <div class="w-3 h-3 bg-purple-500 rounded-full inline-block mr-2"></div>
                    <span>In-progress</span>
                    <p class="text-gray-800 dark:text-gray-100 font-medium">{{ $project_status['in_progress'] }}%</p>
                </div>
                <div class="text-center">
                    <div class="w-3 h-3 bg-red-500 rounded-full inline-block mr-2"></div>
                    <span>Behind</span>
                    <p class="text-gray-800 dark:text-gray-100 font-medium">{{ $project_status['behind'] }}%</p>
                </div>
            </div>
            <!-- Debugging Output -->
            <p class="text-xs text-gray-500 mt-2">Debug: completed={{ $project_status['completed'] }},
                in_progress={{ $project_status['in_progress'] }}, behind={{ $project_status['behind'] }}</p>
        </div>

        <!-- Tasks -->
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700 col-span-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Tasks</h2>
                <button class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                    </svg>
                </button>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                {{ $tasks->where('status.title', 'Completed')->count() }} Tasks completed out of
                {{ $tasks->count() + $tasks->where('status.title', 'Completed')->count() }}
            </p>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-gray-700 dark:text-gray-300">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="py-2 px-4 text-left">Task Name</th>
                            <th class="py-2 px-4 text-left">Status</th>
                            <th class="py-2 px-4 text-left">Assigned to</th>
                            <th class="py-2 px-4 text-left">Total time spend</th>
                            <th class="py-2 px-4 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tasks as $task)
                            <tr class="border-t dark:border-gray-600">
                                <td class="py-2 px-4">{{ $task->name }}</td>
                                <td class="py-2 px-4">
                                    <span
                                        class="{{ $task->status->title == 'Completed' ? 'text-green-500' : ($task->status->title == 'In-progress' ? 'text-purple-500' : 'text-red-500') }}">
                                        {{ $task->status->title }}
                                    </span>
                                </td>
                                <td class="py-2 px-4">{{ $task->assigned_to }}</td>
                                <td class="py-2 px-4">{{ $task->total_time_spent }}</td>
                                <td class="py-2 px-4">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.task.edit', ['task' => $task->id]) }}"
                                            class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('admin.task.destroy', ['task' => $task->id]) }}"
                                            method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Task Sprint Overview Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Tasks Overview</h2>
            <div class="space-x-2">
                <button class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-sm">ALL</button>
                <button class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-sm">1M</button>
                <button class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-sm">6M</button>
                <button class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-sm">1Y</button>
            </div>
        </div>
        <div class="relative w-full h-64">
            <canvas id="taskSprintChart"></canvas>
        </div>
    </div>

    <!-- Activity Logs and Calendar Section -->
    <div class="grid grid-cols-12 gap-6 mb-6">
        <!-- Activity Logs -->
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700 col-span-4">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Activity Logs</h2>
                <button class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                    </svg>
                </button>
            </div>
            <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                @forelse ($activity_logs as $log)
                    <div class="flex items-start space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 mt-0.5" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="font-medium text-gray-800 dark:text-gray-100">
                                {{ $log->description }}
                            </p>
                            @if ($log->subject_type && $log->subject_id)
                                <p class="text-xs">
                                    {{ class_basename($log->subject_type) }} ID: {{ $log->subject_id }}
                                </p>
                            @endif
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $log->created_at }}
                            </p>
                        </div>
                    </div>
                @empty
                    <p>No recent activities.</p>
                @endforelse
            </div>
        </div>

        <!-- Calendar -->
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700 col-span-8">
            @livewire('calendar')
        </div>
    </div>
</x-layouts.app>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Chart initialization started');
        const ctx = document.getElementById('projectStatusChart');
        if (ctx) {
            const context = ctx.getContext('2d');
            const data = {
                datasets: [{
                    data: [{{ $project_status['completed'] }},
                        {{ $project_status['in_progress'] }}, {{ $project_status['behind'] }}
                    ],
                    backgroundColor: ['#10b981', '#8b5cf6', '#ef4444'],
                    borderWidth: 0,
                }],
                labels: ['Completed', 'In-progress', 'Behind']
            };
            console.log('Project Status Chart data:', data);

            new Chart(context, {
                type: 'doughnut',
                data: data,
                options: {
                    cutout: '70%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: false
                        }
                    },
                    maintainAspectRatio: false
                }
            });
            console.log('Project Status Chart initialization completed');
        }

        const sprintCtx = document.getElementById('taskSprintChart');
        if (sprintCtx) {
            const sprintContext = sprintCtx.getContext('2d');
            const sprintLabels = @json(array_keys($sprint_data));
            console.log('Sprint Labels before reversal:', sprintLabels);

            // Reverse labels and data if needed to start from Sprint 1
            const reversedLabels = sprintLabels.reverse();
            console.log('Sprint Labels after reversal:', reversedLabels);

            const sprintData = {
                labels: reversedLabels,
                datasets: [{
                        label: 'To Do',
                        data: @json(array_column($sprint_data, 'todo')).reverse(),
                        backgroundColor: 'rgba(239, 68, 68, 0.6)', // Red
                        borderColor: 'rgba(239, 68, 68, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'In Progress',
                        data: @json(array_column($sprint_data, 'in_progress')).reverse(),
                        backgroundColor: 'rgba(139, 92, 246, 0.6)', // Purple
                        borderColor: 'rgba(139, 92, 246, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Completed',
                        data: @json(array_column($sprint_data, 'completed')).reverse(),
                        backgroundColor: 'rgba(16, 185, 129, 0.6)', // Green
                        borderColor: 'rgba(16, 185, 129, 1)',
                        borderWidth: 1
                    }
                ]
            };
            console.log('Task Sprint Chart data:', sprintData);

            new Chart(sprintContext, {
                type: 'bar',
                data: sprintData,
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Tasks'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Sprints'
                            }
                        }
                    },
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true
                        }
                    }
                }
            });
            console.log('Task Sprint Chart initialization completed');
        }
    });
</script>
