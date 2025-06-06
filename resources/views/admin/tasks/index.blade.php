<x-layouts.app>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                {{ __('Tasks') }}
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Manage your tasks') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <div class="inline-flex rounded-md shadow-sm" role="group">
                <button type="button"
                    class="view-switch px-3 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-l-md hover:bg-gray-100 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 {{ $activeView === 'board' ? 'active-view-button' : '' }}"
                    data-view="board">
                    🗄️
                </button>
                <button type="button"
                    class="view-switch px-3 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 hover:bg-gray-100 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 {{ $activeView === 'list' ? 'active-view-button' : '' }}"
                    data-view="list">
                    📋
                </button>
                <button type="button"
                    class="view-switch px-3 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 hover:bg-gray-100 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 {{ $activeView === 'calendar' ? 'active-view-button' : '' }}"
                    data-view="calendar">
                    📅
                </button>
                <button type="button"
                    class="view-switch px-3 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-r-md hover:bg-gray-100 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 {{ $activeView === 'table' ? 'active-view-button' : '' }}"
                    data-view="table">
                    📊
                </button>
            </div>
            <a href="{{ route('admin.task.create') }}"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-700 dark:hover:bg-blue-800 dark:focus:ring-offset-gray-900">
                {{ __('Add New') }}
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if ($activeView === 'list')
        <div id="task-search" class="mb-4">
            <input type="text" id="taskSearchInput" placeholder="{{ trans('global.search') }}..."
                class="w-full max-w-md p-2 border border-gray-300 dark:border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300">
        </div>
    @endif

    @if ($activeView === 'board')
        <x-tasks.task-board :tasks="$tasks" :statuses="$statuses" :status-colors="$statusColors" :priority-colors="$priorityColors" />
    @elseif ($activeView === 'list')
        <x-tasks.task-list :tasks-flat="$tasksFlat" />
    @elseif ($activeView === 'calendar')
        <x-tasks.task-calendar :events="$calendarData" />
    @elseif ($activeView === 'table')
        <x-table.dataTable :headers="$tableHeaders" :data="$tableData" :routePrefix="$routePrefix" :deleteConfirmationMessage="$deleteConfirmationMessage" :actions="$actions"
            arrayColumnColor="gray" />
    @endif

    <x-tasks.task-filter-drawer :statuses="$statuses" :priorities="$priorities" :projects-for-filter="$projectsForFilter" />

    <style>
        .view-switch.active-view-button {
            background-color: #e2e8f0;
            color: #1a202c;
            font-weight: bold;
        }

        .dark .view-switch.active-view-button {
            background-color: #4a5568;
            color: #ffffff;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const viewSwitches = document.querySelectorAll('.view-switch');

            // Initialize active view (e.g., for calendar or board-specific scripts)
            @if ($activeView === 'calendar' && isset($calendarData))
                if (typeof window.renderTaskCalendar === 'function') {
                    window.renderTaskCalendar();
                }
            @elseif ($activeView === 'board')
                if (typeof window.attachDragAndDropListeners === 'function') {
                    window.attachDragAndDropListeners();
                }
            @endif

            viewSwitches.forEach(button => {
                button.addEventListener('click', () => {
                    const view = button.getAttribute('data-view');
                    // Update session via AJAX or form submission
                    fetch('{{ route('admin.task.set-view') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            task_view_preference: view
                        })
                    }).then(response => {
                        if (response.ok) {
                            window.location.reload(); // Reload to render new view
                        }
                    });
                });
            });
        });
    </script>
</x-layouts.app>
