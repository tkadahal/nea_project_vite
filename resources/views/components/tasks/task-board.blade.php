@props(['tasks', 'statuses', 'statusColors', 'priorityColors'])

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    @foreach ($statuses as $status)
        <div class="kanban-column">
            @php
                $columnHeaderColor = $statusColors[$status->id] ?? 'gray';
                $statusTasks = ($tasks[$status->id] ?? collect())->values()->toArray();
                $visibleTasks = array_slice($statusTasks, 0, 5);
                \Illuminate\Support\Facades\Log::debug('Task count for status', [
                    'status_id' => $status->id,
                    'status_title' => $status->title,
                    'task_count' => count($statusTasks),
                    'tasks' => $statusTasks,
                ]);
            @endphp
            <div class="text-white p-3 rounded-t-md" style="background-color: {{ $columnHeaderColor }};">
                <h4 class="font-bold m-0">{{ $status->title }} ({{ count($statusTasks) }})</h4>
            </div>
            <div class="kanban-list bg-gray-50 dark:bg-gray-700 p-3 rounded-b-md" id="status_{{ $status->id }}"
                data-status-id="{{ $status->id }}">
                @foreach ($visibleTasks as $task)
                    <div class="card-item bg-white dark:bg-gray-800 p-4 rounded-md shadow-md mb-3 kanban-item"
                        data-id="{{ $task['id'] }}" data-project-id="{{ $task['project_id'] }}"
                        data-search="{{ strtolower($task['title'] . ' ' . ($task['description'] ?? '') . ' ' . ($task['priority']['title'] ?? '') . ' ' . ($task['status']['title'] ?? '')) }}"
                        data-status-id="{{ $task['status_id'] ?? $status->id }}"
                        data-priority-id="{{ $task['priority_id'] ?? '' }}">
                        <h5 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                            {{ $task['title'] }}
                            @if ($task['priority'])
                                <span class="badge inline-block px-2 py-1 text-xs font-semibold text-white rounded-full"
                                    style="background-color: {{ $task['priority']['color'] ?? 'gray' }};">
                                    {{ ucfirst($task['priority']['title']) }}
                                </span>
                            @endif
                        </h5>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">{!! $task['description'] ?? '' !!}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Project:
                            {{ $task['project_name'] ?? 'N/A' }}</p>
                        <a href="{{ $task['view_url'] }}"
                            class="mt-2 inline-block px-3 py-1 text-white rounded hover:opacity-80"
                            style="background-color: {{ $task['status_color'] ?? 'gray' }}">
                            {{ trans('global.view') }}
                        </a>
                    </div>
                @endforeach
                @if (count($statusTasks) > 5)
                    <button type="button"
                        class="load-more-btn w-full py-2 px-4 bg-blue-600 text-white font-semibold text-sm rounded-md shadow-md hover:bg-blue-700 hover:scale-105 transition-transform duration-200 ease-in-out dark:bg-blue-500 dark:hover:bg-blue-600 dark:shadow-gray-800 relative"
                        data-status-id="{{ $status->id }}" onclick="loadMoreTasks(this)">
                        <span class="load-more-text">
                            {{ trans('global.loadMore') }}
                        </span>
                        <span class="loading-spinner hidden absolute inset-0 flex items-center justify-center">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </span>
                    </button>
                @endif
            </div>
        </div>
    @endforeach
</div>

