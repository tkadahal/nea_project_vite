@props(['tasks', 'statuses', 'statusColors', 'priorityColors'])

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    @foreach ($statuses as $status)
        <div class="kanban-column">
            @php
                $columnHeaderColor = $statusColors[$status->id] ?? 'gray';
            @endphp
            <div class="text-white p-3 rounded-t-md" style="background-color: {{ $columnHeaderColor }};">
                <h4 class="font-bold m-0">{{ $status->title }}</h4>
            </div>
            <div class="kanban-list bg-gray-50 dark:bg-gray-700 p-3 rounded-b-md" id="status_{{ $status->id }}"
                data-status-id="{{ $status->id }}">
                @foreach ($tasks[$status->id] ?? [] as $task)
                    <div class="card-item bg-white dark:bg-gray-800 p-4 rounded-md shadow-md mb-3 kanban-item"
                        data-id="{{ $task->id }}"
                        data-search="{{ strtolower($task->title . ' ' . ($task->description ?? '') . ' ' . ($task->priority->title ?? '') . ' ' . ($task->status->title ?? '')) }}"
                        data-status-id="{{ $task->status->id }}" data-priority-id="{{ $task->priority->id ?? '' }}">
                        <h5 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                            {{ $task->title }}
                            @php
                                $priorityBadgeColor = $priorityColors[$task->priority->title ?? ''] ?? 'gray';
                            @endphp
                            @if ($task->priority)
                                <span class="badge inline-block px-2 py-1 text-xs font-semibold text-white rounded-full"
                                    style="background-color: {{ $priorityBadgeColor }};">
                                    {{ ucfirst($task->priority->title) }}
                                </span>
                            @endif
                        </h5>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">{!! $task->description !!}</p>
                        @php
                            $taskStatusColor = $statusColors[$task->status->id] ?? 'gray';
                        @endphp
                        <a href="{{ route('admin.task.show', $task->id) }}"
                            class="mt-2 inline-block px-3 py-1 text-white rounded hover:opacity-80"
                            style="background-color: {{ $taskStatusColor }}">
                            View
                        </a>
                    </div>
                @endforeach
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
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<script>
    window.attachDragAndDropListeners = function() {
        // Global array to store pending calendar updates
        window.pendingCalendarUpdates = window.pendingCalendarUpdates || {};

        const statusIdMap = {};
        @foreach ($statuses as $status)
            statusIdMap['status_{{ $status->id }}'] = {{ $status->id }};
        @endforeach

        const statusColors = @json($statusColors);
        const statusTitles = {};
        @foreach ($statuses as $status)
            statusTitles[{{ $status->id }}] = '{{ $status->title }}';
        @endforeach

        function getCsrfToken() {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!token) console.error('CSRF token not found.');
            return token;
        }

        function updateTaskStatus(taskId, newStatusId, draggableElement) {
            const token = getCsrfToken();
            if (!token) {
                console.error('CSRF token missing. Cannot update task status.');
                return;
            }

            fetch("{{ route('admin.task.updateStatus') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        task_id: taskId,
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
                    let errorMessage = error.message || 'Unknown error during status update.';
                    console.error('Error updating task status:', errorMessage);
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Error updating task status: ' + errorMessage);
                    }
                });
        }

        const kanbanLists = document.querySelectorAll('.kanban-list');
        console.log(`Found ${kanbanLists.length} kanban lists`);

        if (kanbanLists.length === 0) {
            console.warn('No kanban lists found. SortableJS not initialized.');
            return;
        }

        kanbanLists.forEach(list => {
            new Sortable(list, {
                group: 'kanban', // Allows dragging between lists
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: function(evt) {
                    const draggableElement = evt.item;
                    const dropzone = evt.to;

                    if (evt.from !== dropzone) {
                        const taskId = draggableElement.dataset.id;
                        const newStatusId = statusIdMap[dropzone.id];

                        if (newStatusId === undefined) {
                            console.error('Invalid dropzone ID:', dropzone.id);
                            return;
                        }

                        draggableElement.dataset.statusId = newStatusId;
                        console.log(`Moved task ${taskId} to status ${newStatusId}`);
                        updateTaskStatus(taskId, newStatusId, draggableElement);
                    }
                }
            });
        });

        console.log('SortableJS initialized for kanban lists');
    };

    // Initial call to attach listeners
    document.addEventListener('DOMContentLoaded', () => {
        const boardView = document.getElementById('board-view');
        if (boardView && !boardView.classList.contains('hidden')) {
            window.attachDragAndDropListeners();
        }
    });
</script>
