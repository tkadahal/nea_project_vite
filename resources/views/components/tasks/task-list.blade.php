@props(['tasksFlat', 'priorityColors', 'statusColors'])

<div id="taskListContainer">
    @if (isset($tasksFlat) && count($tasksFlat) > 0)
        @foreach ($tasksFlat as $task)
            <div class="list-item border border-gray-300 dark:border-gray-600 rounded-lg p-4 mb-3"
                data-search="{{ strtolower($task->title . ' ' . ($task->description ?? '') . ' ' . ($task->priority->title ?? '') . ' ' . ($task->status->title ?? '') . ' ' . ($task->project->title ?? '')) }}">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
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
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">{!! $task->description !!}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                            Project: <span class="font-medium">{{ $task->project->name ?? 'No Project' }}</span> |
                            Status:
                            @php
                                $taskStatusColor = $statusColors[$task->status->id] ?? 'gray';
                            @endphp
                            <span class="inline-block px-2 py-1 text-xs font-semibold text-white rounded-full"
                                style="background-color: {{ $taskStatusColor }};">
                                {{ $task->status->title ?? 'N/A' }}
                            </span> |
                            Due:
                            <span class="font-medium">
                                {{ $task->due_date ? (new DateTime($task->due_date))->format('M d, Y') : 'No Due Date' }}
                            </span>
                        </p>
                    </div>
                    <a href="{{ route('admin.task.show', $task->id) }}"
                        class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                        View
                    </a>
                </div>
            </div>
        @endforeach
    @else
        <p class="text-center p-3 text-gray-500 dark:text-gray-400">No tasks found for this view.</p>
    @endif
</div>