<style>
    .kanban-column {
        display: flex;
        flex-direction: column;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .kanban-list {
        min-height: 150px;
        border: 2px dashed transparent;
        transition: border-color 0.2s ease-in-out;
    }

    .kanban-list.sortable-ghost {
        border-color: #3b82f6;
    }

    .kanban-item {
        cursor: grab;
    }

    .kanban-item.sortable-chosen {
        opacity: 0.5;
        cursor: grabbing;
    }

    .load-more-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<script>
    // Define global variables
    const statusTitles = {};
    @foreach ($statuses as $status)
        statusTitles[{{ $status->id }}] = '{{ $status->title }}';
    @endforeach

    const statusColors = @json($statusColors);

    function getCsrfToken() {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!token) {
            console.error('CSRF token not found.');
            if (typeof toastr !== 'undefined') {
                toastr.error('CSRF token not found. Please refresh the page.');
            }
        }
        return token;
    }

    window.attachDragAndDropListeners = function() {
        const statusIdMap = {};
        @foreach ($statuses as $status)
            statusIdMap['status_{{ $status->id }}'] = {{ $status->id }};
        @endforeach

        function updateTaskStatus(taskId, projectId, newStatusId, draggableElement) {
            const token = getCsrfToken();
            if (!token) return;

            fetch("{{ route('admin.task.updateStatus') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        task_id: taskId,
                        project_id: projectId,
                        status_id: newStatusId
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => {
                            throw new Error(
                                `HTTP error! status: ${response.status}, message: ${err.message || JSON.stringify(err)}`
                            );
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Task status updated:', data);
                    window.pendingCalendarUpdates = window.pendingCalendarUpdates || {};
                    window.pendingCalendarUpdates[taskId] = {
                        status: statusTitles[newStatusId],
                        color: statusColors[newStatusId] || 'gray'
                    };

                    const viewButton = draggableElement.querySelector('a');
                    if (viewButton) {
                        viewButton.style.backgroundColor = statusColors[newStatusId] || 'gray';
                    }

                    if (typeof taskCalendar !== 'undefined' && taskCalendar) {
                        const event = taskCalendar.getEventById(taskId);
                        if (event) {
                            event.setExtendedProp('status', statusTitles[newStatusId]);
                            event.setProp('color', statusColors[newStatusId] || 'gray');
                            taskCalendar.render();
                            delete window.pendingCalendarUpdates[taskId];
                        }
                    }

                    if (typeof toastr !== 'undefined') {
                        toastr.success(data.message || 'Task status updated successfully!');
                    }
                })
                .catch(error => {
                    console.error('Error updating task status:', error.message);
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Error updating task status: ' + error.message);
                    }
                });
        }

        const kanbanLists = document.querySelectorAll('.kanban-list');
        if (kanbanLists.length === 0) {
            console.warn('No kanban lists found. SortableJS not initialized.');
            return;
        }

        kanbanLists.forEach(list => {
            new Sortable(list, {
                group: 'kanban',
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: function(evt) {
                    const draggableElement = evt.item;
                    const dropzone = evt.to;

                    if (evt.from !== dropzone) {
                        const taskId = draggableElement.dataset.id;
                        const projectId = draggableElement.dataset.projectId;
                        const newStatusId = statusIdMap[dropzone.id];

                        if (newStatusId === undefined) {
                            console.error('Invalid dropzone ID:', dropzone.id);
                            return;
                        }

                        if (!projectId) {
                            console.error('Project ID not found for task:', taskId);
                            if (typeof toastr !== 'undefined') {
                                toastr.error('Error: Project ID not found for this task.');
                            }
                            return;
                        }

                        draggableElement.dataset.statusId = newStatusId;
                        console.log(
                            `Moved task ${taskId} to status ${newStatusId} for project ${projectId}`
                        );
                        updateTaskStatus(taskId, projectId, newStatusId, draggableElement);
                    }
                }
            });
        });
    };

    document.addEventListener('DOMContentLoaded', () => {
        const boardView = document.getElementById('board-view');
        if (boardView && !boardView.classList.contains('hidden')) {
            window.attachDragAndDropListeners();
        }
    });

    function loadMoreTasks(button) {
        const statusId = button.getAttribute('data-status-id');
        const list = document.getElementById(`status_${statusId}`);
        const offset = list.querySelectorAll('.kanban-item').length;

        // Show loading spinner
        const spinner = button.querySelector('.loading-spinner');
        const text = button.querySelector('.load-more-text');
        button.disabled = true;
        if (spinner && text) {
            spinner.classList.remove('hidden');
            text.classList.add('hidden');
        }

        const token = getCsrfToken();
        if (!token) {
            button.disabled = false;
            if (spinner && text) {
                spinner.classList.add('hidden');
                text.classList.remove('hidden');
            }
            return;
        }

        console.log('Fetching more tasks', {
            statusId,
            offset,
            statusTitles
        });

        fetch(`{{ route('admin.task.loadMore') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    status_id: statusId,
                    offset: offset
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(
                            `HTTP error! status: ${response.status}, message: ${err.message || JSON.stringify(err)}`
                        );
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Loaded tasks:', data.tasks);
                data.tasks.forEach(task => {
                    const card = document.createElement('div');
                    card.className =
                        'card-item bg-white dark:bg-gray-800 p-4 rounded-md shadow-md mb-3 kanban-item';
                    card.dataset.id = task.id;
                    card.dataset.projectId = task.project_id;
                    card.dataset.search =
                        `${task.title} ${task.description || ''} ${task.priority?.title || ''} ${statusTitles[task.status_id] || ''}`
                        .toLowerCase();
                    card.dataset.statusId = task.status_id;
                    card.dataset.priorityId = task.priority?.title ? '1' : '';
                    card.innerHTML = `
                    <h5 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                        ${task.title}
                        ${task.priority ? `<span class="badge inline-block px-2 py-1 text-xs font-semibold text-white rounded-full" style="background-color: ${task.priority.color};">${task.priority.title.charAt(0).toUpperCase() + task.priority.title.slice(1)}</span>` : ''}
                    </h5>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">${task.description || ''}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Project: ${task.project_name || 'N/A'}</p>
                    <a href="${task.view_url}" class="mt-2 inline-block px-3 py-1 text-white rounded hover:opacity-80" style="background-color: ${task.status_color}">
                        View
                    </a>
                `;
                    list.insertBefore(card, button);
                });
                if (!data.has_more) {
                    button.remove();
                }
                window.attachDragAndDropListeners();
            })
            .catch(error => {
                console.error('Error loading more tasks:', error.message);
                if (typeof toastr !== 'undefined') {
                    toastr.error('Error loading more tasks: ' + error.message);
                }
            })
            .finally(() => {
                button.disabled = false;
                if (spinner && text) {
                    spinner.classList.add('hidden');
                    text.classList.remove('hidden');
                }
            });
    }
</script>
