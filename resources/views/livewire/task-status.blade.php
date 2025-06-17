<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 sm:p-6 border border-gray-200 dark:border-gray-700 col-span-12 lg:col-span-8"
    id="task-status-component">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-base sm:text-sm font-semibold text-gray-800 dark:text-white">Tasks</h2>
        <div class="flex items-center space-x-4">
            <a class="px-1 py-1 sm:px-2 sm:py-1 bg-gray-200 dark:bg-gray-700 rounded text-xs sm:text-sm"
                href="{{ route('admin.tasks.ganttChart') }}">CHART</a>

            <div class="relative" wire:ignore>
                <button
                    class="dropdown-toggle-task text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                    </svg>
                </button>
                <div
                    class="dropdown-menu-task hidden absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-xl z-[1000]">
                    <div class="p-3">
                        @if (!empty($availableDirectorates))
                            <div class="mb-3">
                                <input type="text" id="task-directorate-search"
                                    class="w-full text-sm text-gray-800 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-500 rounded-md px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-400 dark:placeholder-gray-500"
                                    placeholder="Search directorates...">
                            </div>
                            <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">Filter by
                                Directorate
                            </h3>
                            <button wire:click="$set('directorateFilter', null)"
                                class="directorate-option-task block w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-md"
                                data-filter="all" data-name="All Directorates">All Directorates</button>
                            @foreach ($availableDirectorates as $id => $name)
                                <button wire:click="$set('directorateFilter', {{ $id }})"
                                    class="directorate-option-task block w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-md {{ $loop->index >= 3 ? 'hidden' : '' }}"
                                    data-filter="{{ $id }}"
                                    data-name="{{ $name }}">{{ $name }}</button>
                            @endforeach
                            <div id="task-no-results" class="hidden px-3 py-2 text-sm text-gray-500 dark:text-gray-400">
                                No
                                results found</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mb-4" id="task-summary">
        {{ $tasks->where('status.title', 'Completed')->count() }} Tasks completed out of
        {{ $tasks->count() + $tasks->where('status.title', 'Completed')->count() }}
    </p>
    <div class="overflow-x-auto">
        <table class="w-full text-xs sm:text-sm text-gray-700 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="py-2 px-2 sm:px-4 text-left">Task Name</th>
                    <th class="py-2 px-2 sm:px-4 text-left hidden sm:table-cell">Status</th>
                    <th class="py-2 px-2 sm:px-4 text-left hidden md:table-cell">Assigned to</th>
                    <th class="py-2 px-2 sm:px-4 text-left hidden lg:table-cell">Total time</th>
                    <th class="py-2 px-2 sm:px-4 text-left">Actions</th>
                </tr>
            </thead>
            <tbody id="task-table">
                @foreach ($tasks as $task)
                    <tr class="border-t dark:border-gray-600">
                        <td class="py-2 px-2 sm:px-4">
                            {{ $task->name }}
                            <div class="sm:hidden text-xs text-gray-500 dark:text-gray-400 mt-1">
                                <span
                                    class="{{ $task->status->title == 'Completed' ? 'text-green-500' : ($task->status->title == 'In-progress' ? 'text-purple-500' : 'text-red-500') }}">
                                    {{ $task->status->title }}
                                </span>
                                <br>
                                Assigned: {{ $task->assigned_to }}
                                <br>
                                Time: {{ $task->total_time_spent }}
                            </div>
                        </td>
                        <td class="py-2 px-2 sm:px-4 hidden sm:table-cell">
                            <span
                                class="{{ $task->status->title == 'Completed' ? 'text-green-500' : ($task->status->title == 'In-progress' ? 'text-purple-500' : 'text-red-500') }}">
                                {{ $task->status->title }}
                            </span>
                        </td>
                        <td class="py-2 px-2 sm:px-4 hidden md:table-cell">{{ $task->assigned_to }}</td>
                        <td class="py-2 px-2 sm:px-4 hidden lg:table-cell">{{ $task->total_time_spent }}</td>
                        <td class="py-2 px-2 sm:px-4">
                            <div class="flex space-x-1 sm:space-x-2">
                                <a href="{{ route('admin.task.edit', ['task' => $task->id]) }}"
                                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </a>
                                <form action="{{ route('admin.task.destroy', ['task' => $task->id]) }}" method="POST"
                                    style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
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

<script>
    function waitForJQuery(callback) {
        if (typeof jQuery !== 'undefined' && jQuery.fn.jquery === '3.7.1') {
            callback(jQuery);
        } else {
            setTimeout(() => waitForJQuery(callback), 100);
        }
    }

    waitForJQuery(function($) {
        const $taskContainer = $('#task-status-component');

        $taskContainer.find('.dropdown-toggle-task').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const $dropdown = $taskContainer.find('.dropdown-menu-task');
            $dropdown.toggleClass('hidden');
            console.log('Task dropdown toggled, visible:', !$dropdown.hasClass('hidden'), 'position:',
                $dropdown.position());
            $taskContainer.find('#task-directorate-search').val('');
            $taskContainer.find('.directorate-option-task').each(function(index) {
                $(this).toggleClass('hidden', index >= 4);
            });
            $taskContainer.find('#task-no-results').addClass('hidden');
        });

        $(document).on('click', function(event) {
            if (!$(event.target).closest('.dropdown-toggle-task, .dropdown-menu-task').length) {
                $taskContainer.find('.dropdown-menu-task').addClass('hidden');
                console.log('Task dropdown closed');
            }
        });

        $taskContainer.find('.directorate-option-task').on('click', function() {
            const filterValue = $(this).data('filter');
            console.log('Task filter clicked:', filterValue);
        });

        $taskContainer.find('#task-directorate-search').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            console.log('Task search term:', searchTerm);
            let visibleCount = 0;

            $taskContainer.find('.directorate-option-task').each(function() {
                const name = $(this).data('name').toLowerCase();
                const isAllDirectorates = $(this).data('filter') === 'all';
                if (isAllDirectorates || name.includes(searchTerm)) {
                    $(this).removeClass('hidden');
                    visibleCount++;
                } else {
                    $(this).addClass('hidden');
                }
            });

            $taskContainer.find('#task-no-results').toggleClass('hidden', visibleCount > 0);
        });

        const component = document.getElementById('task-status-component');
        if (component) {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList' || mutation.type === 'characterData') {
                        console.log('Task table updated');
                    }
                });
            });
            observer.observe(component, {
                childList: true,
                subtree: true,
                characterData: true
            });
        }
    });
</script>
