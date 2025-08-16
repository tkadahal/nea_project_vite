@props(['tasks', 'statuses', 'statusColors', 'priorityColors', 'directorates', 'departments'])

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    @foreach ($statuses as $status)
        <div class="kanban-column">
            @php
                $columnHeaderColor = $statusColors[$status->id] ?? 'gray';
                $statusTasks = ($tasks[$status->id] ?? collect())->values()->toArray();
                $visibleTasks = array_slice($statusTasks, 0, 5);
                \Log::debug("Status {$status->id} ({$status->title}) tasks:", [
                    'count' => count($statusTasks),
                    'tasks' => $statusTasks,
                ]);
            @endphp
            <div class="text-white p-3 rounded-t-md" style="background-color: {{ $columnHeaderColor }};">
                <h4 class="font-bold m-0">{{ $status->title }} (<span class="task-count">{{ count($statusTasks) }}</span>)
                </h4>
            </div>
            <div class="kanban-list bg-gray-50 dark:bg-gray-700 p-3 rounded-b-md" id="status_{{ $status->id }}"
                data-status-id="{{ $status->id }}">
                @foreach ($visibleTasks as $task)
                    <div class="card-item bg-white dark:bg-gray-800 p-4 rounded-md shadow-md mb-3 kanban-item"
                        data-task-id="{{ $task['id'] }}" data-project-id="{{ $task['project_id'] ?? '' }}"
                        data-directorate-id="{{ $task['directorate_id'] ?? '' }}"
                        data-department-id="{{ $task['department_id'] ?? '' }}"
                        data-priority-id="{{ $task['priority_id'] ?? '' }}"
                        data-start-date="{{ $task['start_date'] ?? '' }}"
                        data-due-date="{{ $task['due_date'] ?? '' }}" data-title="{{ $task['title'] }}"
                        data-search="{{ strtolower($task['title'] .' ' .($task['description'] ?? '') .' ' .($task['priority']['title'] ?? '') .' ' .($task['status']['title'] ?? '') .' ' .($task['directorate_id'] ?? '') .' ' .($task['department_id'] ?? '') .' ' .collect($task['sub_tasks'] ?? [])->pluck('title')->implode(' ')) }}"
                        data-status-id="{{ $task['status_id'] ?? $status->id }}">
                        <h5 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                            {{ $task['title'] ?? 'Untitled Task' }}
                            @if ($task['parent_id'])
                                <span class="text-sm text-gray-500 dark:text-gray-400">Sub-task of:
                                    {{ $task['parent_title'] }}</span>
                            @endif
                            @if ($task['priority'])
                                <span class="badge inline-block px-2 py-1 text-xs font-semibold text-white rounded-full"
                                    style="background-color: {{ $task['priority']['color'] ?? 'gray' }};">
                                    {{ ucfirst($task['priority']['title']) }}
                                </span>
                            @endif
                        </h5>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">{!! $task['description'] ?? 'No description' !!}</p>
                        @if ($task['project_id'])
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Project:
                                {{ $task['project_name'] ?? 'N/A' }}</p>
                        @elseif ($task['directorate_id'] && !$task['department_id'] && !$task['project_id'])
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Directorate:
                                {{ $directorates->find($task['directorate_id'])?->title ?? 'N/A' }}</p>
                        @elseif ($task['directorate_id'] && $task['department_id'] && !$task['project_id'])
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Department:
                                {{ $departments->find($task['department_id'])?->title ?? 'N/A' }}</p>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">No Project/Directorate/Department
                            </p>
                        @endif
                        <!-- Sub-tasks -->
                        @if (!empty($task['sub_tasks']))
                            <div class="mt-2">
                                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Sub-tasks:</p>
                                <ul class="list-disc pl-5 text-sm text-gray-600 dark:text-gray-400">
                                    @foreach ($task['sub_tasks'] as $subTask)
                                        <li>
                                            <a href="{{ $subTask['view_url'] }}"
                                                class="hover:underline">{{ $subTask['title'] }}</a>
                                            <span
                                                class="badge inline-block px-2 py-1 text-xs font-semibold text-white rounded-full"
                                                style="background-color: {{ $subTask['status']['color'] ?? 'gray' }};">
                                                {{ $subTask['status']['title'] ?? 'N/A' }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="mt-2 flex space-x-2">
                            <a href="{{ $task['view_url'] }}" class="px-3 py-1 text-white rounded hover:opacity-80"
                                style="background-color: {{ $task['status_color'] ?? 'gray' }}">
                                {{ trans('global.view') }}
                            </a>
                            @can('task_create')
                                <a href="{{ route('admin.task.create', ['parent_id' => $task['id']]) }}"
                                    class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600">
                                    Add Sub-task
                                </a>
                            @endcan
                        </div>
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

            const payload = {
                task_id: taskId,
                status_id: newStatusId
            };
            if (projectId) {
                payload.project_id = projectId;
            }

            fetch("{{ route('admin.task.updateStatus') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload)
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
                        status: statusTitles[newStatusId] || 'Unknown Status',
                        color: statusColors[newStatusId] || 'gray'
                    };

                    const viewButton = draggableElement.querySelector('a:not([href*="parent_id"])');
                    if (viewButton) {
                        viewButton.style.backgroundColor = statusColors[newStatusId] || 'gray';
                    }

                    if (typeof taskCalendar !== 'undefined' && taskCalendar) {
                        const event = taskCalendar.getEventById(taskId);
                        if (event) {
                            event.setExtendedProp('status', statusTitles[newStatusId] || 'Unknown Status');
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
                        const taskId = draggableElement.dataset.taskId;
                        const projectId = draggableElement.dataset.projectId || null;
                        const newStatusId = statusIdMap[dropzone.id];

                        if (newStatusId === undefined) {
                            console.error('Invalid dropzone ID:', dropzone.id);
                            return;
                        }

                        draggableElement.dataset.statusId = newStatusId;
                        console.log(
                            `Moved task ${taskId} to status ${newStatusId} ${projectId ? `for project ${projectId}` : 'without project'}`
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
        const offset = list.querySelectorAll('.card-item').length;

        // Apply current filter values
        const directorateId = document.getElementById('directorate_id')?.value || '';
        const projectId = document.getElementById('project_id')?.value || '';
        const priorityId = document.getElementById('priority_id')?.value || '';
        const dateStart = document.getElementById('date_start')?.value || '';
        const dateEnd = document.getElementById('date_end')?.value || '';

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
            directorateId,
            projectId,
            priorityId,
            dateStart,
            dateEnd,
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
                    offset: offset,
                    directorate_id: directorateId,
                    project_id: projectId,
                    priority_id: priorityId,
                    date_start: dateStart,
                    date_end: dateEnd
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
                    card.dataset.taskId = task.id;
                    card.dataset.projectId = task.project_id || '';
                    card.dataset.directorateId = task.directorate_id || '';
                    card.dataset.departmentId = task.department_id || '';
                    card.dataset.priorityId = task.priority_id || '';
                    card.dataset.startDate = task.start_date || '';
                    card.dataset.dueDate = task.due_date || '';
                    card.dataset.title = task.title || 'Untitled Task';
                    card.dataset.search =
                        `${task.title || ''} ${task.description || ''} ${task.priority?.title || ''} ${statusTitles[task.status_id] || ''} ${task.directorate_id || ''} ${task.department_id || ''} ${task.sub_tasks ? task.sub_tasks.map(st => st.title).join(' ') : ''}`
                        .toLowerCase();
                    card.dataset.statusId = task.status_id;
                    card.innerHTML = `
                    <h5 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                        ${task.title || 'Untitled Task'}
                        ${task.parent_id ? `<span class="text-sm text-gray-500 dark:text-gray-400">Sub-task of: ${task.parent_title || 'N/A'}</span>` : ''}
                        ${task.priority ? `<span class="badge inline-block px-2 py-1 text-xs font-semibold text-white rounded-full" style="background-color: ${task.priority.color};">${task.priority.title.charAt(0).toUpperCase() + task.priority.title.slice(1)}</span>` : ''}
                    </h5>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">${task.description || 'No description'}</p>
                    ${task.project_id ? `<p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Project: ${task.project_name || 'N/A'}</p>` :
                    task.directorate_id && !task.department_id && !task.project_id ? `<p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Directorate: ${task.directorate_name || 'N/A'}</p>` :
                    task.directorate_id && task.department_id && !task.project_id ? `<p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Department: ${task.department_name || 'N/A'}</p>` :
                    '<p class="text-sm text-gray-500 dark:text-gray-400 mt-1">No Project/Directorate/Department</p>'}
                    ${task.sub_tasks && task.sub_tasks.length ? `
                        <div class="mt-2">
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Sub-tasks:</p>
                            <ul class="list-disc pl-5 text-sm text-gray-600 dark:text-gray-400">
                                ${task.sub_tasks.map(st => `
                                    <li>
                                        <a href="${st.view_url}" class="hover:underline">${st.title}</a>
                                        <span class="badge inline-block px-2 py-1 text-xs font-semibold text-white rounded-full" style="background-color: ${st.status.color || 'gray'};">
                                            ${st.status.title || 'N/A'}
                                        </span>
                                    </li>
                                `).join('')}
                            </ul>
                        </div>
                    ` : ''}
                    <div class="mt-2 flex space-x-2">
                        <a href="${task.view_url}" class="px-3 py-1 text-white rounded hover:opacity-80" style="background-color: ${task.status_color}">
                            View
                        </a>
                        ${task.can_create_subtask ? `
                            <a href="{{ route('admin.task.create') }}?parent_id=${task.id}" class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600">
                                Add Sub-task
                            </a>
                        ` : ''}
                    </div>
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
